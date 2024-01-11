<?php
// require_once ("../environment/environment-config.php");
require_once ("config/globals-daemon.php");
require_once ("../lib/sinapad-rest/rest-php-adapter.php");

include "../job-properties-mananger.php";

/** https://tools.ietf.org/html/rfc5424#section-6.3 */
openlog("[dockthor-log][daemon-check-pending.php]", LOG_PID | LOG_PERROR, LOG_LOCAL0);

/** Jobs with more than one ligands always go to sdumont */
function sendJobToSdumont($portal_id, $nrun, $numOfLigands, $args, $uuid, $submissionType){
    
    $slots=$nrun * $numOfLigands;
    $extra_params="sga_requesting_resources::true;sga_requesting_slots::$slots";
    
    syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; extra parameters: $extra_params");
    
    $result = rest_run ( $args, $uuid, $extra_params, $slots, $submissionType );
    return $result;
    
}

/** Jobs with one ligand, can be sent to altx or sdumont (case altx off).  */
function sendJobToDocking($portal_id, $args, $uuid, $submissionType){  
    
    syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; extra parameters: NONE; submissionType=$submissionType");
    $result = rest_run ( $args, $uuid, "", null, $submissionType);
    return $result;
    
}

function sendJobFromCheckingToPending($portal_id,$code){
    
    file_put_contents ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/pending/$portal_id", "$code" );
    unlink ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking/" . $portal_id );
    
}

/**
 * Coleta e definicao de parametros
 * */

// Id do usuario recebido quando logado
$uuid = $argv [1];

// Id do job no portal
$portal_id = $argv [2];

// Submission type
$submissionType = $argv [3];

// Numero de ligantes
$numOfLigands = 0;

// 
$nrun = 12;

// Pegar array de propriedades do job, com id do portal
$jobPropertiesMananger = JobPropertiesMananger::getInstance();
$job_properties = $jobPropertiesMananger->getJobProperties($portal_id);

/** 
 * Montagem dos argumentos do job, ex.: 
 * o::gmmsb_iuri_testrun_5bc49d4e27680/OUTPUT;gc::{16,6,20};gs::{11,11,11};naval::500000;popsize::750;seed::-1985;rstep::0.25;nrun::12;r::gmmsb_iuri_testrun_5bc49d4e27680/INPUT/PROTEIN/1caq.in;l::gmmsb_iuri_testrun_5bc49d4e27680/INPUT/LIGAND
 * */

syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; preparing parameters ...");

// Move o ponteiro do array para o ultimo elemento
end ( $job_properties [$portal_id] ['submissions'] );

// Pega a ultima submissao, que eh a mais recente (pode haver n) 
$submission = $job_properties [$portal_id] ['submissions'] [key ( $job_properties [$portal_id] ['submissions'] )];
$args = "o::$portal_id/OUTPUT";

// Varendo os parametros do arquivo properties
foreach ( $submission ['args'] as $key => $value ) {
	if (is_array ( $value )) {
		$args = "$args;$key::{";
		$i = 0;
		foreach ( $value as $param ) {
			reset ( $value );
			if ($i == 0) {
				$args = "$args$param";
			} else {
				$args = "$args,$param";
			}
			$i ++;
		}
		$args = "$args}";
	} else {
		
		if ($key == "nrun") {		
			$nrun = $value;
		}
		$args = "$args;$key::$value";
	}
}

/** 
 * Criar o diretorio para o job
 * 
 */

// Checar antes se ele ja existe, caso sim, sera deletado
if (rest_exists ( null, $portal_id, $uuid ) == 200) {
	rest_delete ( null, $portal_id, $uuid );
}
rest_create_directory ( null, $portal_id, $uuid );
rest_create_directory ( $portal_id, "INPUT", $uuid );

$couldUpload = true;
// foreach ( $submission ['file-args'] as $key => $file ) {
// 	if (! empty ( $file )) {
// 		$is_directory = false;
// 		if (strlen ( $file ) > 10) {
// 			if (substr ( $file, 0, 10 ) === "directory/") {
// 				$is_directory = true;
// 				$directory = substr ( $file, 10, strlen ( $file ) );
// 				rest_create_directory ( "$portal_id/INPUT", $directory, $uuid );
// 				$files_to_upload = preg_grep ( '/^([^.])/', scandir ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/$directory" ) );

// 				foreach ( $files_to_upload as $file_to_upload ) {
// 					$couldUpload = rest_upload ( "$portal_id/INPUT/$directory", $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/$directory/$file_to_upload", $uuid ) == 200 && $couldUpload;
// 				}
// 			}
// 		}
// 		if (! $is_directory) {
// 			$parent = "$portal_id/INPUT";

// 			$path = pathinfo ( $file );
// 			$directories = $path ['dirname'] != "." ? $path ['dirname'] : "";
// 			if (! empty ( $directories )) {
// 				if (rest_exists ( $portal_id, $directories, $uuid ) != 200) {
// 					foreach ( explode ( "/", $directories ) as $dir ) {
// 						rest_create_directory ( $parent, $dir, $uuid );
// 						$parent .= "/$dir";
// 					}
// 				}
// 			}
// 			$couldUpload = rest_upload ( $parent, $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/$file", $uuid ) == 200 && $couldUpload;
// 		}
// 	}
// 	$args = "$args;$key::$portal_id/INPUT/$file";
// }

/** 
 * Contagem de ligantes
 */

// Contagem de ligantes ( Mudança para envio de entrada compactada (Marcelo Monteiro Galheigo))
foreach ( $submission ['file-args']['l'] as $lig ) {
    $numOfLigands = $numOfLigands + 1;
}

/**
 *  Preparacao dos 
 *  - diretorios no csgrid para o novo job e 
 *  - da string de argumentos para a submissao do job.
 *  
 *  Detalhes:
 *  - Caso tenha mais de 1 ligante: todos os arquivos serao compactados; enviado e executado (baseado na concatenacao da string de argumentos)
 *  - Caso contrario, o mecanismo antigo eh acionado. Para cada protein, ligante e cofactor, sera:
 *  criado um diretorio para cada no csgrid, cada arquivo sera enviado para este diretorio e executado (baseado na concatenacao da string de argumentos)
 */
if ($numOfLigands > 1) {
	
	$sources="";

	$sourcePath=$GLOBALS['DOCKTHOR_PATH']."/apps/docking/daemon/jobs/$portal_id";
	
	if(file_exists("$sourcePath/PROTEIN") && is_dir("$sourcePath/PROTEIN")){
		$sources=$sources." PROTEIN";
	}
	
	if(file_exists("$sourcePath/LIGAND") && is_dir("$sourcePath/LIGAND")){
		$sources=$sources." LIGAND";
	}
	
	if(file_exists("$sourcePath/COFACTOR") && is_dir("$sourcePath/COFACTOR")){
		$sources=$sources." COFACTOR";
	}
	
	shell_exec("cd $sourcePath; zip -r INPUT.zip $sources");
	
	$couldUpload = rest_upload ( "$portal_id/INPUT", "$sourcePath/INPUT.zip", $uuid ) == 200 && $couldUpload;
	
	$args = "$args;in::$portal_id/INPUT/INPUT.zip";
	
	shell_exec("rm -f $sourcePath/INPUT.zip");
	
	
} else {

    // PROTEIN
    // Criacao do diretorio no csgrid
	if(rest_create_directory ( "$portal_id/INPUT", "PROTEIN", $uuid ) == '200'){
	    // Montagem da string de argumentos, a partir do arquivo de propriedades do job
		foreach ( $submission ['file-args']['r'] as $file ) {
		    // Path completo do arquivo
			$filePath = $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/PROTEIN/$file";
			// Checagem se esse arquivo existe mesmo
			if(file_exists($filePath) && !is_dir($filePath)){
			    // Envia o arquivo para o csgrid
				$couldUpload = rest_upload ( "$portal_id/INPUT/PROTEIN", $filePath , $uuid ) == 200 && $couldUpload;
				// Se der errado, pare aqui
				if(!$couldUpload){
					break;
				} 
				// Montagem dos argumentos
				$args = "$args;r::$portal_id/INPUT/PROTEIN/$file";
			} else {
				$couldUpload = false;
			}
		}
	}
	
	// LIGAND
	// Criacao do diretorio no csgrid
	if(rest_create_directory ( "$portal_id/INPUT", "LIGAND", $uuid ) == '200'){
	    // Montagem da string de argumentos, a partir do arquivo de propriedades do job
		foreach ( $submission ['file-args']['l'] as $file ) {		
		    // Path completo do arquivo
			$filePath = $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/LIGAND/$file";
			// Checagem se esse arquivo existe mesmo
			if(file_exists($filePath) && !is_dir($filePath)){
			    // Envia o arquivo para o csgrid
				$couldUpload = rest_upload ( "$portal_id/INPUT/LIGAND",$filePath , $uuid ) == 200 && $couldUpload;
				// Se der errado, pare aqui
				if(!$couldUpload){
					break;
				} 
			} else {
				$couldUpload = false;
			}
		}
		// Montagem dos argumentos
		$args = "$args;l::$portal_id/INPUT/LIGAND";
	}
	
	// COFACTOR
	// Criacao do diretorio no csgrid
	if(rest_create_directory ( "$portal_id/INPUT", "COFACTOR", $uuid ) == '200'){
	    // Montagem da string de argumentos, a partir do arquivo de propriedades do job
		if(isset($submission ['file-args']['c'])){
			foreach ( $submission ['file-args']['c'] as $file ) {
			    // Path completo do arquivo
                $filePath = $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/COFACTOR/$file";
                // Checagem se esse arquivo existe mesmo
                if(file_exists($filePath) && !is_dir($filePath)){
                    // Envia o arquivo para o csgrid
                    $couldUpload = rest_upload ( "$portal_id/INPUT/COFACTOR",$filePath , $uuid ) == 200 && $couldUpload;
                    // Se der errado, pare aqui
                    if(!$couldUpload){
                        break;
                    }
                } else {
                    $couldUpload = false;
                }
            }
            // Montagem dos argumentos
            $args = "$args;cfactordir::$portal_id/INPUT/COFACTOR;cfactor::" .  count($submission ['file-args']['c']);
		}
	}

}

/**
 * Caso tenha dado certo todos os upload anteriormente, segue com a submissao.
 * Mas, caso algum erro, o job sera enviado para "pending" novamente.
 */

if ($couldUpload) {
	
    // Criacao do diretorio de resultados
    $restCreateDirectoryResult = rest_create_directory ( $portal_id, "OUTPUT", $uuid );
	if ($restCreateDirectoryResult == '200') {
	    
	    syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; submissionType: $submissionType; parameters: $args ");
	    $result == null;

	    if ($numOfLigands == 1) {
            
            $result = sendJobToDocking($portal_id, $args, $uuid, $submissionType);                      
            
        }else if ($numOfLigands > 1) {
            
            $result = sendJobToSdumont($portal_id, $nrun, $numOfLigands, $args, $uuid, $submissionType);
            
        }else{
            syslog(LOG_INFO|LOG_LOCAL0, "ERROR: numOfLigands error (numOfLigands=$numOfLigands)");
            sendJobFromCheckingToPending($portal_id,500);
            exit();
        }	
	    
		if($result!=null){
		    
		    syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; result".json_encode($result));
		    
		    // Caso tenho sucesso na submissao: atualize o portal com as novas informacoes do job (mover para running e adicionar mais informacoes no arquivo de propriedades)
		    // Caso erro, o job sera movido para "error"
		    // Para qualquer outra resposta, sera movido para "pending" novamente, para ser submitido mais tarde
		    $code = $result->{'code'};
		    if ($code == 200) {
		        
		        $job_id = $result->{'jobId'};
		        syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; submitted successfully, via REST; job_id:$job_id");
		        		        
		        
		        syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; $job_id; start to get job status ....");
		        
		        // Tentar coletar o status do job em 10 vezes, em janelas de 30 segundos. 
		        # Caso tentar 10 vezes, algo deu errado e uma mensagens sera exibida no syslog
		        $tryJobReadyIndex=1;
		        for ($tryJobReadyIndex; $tryJobReadyIndex < 10; $tryJobReadyIndex++) {
		            
		            $service_job = rest_get($job_id, $uuid);
		            syslog(LOG_INFO|LOG_LOCAL0, "Trying to get job ready (rest_get() 200): Job $portal_id; $job_id; finish to get job status: [".json_encode($service_job)."]");
		            
		            // Prosseguir somente se o status do job for 200 (rest_get()) - se nao, o daemon tenta na proxima
		            if($service_job->{'code'}==200){
		                
		                syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; $job_id; Success to get job status (200)");
		                
		                // Atualizar o arquivo de propriedades com mais informacoes, vindas do csgrid
		                $submission['service-submission-date'] = $service_job->{'startDate'};
		                $submission['service-resource'] = $service_job->{'resource'};
		                $submission['job-service-status'] = $service_job->{'status'};
		                if(isset($job_properties [$portal_id] ['submissions']['pending'])){
		                    unset($job_properties [$portal_id] ['submissions']['pending']);
		                }
		                $job_properties [$portal_id] ['submissions'][$job_id] = $submission;
		                
		                $propertiesFileSaveResult = $jobPropertiesMananger->saveJobProperties($portal_id, $job_properties);
		                
		                if ($propertiesFileSaveResult==false){
		                    syslog(LOG_ERR|LOG_LOCAL0, "Job $portal_id $job_id; ERROR: Could not save the job properties");
		                }else{
		                    syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; $job_id; Success to update property file: ".json_encode($job_properties));
		                }
		                
		                // Mover o job de "checking" para "running"
		                syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; $job_id; Moving job from 'checking' to 'running' ...");
		                file_put_contents ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/running/$portal_id", "$job_id" );
		                unlink ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking/" . $portal_id );
		                
		                // break for
		                break;
		            }else{
				$sleepTime = 30; // seconds
				syslog(LOG_INFO|LOG_LOCAL0, "Sleep $sleepTime seconds: tryJobReadyIndex $tryJobReadyIndex; Job $portal_id; $job_id; status: [".json_encode($service_job)."]");
		                sleep($sleepTime); 
		            }
		        }

			syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; $job_id; tryJobReadyIndex $tryJobReadyIndex ");
		        if($tryJobReadyIndex === 10){
		            syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; $job_id; ERROR: Failed to get job status (tried $tryJobReadyIndex times). Moving job to error_stuck_in_waiting folder");

			    // Move job from checking to error
			    file_put_contents ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/error_stuck_in_waiting/$portal_id", "$job_id" );
                            unlink ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking/" . $portal_id );			    

			    // Cancel job (isso n funciona. eh retornado o codigo 494 - nao pode ser cancelado)
			    // $cancelResponse = rest_cancel($job_id, $uuid, true);
			    // syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; $job_id; Cancel response ".json_encode($cancelResponse));

		        }
		        
		    } else if ($code == 400) {
		        syslog(LOG_ERR|LOG_LOCAL0, "Job $portal_id; ERROR: USER ERROR (REST code result $code); The job will be send to error/ folder");
		        
		        file_put_contents ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/error/$portal_id", "$job_id" );
		        unlink ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking/" . $portal_id );
		    } else {
		        syslog(LOG_ERR|LOG_LOCAL0, "Job $portal_id; ERROR: UNDEFINED ERROR; REST code result $code. The job will be send to pending/ folder");
		        sendJobFromCheckingToPending($portal_id,$code);
		    }
		}
		
	}else{
	    syslog(LOG_INFO|LOG_LOCAL0, "ERROR could not create directory result (restCreateDirectoryResult=$restCreateDirectoryResult) - Job $portal_id; submissionType: $submissionType; parameters: $args ");
	    syslog(LOG_INFO|LOG_LOCAL0, "Send job $portal_id from checking to pending, due 'could not create directory result'");
	    sendJobFromCheckingToPending($portal_id,500);
	}
} else {
    syslog(LOG_ERR|LOG_LOCAL0, "Job $portal_id; ERROR: The job can't run and will be send to pending again (some error occoured on rest_upload).");
    
	file_put_contents ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/pending/$portal_id", "pending" );
	unlink ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking/" . $portal_id );
}

syslog(LOG_INFO|LOG_LOCAL0, "daemon-check-pending.php finished!");

?>

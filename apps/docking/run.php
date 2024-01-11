<?php
//require_once 'environment/environment-config.php';
require_once '../../conf/globals-dockthor.php';
require_once 'lib/sinapad-rest/rest-php-adapter.php';
require_once 'exception/upload-exception.php';
require_once 'lib/utils/email-utils.php';
require_once 'lib/utils/database-queries.php';
require_once 'prepared-files-app/lib/preparedResourcesLib.php';

include "job-properties-mananger.php"; 

openlog("[dockthor-log][run.php]", LOG_PID | LOG_PERROR, LOG_LOCAL0);
syslog(LOG_INFO|LOG_LOCAL0, "#### run.php ####");

session_start ();
$session_id = session_id ();

$args_array = array();
$files_array = array();

$jobName = $_POST['jobName'];
$portal_id = uniqid(str_replace(" ", "_", $jobName) . "_");

syslog(LOG_INFO|LOG_LOCAL0, "User pressed dock button (portal id:$portal_id, session_id:$session_id)");

$response = array();

// Force an error
/*
http_response_code(500); // break here
$response['problem'] = 'protein';
echo json_encode($response);
exit();
*/

// Protein verify (count==1)
$proteinsList = null;
$proteinsListCount = 0;
if(isset($_POST['proteinsList'])){
	$proteinsList = json_decode ($_POST['proteinsList'], true);
	$proteinsListCount = count($proteinsList);
	if($proteinsListCount!=1){ // Must have 1 or more
		syslog(LOG_ERR|LOG_LOCAL0, "ERROR - protein was send enpty");
		http_response_code(500); // break here
		$response['problem'] = 'protein';
		echo json_encode($response);
		exit();
	} 
} else {
	syslog(LOG_ERR|LOG_LOCAL0, "ERROR - protein parameter must have exist");
	http_response_code(500); // break here
	$response['problem'] = 'protein';
	echo json_encode($response);
	exit();
}

// Ligand verify (count>1)
$ligandsList = null;
$ligandsListCount = 0;
if(isset($_POST['ligandsList'])){
	$ligandsList = json_decode ($_POST['ligandsList'], true);
	$ligandsListCount = count($ligandsList);
	if(!($ligandsListCount>=1)){ // Must have 1 or more
		syslog(LOG_ERR|LOG_LOCAL0, "ERROR - ligand was send empty");
		http_response_code(500); // break here
		$response['problem'] = 'ligand';
		echo json_encode($response);
		exit();
	}
} else {
	syslog(LOG_ERR|LOG_LOCAL0, "ERROR - ligand parameter must have exist");
	http_response_code(500); // break here
	$response['problem'] = 'ligand';
	echo json_encode($response);
	exit();
}

// Cofactor verify (count>=0)
$cofactorList = null;
$cofactorListCount = 0;
if(isset($_POST['cofactorList'])){
	$cofactorList = json_decode ($_POST['cofactorList'], true);
	$cofactorListCount = count($cofactorList);
	if(!($cofactorListCount>=0)){ // Must have 0 or more
		http_response_code(500); // break here
		$response['problem'] = 'cofactor';
		echo json_encode($response);
		exit();
	}
}

syslog(LOG_INFO|LOG_LOCAL0, "Success on counts: protein=$proteinsListCount, cofactor=$cofactorListCount, ligand=$ligandsListCount");

// Protein target info for statistics
$proteinTargetInfo = null;
if(isset($_POST['proteinTargetInfo']) && $_POST['proteinTargetInfo']!='null'){
	$proteinTargetInfo = json_decode($_POST['proteinTargetInfo']);

	//// var_dump in syslog debug:
	//ob_start();
	//var_dump($_POST['proteinTargetInfo']);
	//syslog(LOG_INFO|LOG_LOCAL0, ob_get_clean());

	syslog(LOG_INFO|LOG_LOCAL0, "Protein target info: ".json_encode($proteinTargetInfo));
} else {
	syslog(LOG_INFO|LOG_LOCAL0, "Protein target info: Not used");
}

$args_array['gc'] = array();
$args_array['gc']['xc'] = (isset($_POST ['xGridCenter']) && $_POST ['xGridCenter'] != '' && $_POST ['xGridCenter'] != null) ? $_POST ['xGridCenter'] : "0" ;
$args_array['gc']['yc'] = (isset($_POST ['yGridCenter']) && $_POST ['yGridCenter'] != '' && $_POST ['yGridCenter'] != null) ? $_POST ['yGridCenter'] : "0" ;
$args_array['gc']['zc'] = (isset($_POST ['zGridCenter']) && $_POST ['zGridCenter'] != '' && $_POST ['zGridCenter'] != null) ? $_POST ['zGridCenter'] : "0";

if(isset($_POST['xGridSize']) || isset($_POST['yGridSize']) || isset($_POST['zGridSize'])){
	$args_array['gs'] = array();
	$args_array['gs']['x'] = (isset($_POST ['xGridSize']) && $_POST ['xGridSize'] != '' && $_POST ['xGridSize'] != null) ? $_POST ['xGridSize'] : "0" ;
	$args_array['gs']['y'] = (isset($_POST ['yGridSize']) && $_POST ['yGridSize'] != '' && $_POST ['yGridSize'] != null) ? $_POST ['yGridSize'] : "0" ;
	$args_array['gs']['z'] = (isset($_POST ['zGridSize']) && $_POST ['zGridSize'] != '' && $_POST ['zGridSize'] != null) ? $_POST ['zGridSize'] : "0" ;
}

if(isset($_POST['naval'])){
	$args_array['naval'] = $_POST['naval'];
}

if(isset($_POST['popsize'])){
	$args_array['popsize'] = $_POST['popsize'];
}

if(isset($_POST['seed'])){
	$args_array['seed'] = $_POST['seed'];
}

if(isset($_POST['rstep'])){
	$args_array['rstep'] = $_POST['rstep'];
}

if(isset($_POST['nrun'])){
	$args_array['nrun'] = $_POST['nrun'];
}

if(isset($_POST['cfactor'])){
	$args_array['cfactor'] = $_POST['cfactor'];
}

if(isset($_POST['softvdw'])){
    
    // Isso garante que se tiver valor true ou false, $args_array['softvdw'] sera definido
    // Mas se não tiver, como null, $args_array['softvdw'] nem existira
    if($_POST['softvdw'] == "true"){
        $args_array['softvdw'] = 0.35;        
    }else if($_POST['softvdw'] == "false"){
        $args_array['softvdw'] = 0.07;
    }
    
}

$job_portal_path = "daemon/jobs/" . $portal_id;

if (mkdir ( $job_portal_path )) { //tenta criar diretório em daemon/jobs
	if(isset($_POST['proteinsList'])){
		$proteinsList = json_decode ($_POST['proteinsList'], true);
		mkdir("$job_portal_path/PROTEIN");
		$files_array['r'] = array();
		foreach($proteinsList as $protein){
			$file_name_without_extension = preg_replace ( '/\\.[^.\\s]{2,3}$/', '', $protein ); // retirando extensão
			foreach ( glob ( "session-files/$session_id/DOCKING/PROTEIN/$file_name_without_extension.in" ) as $in_file ) {
				copy ( $in_file, "$job_portal_path/PROTEIN/" . basename ( $in_file ) );
// 				$files_array['r'] = basename ( $in_file ) ;
// 				if($files_array['r'] == null){
// 					$files_array['r'] = array();
// 				}
				array_push($files_array['r'], basename ( $in_file ));
				break;
			}
		}
	}
	
	if(isset($_POST['ligandsList'])){
		$ligandsList = json_decode ($_POST['ligandsList'], true);
		mkdir("$job_portal_path/LIGAND");
		$files_array['l'] = array();
		foreach($ligandsList as $ligand){
			foreach ( glob ( "session-files/$session_id/DOCKING/LIGAND/OUTPUT/$ligand/*.top" ) as $ligand_top_file ) {
				copy ( $ligand_top_file, "$job_portal_path/LIGAND/" . basename ( $ligand_top_file ) );
				//$files_array['l'] = basename ( $ligand_top_file ) ;
// 				if($files_array['l'] == null){
// 					$files_array['l'] = array();
// 				}
				array_push($files_array['l'], basename ( $ligand_top_file ));
			}			
		}
		copy("session-files/$session_id/DOCKING/LIGAND/ligand_mapfile.csv", "$job_portal_path/LIGAND/ligand_mapfile.csv");
	}
	
	if(isset($_POST['cofactorList'])){
		$cofactorList = json_decode ($_POST['cofactorList'], true);
		mkdir("$job_portal_path/COFACTOR");
		$files_array['c'] = array();
		foreach($cofactorList as $cofactor){
			foreach ( glob ( "session-files/$session_id/DOCKING/COFACTOR/OUTPUT/$cofactor/*.top" ) as $cofactor_top_file ) {
				copy ( $cofactor_top_file, "$job_portal_path/COFACTOR/" . basename ( $cofactor_top_file ) );
				//$files_array['l'] = basename ( $ligand_top_file ) ;
// 				if($files_array['c'] == null){
// 					$files_array['c'] = array();
// 				}
				array_push($files_array['c'], basename ( $cofactor_top_file ));
				break;
			}
		}
		copy("session-files/$session_id/DOCKING/COFACTOR/cofactor_mapfile.csv", "$job_portal_path/COFACTOR/cofactor_mapfile.csv");
	}
	
	if(isset($_POST['emails'])){
		$email_objects = json_decode ($_POST['emails'], true);
		$emails = array();
		foreach ($email_objects as $email_ob){
			array_push($emails, $email_ob['email']);
			
			if(isset($_POST['subscribe'])){
				$databaseQueries = new DatabaseQueries();
				$databaseQueries->subscribeUserToNews($email_ob['email']);
			}
		}
	}
	
	$job_properties = array();
	$job_properties[$portal_id] = array();
	$job_properties[$portal_id]["portal-submission-date"] = date("Y-m-d H:i:s");
	$job_properties[$portal_id]["email"] = $emails;
	$job_properties[$portal_id]["submissions"]["pending"] = ["args" => $args_array, "file-args" => $files_array];
	
	$jobPropertiesMananger = JobPropertiesMananger::getInstance();
	$propertiesFileSaveResult = $jobPropertiesMananger->saveJobProperties($portal_id, $job_properties);
	
	/** 
	 * CHeck if has some problem with
	 * - property array
	 * - if ligands are equals to zero
	 * - if could save property file on disk
	 * */
	$error = false;
	$error_messages = [];	
	if(!isset($job_properties[$portal_id]["submissions"]["pending"]["file-args"]["l"])){
	    $error = true;
	    $error_messages[] = "# Ligant array Error: The array is not setted: \$job_properties[\$portal_id][\"submissions\"][\"pending\"][\"file-args\"][\"l\"]";
	} 
	
	if (count($job_properties[$portal_id]["submissions"]["pending"]["file-args"]["l"])==0){
	    $error = true;
	    $error_messages[] = "# Ligant quantity error: The number of ligants is 0";
	} 
	
	if ($propertiesFileSaveResult==false){
	    $error = true;
	    $error_messages[] = "# Property file error: Could not save the properties.json file";
	}
	
	$response = array();
	
	/** A condição verifica se houve algum problema anteriormente
	 * - Caso não, a pagina/angular recebe erro e tratada com bootbox message, permitindo ao usuario enviar novamente. Em seguida um emal eh disparado para dockthor@lncc.br informando o problema.
	 * - Caso sim, o job eh submetido normalmente. 
	 */
	
	if($error){
	//if(true){ // force error
	    
	    $response['operationStatus'] = 'error';
	    
	    if(!isset($_POST['ligandsList'])){
	        $error_messages[] = "# POST ligandsList is not set";
	    }
	    
	    $errorMessage = "\n  
<p><b>Session id: </b> $session_id </p>
<p><b>Portal id: </b> $portal_id </p>
<p><b>Email</b>: ".(json_encode($emails,JSON_PRETTY_PRINT))."</p>\n
<p><b>Errors</b>: </p>
<pre><code>".(json_encode ( $error_messages, JSON_PRETTY_PRINT ))."</code><pre>\n
<p><b>Property content</b>: </p>
<pre><code>".(json_encode ( $job_properties, JSON_PRETTY_PRINT ))."</code><pre><br>";
	    
	    syslog(LOG_INFO|LOG_LOCAL0, "ERROR: $errorMessage");
	    $mailResponse = mail("dockthor@lncc.br", "[DockThor] Failed to submit - JOB $portal_id", $errorMessage, "Content-type: text/html; charset=utf-8" . "\r\n", '-fdockthor@lncc.br');
	    	    
	}else{
	    
	    // Move job to pending
	    file_put_contents ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/pending/$portal_id", "pending" );
	    
		// Protein target info (covid statistics)
		if($proteinTargetInfo!=null && isset($proteinTargetInfo->id) && isset($proteinTargetInfo->path)){	
			syslog(LOG_DEBUG|LOG_LOCAL0, "Protein target info (covid statistics): ".json_encode($proteinTargetInfo));
			$preparedResourcesLib = new PreparedResourcesLib();			
			$resultRegisterDataForStatistics = $preparedResourcesLib->statisticRegisterSubmission($portal_id, $proteinTargetInfo->id, $proteinTargetInfo->path);
			if($resultRegisterDataForStatistics){
				syslog(LOG_INFO|LOG_LOCAL0, "SUCCESS - Register protein data for statistics!");
			} else {
				syslog(LOG_ERR|LOG_LOCAL0, "ERROR - could not register protein data for statistics due a mysql operation!");					
			}
		}
		
		// Mail to user
	    $constants = array(
	        'VAR_JOB_ID' => $portal_id,
	        //'VAR_JOB_LINK' => "http://dockthor.lncc.br/v2/index.php?tab=DOCKING&page=RESULTS&jobId=$portal_id"
	        'VAR_JOB_LINK' => $GLOBALS['DOCKTHOR_URL']."index.php?tab=DOCKING&page=RESULTS&jobId=$portal_id"
	    );
	    $message = strtr(file_get_contents("email-templates/job-submited.html"), $constants);
	    $resultMailSubmitted = EmailUtils::sendEmail($emails, "[DockThor] JOB $portal_id SUBMITTED", $message, true);
	    
	    // Check result of email and log it
	    if($resultMailSubmitted){
	        syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; session_id:$session_id; The job was sent to pending folder, and an email was sent to ".json_encode($emails)."; properties:".json_encode($job_properties));	        
	    }else{
	        syslog(LOG_INFO|LOG_LOCAL0, "ERROR - Job $portal_id; session_id $session_id; An error occurred when mail to ".json_encode($emails)."; properties:".json_encode($job_properties));
	    }
	    
	    // Build web response
	    $response['operationStatus'] = 'submitted';
	    $response['portalId'] = $portal_id;
	}
	
	http_response_code(200); 
	syslog(LOG_INFO|LOG_LOCAL0, "SUCCESS - ".json_encode($response));
	echo json_encode($response);
	// 	header ( "Location: " . "../../index.php?tab=DOCKING&page=RESULTS&jobId=$portal_id");
	
}
?>

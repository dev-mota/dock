<?php
// Test urls 
// http://localhost/DockThor-3.0/?tab=DOCKING&page=RESULTS&jobId=teste_para_aba_results_3LIGANDS_592c469ee23b9

require_once '../environment/dtstatistic-environment.php';
require_once '../environment/docktscore-environment.php';
require_once '../lib/utils/utils.php';
require_once '../../../conf/globals-dockthor.php';

openlog("[dockthor-log][result-action.php]", LOG_PID | LOG_PERROR, LOG_LOCAL0);

$action = null;
$response['status'] = 'ERR';

function getUserIp() {
	if (! empty ( $_SERVER ['HTTP_CLIENT_IP'] )) // se possível, obtém o endereço ip da máquina do cliente
	{
		$ip = $_SERVER ['HTTP_CLIENT_IP'];
	} elseif (! empty ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) // verifica se o ip está passando pelo proxy
	{
		$ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER ['REMOTE_ADDR'];
	}
	return $ip;
}

function generateBestTopFile($session_id, $jobId, $ligandId, $mol2Position){

	$resultDir = "../session-files/$session_id/RESULTS/$jobId/RESULT";

	$command = "cd $resultDir ; python3 ".$GLOBALS["BUILD_SPLIT_MOL2_PY"]." -i result-$ligandId.mol2 -n $mol2Position";
	syslog(LOG_INFO|LOG_LOCAL0, "ANALYSE BUILD_SPLIT_MOL2_PY Command: $command");
	$result = shell_exec($command);
	
	$fileName = null;
	if($result==''){
		$fileName = "result-".$ligandId."_top".$mol2Position.".mol2"; // ex.: result-ligand_42e89394_2_top3.mol2
	}

	return $fileName; 
}

if(isset ($_POST['action'])){
	$action = $_POST['action'];
}

if(isset ($_GET ['action'])){
	$action = $_GET['action'];
}

session_start ();
$session_id = session_id ();

if ($action != null){
	
	if($action=='ANALYSE'){		
		
		//////////////// Get parameters
		$num = $_POST ['num']; // binding nodes

                $illegalChar = array("'", "\"", " ", "/", "_", ".", ",", "?", "!", ":", ";", "-", "+", "<", ">", "%", "~", "€", "$", "[", "]", "{", "}", "@", "&", "#", "*", "„");

		$num = str_replace($illegalChar, "", $num);

		if(isset ($_POST ['c'])){
			$c = $_POST ['c']; // rmsd
			$cFloat = number_format((float)$c, 1, '.', '');
		}

		$referenceFile = $_FILES ['r']; // reference file
		$jobId = $_POST ['jobId']; // reference file

		$illegalChar = array(".", ",", "?", "!", ":", ";", "-", "+", "<", ">", "%", "~", "€", "$", "[", "]", "{", "}", "@", "&", "#", "*", "„");
                $jobId = str_replace($illegalChar, "", $jobId);
		
		//syslog(LOG_INFO|LOG_LOCAL0, "$action - num=$num, c=$c, cFloat=$cFloat, jobId=$jobId, r=".$referenceFile['tmp_name'][0].", r=".$referenceFile['name'][0]);
		
        //////////////// Create session dir if necessary
		$sessionDir = "../session-files/$session_id";
		if(!file_exists($sessionDir)){
		    mkdir($sessionDir);
		}
		
		//////////////// Create result dir if necessary
		$resultsDir = "$sessionDir/RESULTS";
		if(!file_exists($resultsDir)){
		    mkdir($resultsDir);
		}		
		
		//////////////// Create result job dir if necessary
		$zipDestinyPath = "$resultsDir/$jobId";
		if(!file_exists($zipDestinyPath)){
		    mkdir($zipDestinyPath);
		}
		
		//////////////// Create result dir if necessary		
		$zipDestinyPath = "$resultsDir/$jobId/RESULT";
		if(!file_exists($zipDestinyPath)){
		    mkdir($zipDestinyPath);
		}
		
		//////////////// Clear previous result from the job
		if(file_exists($zipDestinyPath)){
			shell_exec("rm -rf $zipDestinyPath");
		}
		
		//////////////// Prepare _prep.pdb from in		
		syslog(LOG_INFO|LOG_LOCAL0, "Prepare _prep.pdb from in...");
		$utils = new Utils();
		$jobProteinPath = "../daemon/jobs/$jobId/PROTEIN";
		if(file_exists($jobProteinPath)){
			
			$inFiles = glob("$jobProteinPath/*.in");
			if(count($inFiles)==1){
				
				$targetProteinName = basename($inFiles[0]); // ex.: 1hpv_protein.in
				$preparedPdbFileName = $utils->convertInToPDB($jobProteinPath, $targetProteinName);
				
				if(file_exists("$jobProteinPath/$preparedPdbFileName")){
					syslog(LOG_INFO|LOG_LOCAL0, "Prepare _prep.pdb from in: SUCCESS ($preparedPdbFileName)");	
				} else {
					syslog(LOG_INFO|LOG_LOCAL0, "Prepare _prep.pdb from in: FAIL, could not create");	
				}
				
			} else {
				syslog(LOG_INFO|LOG_LOCAL0, "Prepare _prep.pdb from in: error! exite mais de 1 arquivo in na pasta PROTEIN");
			}
			
		} else {
			syslog(LOG_INFO|LOG_LOCAL0, "Prepare _prep.pdb from in: error! protein not found ($jobProteinPath)");
		}
		
		//////////////// Unzip in session dir
		$unzipToSessionCommand = "unzip ../daemon/jobs/$jobId/result/$jobId.zip -d $zipDestinyPath";
		$result = shell_exec($unzipToSessionCommand);

		//////////////// Calc ligands quantity
		$str = file_get_contents("../daemon/jobs/$jobId/properties.json");
		$json = json_decode($str, true); // decode the JSON into an associative array		
		$lastSubmition = end($json[$jobId]['submissions']);
		$ligandArray = array();
		foreach ($lastSubmition['file-args']['l'] as $element) {
			$fileNameSplit = explode(".", $element);
			$ligandArray[] = $fileNameSplit[0];
		}
		$numOfLigands = sizeof($ligandArray);


		$resultFiles=glob("$zipDestinyPath/*_run*.*");

                if($numOfLigands > 1) {
			$resultFiles=glob("$zipDestinyPath/result-*.*");

		}

                if(count($resultFiles) > 0) {
		
		//////////////// Define ligand name. Ex.: 1hpv_protein*.in
		$protein = $lastSubmition['file-args']['r'][0];
		$splitProteinFileName = explode(".", $protein);
		$protein = $splitProteinFileName[0]."*.in";
		
		if ($numOfLigands == 1) {
			
			//////////////// Prepare for dtstatistic			
			$referenceFileParameter = "";  // Reference file
			if($referenceFile['tmp_name'][0] != ""){
				
				$fileName = $referenceFile['name'][0];
				$filePath = "../session-files/$session_id/RESULTS/$jobId/RESULT/" . $fileName;
				$moveResult = move_uploaded_file($referenceFile['tmp_name'][0], $filePath);
			
				if($moveResult){
					$referenceFileParameter = "-r $fileName";
				}
			}
			
			//////////////// Read each ligand
			foreach ($ligandArray as $ligandId) {
				
				//////////////// Buil dtstatistic command
			    $dtstatisticCommand = "cd ".$GLOBALS['DOCKTHOR_PATH']."apps/docking/session-files/$session_id/RESULTS/$jobId/RESULT;".$GLOBALS ["DOCKTSCORE_SCRIPT"]." ".$GLOBALS['DOCKTHOR_PATH']."apps/docking/daemon/jobs/$jobId/PROTEIN/$protein $ligandId . $num $cFloat $referenceFileParameter";
				syslog(LOG_INFO|LOG_LOCAL0, "$action - dtstatisticCommand: $dtstatisticCommand");
				
				//////////////// Execute dtstatistic
				$result = shell_exec($dtstatisticCommand);
				syslog(LOG_INFO|LOG_LOCAL0, "$action - dtstatisticCommand result: $result");
				
			}
			
		}
		
		$buildBestCSVCommand = $GLOBALS["BUILD_BEST_CSV_SCRIPT"]." ../session-files/$session_id/RESULTS/$jobId/RESULT";
		$resultCSV = shell_exec($buildBestCSVCommand);
		
		$buildBestSnapCommand = $GLOBALS["BUILD_BEST_SNAP_SCRIPT"]." ../session-files/$session_id/RESULTS/$jobId/RESULT";
		$resultSnap = shell_exec($buildBestSnapCommand);
		
		// Read log files and generate result elements for view 
		$resultElements = array();
		$ligandCont = 0;
		
		$snapCSVFilePath = "../session-files/$session_id/RESULTS/$jobId/RESULT/snap.txt";
		
		$snapCSVFile = fopen($snapCSVFilePath, "r");
		
		$ligandArray = array();
		
		if($snapCSVFile != false) {
			
			while (!feof($snapCSVFile)) {
				
				$line = fgets($snapCSVFile); // (do not use "$file" more than once!)
				$line = substr($line, 0, strlen($line) - 1);
				
				if(strlen($line) > 4) {
					
					array_push($ligandArray, $line);
				}
			}
		}
		
		fclose($snapCSVFile);
		
		foreach ($ligandArray as $ligandId) {
		    $logFilePath = "../session-files/$session_id/RESULTS/$jobId/RESULT/result-$ligandId.log";
			$logFile = fopen($logFilePath, "r");
			
			$i = 0;
			//read line by line from pdb file
			if($logFile != false){
			
				$resultElements[$ligandCont] = array();
				$resultElements[$ligandCont]['name'] = $ligandId;

				while (!feof($logFile)) {
				
					//read the line
					$line = fgets($logFile); // (do not use "$file" more than once!)
				
					if(substr( $line, 0, 7 ) == "ligand_"){ // string substr ( string $string , int $start [, int $length ] )


						if( $i == ($num) )
							continue;
						// transform the line in array
						$lineArray = preg_split('/\s+/', $line);
						$resultElements[$ligandCont]['elements'][$i]['fileName'] = $lineArray[0];
						$resultElements[$ligandCont]['elements'][$i]['model'] = $lineArray[1];
						$resultElements[$ligandCont]['elements'][$i]['tenergy'] = $lineArray[2];
						$resultElements[$ligandCont]['elements'][$i]['ienergy'] = $lineArray[3];
						$resultElements[$ligandCont]['elements'][$i]['vdw'] = $lineArray[4];
						$resultElements[$ligandCont]['elements'][$i]['coul'] = $lineArray[5];
						$resultElements[$ligandCont]['elements'][$i]['numrotors'] = $lineArray[6];
						$resultElements[$ligandCont]['elements'][$i]['rmsd'] = $lineArray[7];
						$resultElements[$ligandCont]['elements'][$i]['score'] = $lineArray[8];						
						
						$fileName = $lineArray[0];
						$pos = strrpos($fileName, "_");
						$run = substr($fileName,$pos+1,-4);
						$resultElements[$ligandCont]['elements'][$i]['run'] = $run;

						// Split to single file (used in NGL Viewer)
						// P.s.: $mol2Position > 0
						$bestTopFileName = generateBestTopFile($session_id, $jobId, $ligandId, $i+1);
						$resultElements[$ligandCont]['elements'][$i]['bestTopFileName'] = $bestTopFileName;
												
						$i++;
					}					
				}
			}
			$ligandCont++;
		}
		//fclose($testFile);
		//$ligand_string=substr($ligand_string, 0, strlen($ligand_string)-1);
		//fwrite($dtLigandListFile, $ligand_string);
		//fclose($dtLigandListFile);
		
		////////////////response
		//$jsonResultElements = json_encode($resultElements,JSON_PRETTY_PRINT);
		//print_r($resultElements);
		//print_r($resultElements);
		//print_r($resultElements);
		
		$response['elements'] = $resultElements;
		$response['status'] = 'SUCCESS';
		//echo $GLOBALS['DOCKTHOR_PATH'];

		} else {
			 syslog(LOG_INFO|LOG_LOCAL0, "Does not exist viable solutions for job $jobId!");
	 		$response['status'] = 'ERR';
                        $response['errorMessage'] = "Does not exist viable solutions for job $jobId";
		}
		
		
	} else if ($action =='DOWNLOAD-RESULTS'){
		if (isset ( $_GET ["jobId"] )) {
			
			$jobId = $_GET ["jobId"];

			$illegalChar = array(".", ",", "?", "!", ":", ";", "-", "+", "<", ">", "%", "~", "€", "$", "[", "]", "{", "}", "@", "&", "#", "*", "„");
                	$jobId = str_replace($illegalChar, "", $jobId);

			$ip = getUserIp ();
			$date = date ( 'H-i-s, j-m-y' );

			$buildBestMol2Command = $GLOBALS["BUILD_BEST_MOL2_SCRIPT"]." ../session-files/$session_id/RESULTS/$jobId/RESULT ".$GLOBALS["BUILD_SPLIT_MOL2_PY"];
			syslog(LOG_INFO|LOG_LOCAL0, "$action buildBestMol2Command: $buildBestMol2Command");
            $resultMol2 = shell_exec($buildBestMol2Command);
		
			//////////////// Temp files will contain all files from daemon exept "result" folder 
			$temp = "../session-files/$session_id/RESULTS/$jobId/TEMP";
			// clear
			if(file_exists($temp)){
			    shell_exec("rm -rf $temp");
			}
			// create temp
			shell_exec("mkdir $temp");
			// copy all 
			shell_exec("cp -r ../daemon/jobs/$jobId/* $temp");
			// remove o zip do "result"
			shell_exec("rm -rf $temp/result");
			
			shell_exec("rm -f $temp/../RESULT/snap.txt");

			shell_exec("cp -rf $temp/../RESULT $temp");

			#creating parameters.txt file
			$str = file_get_contents("../daemon/jobs/$jobId/properties.json");
			$json = json_decode($str, true); // decode the JSON into an associative array
			$cofactorArray = array();
			$ligandArray = array();
			$lastSubmition = end($json[$jobId]['submissions']);			
			$protein = $lastSubmition['file-args']['r'][0];
			
			foreach ($lastSubmition['file-args']['c'] as $element) {
				$fileNameSplit = explode(".", $element);
				//$fileNameSplit = explode("cofactor_", $fileNameSplit[0]);
				$cofactorArray[] = $fileNameSplit[0];
			}
			$numOfCofactors = sizeof($cofactorArray);
			
			foreach ($lastSubmition['file-args']['l'] as $element) {
				$fileNameSplit = explode(".", $element);
				$ligandArray[] = $fileNameSplit[0];
			}
			$numOfLigands = sizeof($ligandArray);
			
			$sizex=$lastSubmition['args']['gs']['x']*2;
			$sizey=$lastSubmition['args']['gs']['y']*2;
			$sizez=$lastSubmition['args']['gs']['z']*2;
			
			$discretization=$lastSubmition['args']['rstep'];			
			
			$number_of_evaluations = $lastSubmition['args']['naval'];
			$population_size = $lastSubmition['args']['popsize'];
			$number_of_runs = $lastSubmition['args']['nrun'];
			$seed = $lastSubmition['args']['seed'];
			
			$submission_date=$json[$jobId]['portal-submission-date'];
			$job_name=$jobId;
			$id=key($json[$jobId]['submissions']);//key($lastSubmition);
			
			#creating file parameters.txt
			$parameters_txt= fopen($temp."/parameters.txt", "w");
			$text="INPUT FILES\n\nprotein = ".$protein."\nligand set size = ".$numOfLigands."\nligand files = ";
			
			$ligands_csv= array();
			$file_csv=fopen($temp."/LIGAND/ligand_mapfile.csv","r");
			while(($line = fgetcsv($file_csv)) !== false){
				$ligands_csv[]=$line;
			}
			fclose($file_csv);
			$ligands_csv_lines=sizeof($ligands_csv);
			
			$i=0;
			foreach($ligands_csv as $ligand){
				if($i>0){
					$text=$text.$ligand[1];
				}
				$i++;
				if($i==$ligands_csv_lines){
					$text=$text."\ncofactor set size = ";
				}else if($i>1){
					$text=$text.";";
				}
			}
		
			$text=$text.$numOfCofactors."\ncofactor files = ";
			
			$cofactors_csv= array();
			if(file_exists($temp."/COFACTOR/cofactor_mapfile.csv")){
				$file_csv=fopen($temp."/COFACTOR/cofactor_mapfile.csv","r");
				while(($line = fgetcsv($file_csv)) !== false){
					$cofactors_csv[]=$line;
				}
				fclose($file_csv);
				$cofactors_csv_lines=sizeof($cofactors_csv);
					
				$i=0;
				foreach($cofactors_csv as $cofactor){
					if($i>0){
						$text=$text.$cofactor[1];
					}
					$i++;
					if(($i!=$cofactors_csv_lines) && ($i>1)){
						$text=$text.";";
					}
				}
			}
			
			$text=$text."\n\n";
			
			$text=$text."GRID SETTINGS\n\ncenter x = ".$lastSubmition['args']['gc']['xc']."\ncenter y = ".$lastSubmition['args']['gc']['yc']."\ncenter z = ".$lastSubmition['args']['gc']['zc']."\ntotal size x = ".$sizex."\ntotal size y = ".$sizey."\ntotal size z = ".$sizez."\ndiscretization = ".$discretization."\n\n";
			$text=$text."GENETIC ALGORITHM SETTINGS\n\nnumber of evaluations = ".$number_of_evaluations."\npopulation size = ".$population_size."\nnumber of runs = ".$number_of_runs."\nseed at run #1 = ".$seed."\n\n";
			$text=$text."JOB INFO\n\nsubmission date = ".$submission_date."\njob name = ".$job_name."\nID = ".$id;
			
			fwrite($parameters_txt, $text);
			fclose($parameters_txt);
			
			shell_exec("rm -f $temp/RESULT/snap.txt");
			
			shell_exec("rm -rf $temp/properties.json");
		
			/////////////// Zip temp files
			
			$command = "cd $temp; rm ../$jobId.zip; ";
			$commandResult = shell_exec($command);
			
			/*
			$command = "cd $temp; rm ../$jobId.zip; zip -r ../$jobId.zip .";
			syslog(LOG_INFO|LOG_LOCAL0, "DOWNLOAD: zipCommand:$command");
			shell_exec($command);
			*/
			
			$command = "cd $temp; zip -r ../$jobId.zip . -x 'RESULT/*'"; 
			syslog(LOG_INFO|LOG_LOCAL0, "DOWNLOAD: prepare for zip commands: $command");
			shell_exec($command);
			
			$command = "cd $temp; zip -r ../$jobId.zip . -x 'RESULT/*_top[-0-9].*' 'RESULT/dockthor.out' 'RESULT/*.inf'";
			syslog(LOG_INFO|LOG_LOCAL0, "DOWNLOAD: zip command for 1L and VS: $command");
			shell_exec($command);			
			
            header ( "Location: ../session-files/$session_id/RESULTS/$jobId/$jobId.zip");
		}
	} else if ($action =='REMOVE-ALL-FILES-FROM-JOB'){

		if (isset ( $_GET["jobId"] )) {
			
			$jobId = $_GET["jobId"];

			$illegalChar = array(".", ",", "?", "!", ":", ";", "-", "+", "<", ">", "%", "~", "€", "$", "[", "]", "{", "}", "@", "&", "#", "*", "„");
                	$jobId = str_replace($illegalChar, "", $jobId);
			
			$utils = new Utils();
			
			// Clear dir
			$result = $utils->clearDir("../daemon/jobs/$jobId");

			if($result){
				
				// Remove dir
				$result = rmdir("../daemon/jobs/$jobId");
				if($result){
					
					// Remove referencia (success)
					$result = unlink("../daemon/success/$jobId");
					if($result){
						$response['status'] = 'SUCCESS';
					}else{
						$response['status'] = 'ERR';
						$response['errorMessage'] = "Failed to delete success file!";
					}				
					
				}else{
					$response['status'] = 'ERR';
					$response['errorMessage'] = "Failed to delete $jobId folder!";
				}	
				
			}else{
				$response['status'] = 'ERR';
				$response['errorMessage'] = "Failed to clear $jobId folder!";
			}
			
		}else{
			$response['status'] = 'ERR';
			$response['errorMessage'] = "Failed to get jobId $jobId!";
		}
		
	}
	
}else{
	$response['status'] = 'ERR';
}

echo json_encode ($response);

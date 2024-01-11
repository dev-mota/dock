<?php
require_once '../environment/datasets-environment.php';
include "../pdbthorbox/pdbthorbox.php";
include_once "../lib/utils/utils.php"; 
include '../lib/utils/file-validator.php';
include_once '../prepared-files-app/lib/preparedResourcesLib.php'; 

/** https://tools.ietf.org/html/rfc5424#section-6.3 */
openlog("[dockthor][docking][protein-action.php]", LOG_PID | LOG_PERROR, LOG_LOCAL0);

session_start ();
$session_id = session_id ();

$action = '';

if (isset ( $_FILES ['files'])) {
	$action = $_POST ['action'];
} else {
	$postdata = file_get_contents ( "php://input" );
	$request = json_decode ( $postdata );
	$action = $request->params->action;
}

if(isset ( $_GET ['action'])){
	$action = $_GET ['action'];
}

$response = array();
$utils = new Utils();

$proteinTestFile = "../test-files/1hpv_protein.pdb";
$proteinTestFileParts = pathinfo($proteinTestFile);

$testBasePath = "../test-files/";
//$targetBasePath = "../../../datasets/"; 
$targetBasePath = $GLOBALS['DATASETS_DIR']."/";

if ( isset($action) && ($session_id!=null) ) {

	syslog(LOG_INFO|LOG_LOCAL0, $action);	
	
	if($action=='GET-PREDEFINED-FILE-INFO'){
		
		$type = $request->params->type; // test or target
		
		$filePath = "";		
		if($type == 'test'){			
			$filePath = $testBasePath.$request->params->filePath;
		} else if ($type == 'target'){
			$filePath = $targetBasePath.$request->params->filePath;
		} else {
			syslog(LOG_INFO|LOG_LOCAL0, "GET-PREDEFINED-FILE-INFO file type not allowed ($type) ");
			http_response_code(500);
		}
		// syslog(LOG_INFO|LOG_LOCAL0, $filePath);
		
		if(is_file($filePath)){
			// Create file info object
			$filePathParts = pathinfo($filePath); // pathinfo lê somente o path dado, sem considerar se o arquivo existe ou não... 	
			$file = [];
			$file['name'] = $filePathParts['basename'];
			$file['codedName'] = $filePathParts['basename'];
			$file['codedName_pdb'] = $filePathParts['filename']."_prep.".$proteinTestFileParts['extension'];
			$file['isPredefinedFile'] = true;
			$file['prepared'] = false;
			// $file['size'] = number_format(filesize($filePath)/1024,2).' KB';
			$file['size'] = filesize($filePath);
			$file['content'] = file_get_contents($filePath);
			
			if($type == 'test'){
				$file['isTestFile'] = true;
			}
			
			// Response		
			$response ["file"] = $file;			
		} else{
			syslog(LOG_INFO|LOG_LOCAL0, "GET-PREDEFINED-FILE-INFO: file path not exist!");
			http_response_code(500);
		}		
		
	} else if($action=='PREPARE'){
	
		$file_name = $request->params->fileName;
		$prepare_result = pdbthorboxPrepare($session_id, $file_name);
		
		if ($prepare_result['SUCCESS']) {
			$response['operationStatus'] = 'SUCCESS';
			$response['chains'] = $prepare_result['CHAINS'];
		} else {
			$response['operationStatus'] = 'ERROR';
			$response['errorMessage'] = 'Invalid File Structure';
		}
		
	} else if ($action=='PREPARE-TEST-FILE'){		
		
		$filePath = $testBasePath.$request->params->filePath;
		
		$session_dir = "../session-files/$session_id";
		if (!file_exists ( $session_dir ) || ! is_dir ( $session_dir )) {
			mkdir ( $session_dir );
		}
		
		$protein_dir = "$session_dir/PROTEIN";
		if (!file_exists($protein_dir) || ! is_dir($protein_dir) ) {
			mkdir ($protein_dir);
		} else {
			$utils->clearDir ( $protein_dir );
		}		
		
		$filePathParts = pathinfo($filePath); // pathinfo lê somente o path dado, sem considerar se o arquivo existe ou não... 	
		// copy ( $proteinTestFile, $protein_dir."/".$proteinTestFileParts['basename'] );
		copy($filePath, $protein_dir."/".$filePathParts['basename']);
	
		$prepare_result = pdbthorboxPrepare($session_id, $proteinTestFileParts['basename']);
		if ($prepare_result['SUCCESS']) {
			$response['operationStatus'] = 'SUCCESS';
			$response['chains'] = $prepare_result['CHAINS'];
		} else {
			$response['operationStatus'] = 'ERROR';
			$response['errorMessage'] = 'Invalid File Structure';
		}
		
	} else if ($action=='DOWNLOAD-FILE') {
		$file_type = $_GET['type'];
		$file_name = preg_replace ( '/\\.[^.\\s]{2,3}$/', '', $_GET['fileName'] ); // retirando extensão
		switch ($file_type) {
			case 'zip' :
				header ( "Location: ../session-files/$session_id/PROTEIN/$file_name.zip" );
				break;
			case 'prep' :
				header ( "Location: ../session-files/$session_id/PROTEIN/$file_name" . "_prep.pdb" );
				break;
			case 'in' :
				$file = "../session-files/$session_id/PROTEIN/$file_name.in";
				header("Cache-Control: public");
				header("Content-Description: File Transfer");
				header("Content-Disposition: attachment; filename=$file_name.in");
				header("Content-Type: application/zip");
				header("Content-Transfer-Encoding: binary");
					
				// read the file from disk
				readfile($file);
				break;
			case 'pdb' :
				header ( "Location: ../session-files/$session_id/PROTEIN/$file_name.pdb" );
				break;
			default :
				break;
		}
	} else if ($action=='SAVE-FILE'){		
		
		$file = $_FILES['files'];
		
		syslog(LOG_INFO|LOG_LOCAL0, "$action file:".json_encode($file));
		
		$session_dir = "../session-files/$session_id";
		if (! file_exists ( $session_dir ) || ! is_dir ( $session_dir )) {
			mkdir ( $session_dir );
		}
		
		$response ["files"] = array ();
		$response['operationStatus'] = 'STARTING';
		
		$fileNameId = substr( md5(microtime()), rand(0, 26), 10);
		$fileExtension = array_pop(explode('.', $file['name']));
		
		if(!($fileExtension == "pdb" || $fileExtension == "in")){
			$response['operationStatus'] = 'ERROR';
			$response['errorMessage'] = 'File type not allowed';
		}
		else {
			$fileNameId = "protein_$fileNameId";
			saveFile($file, "$session_dir/PROTEIN", "$fileNameId.$fileExtension" );
		}
		if($response['operationStatus'] != 'ERROR'){
			if (FileValidator::isEmpty ( "$session_dir/PROTEIN/$fileNameId.$fileExtension" )) {
				$response['operationStatus'] = 'ERROR';
				$response['errorMessage'] = 'Empty file';
			} else {
				
				$responseFile = array ();
				$responseFile ["name"] = $fileNameId;
				$responseFile ["size"] = $file['size'];
				$responseFile ["thumbnailUrl"] = "apps/docking/session-files/$session_id/PROTEIN/" . $file['name'];
				$responseFile ["deleteUrl"] = "apps/docking/session-files/$session_id/PROTEIN/" .$file['name'];
				$responseFile ["deleteType"] = "DELETE";
				$responseFile ["state"] = "pending";
				$responseFile ['nameWithExtension'] = "$fileNameId.$fileExtension";
				array_push($response["files"], $responseFile);
				
				$response['operationStatus'] = 'SUCCESS';
			}
		}
		
	} else if ($action=='SEND-TO-DOCK'){
		$docking_dir = "../session-files/$session_id/DOCKING";
		if(!file_exists($docking_dir) || !is_dir($docking_dir)){
			mkdir($docking_dir);
		}
		
		if(!file_exists("$docking_dir/PROTEIN") || !is_dir("$docking_dir/PROTEIN")){
			mkdir("$docking_dir/PROTEIN");
		} else {
			shell_exec("rm $docking_dir/PROTEIN/*");
		}
		
		shell_exec("cp ../session-files/$session_id/PROTEIN/*_prep.pdb $docking_dir/PROTEIN/");
		shell_exec("cp ../session-files/$session_id/PROTEIN/*.in $docking_dir/PROTEIN/");
	} else if ($action == "LOAD-PREPARED-RESOURCES"){
		
		// syslog(LOG_INFO|LOG_LOCAL0, "Action: ".$action);
		
		$response["data"] = array ();
		
		try{
			$preparedResourcesLib = new PreparedResourcesLib();
			$data = $preparedResourcesLib->loadStructure("datasets/targets/covid-19");
			
			// syslog(LOG_INFO|LOG_LOCAL0, "SUCCESS");
			$response ["data"] = $data;	
		}catch(Exception $e){
			syslog(LOG_INFO|LOG_LOCAL0, $e->getMessage());
			http_response_code(500);
		}
		
	}
}

echo json_encode ( $response );

function pdbthorboxPrepare($session_id, $file_name){
	$pdbthorbox = new Pdbthorbox($session_id);
	$prepare_result = $pdbthorbox->prepare ( $file_name );
	return $prepare_result;
}

function saveFile($file, $path_to, $fileNameIdWithExtension) {
	$utils = new Utils();
	if (! file_exists ( $path_to ) || ! is_dir ( $path_to )) {
		mkdir ( $path_to );
	} else {
		$utils->clearDir ( $path_to );
	}
	move_uploaded_file ( $file ['tmp_name'], "$path_to/" . $fileNameIdWithExtension );

	$command="python3 ". $GLOBALS['NORMALIZE_SCRIPT'] . " " . $path_to . "/" .  $fileNameIdWithExtension;

	syslog(LOG_INFO|LOG_LOCAL0, $command);

	$normalizeOutput=shell_exec($command);

	syslog(LOG_INFO|LOG_LOCAL0, $normalizeOutput);

	return $file;
}
?>

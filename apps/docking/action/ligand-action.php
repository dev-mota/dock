<?php
require_once '../environment/datasets-environment.php';
include "../mmffligand/mmffligand.php";
include "../lib/utils/utils.php";
include '../lib/utils/file-validator.php';

/** https://tools.ietf.org/html/rfc5424#section-6.3 */
openlog("[dockthor][docking][ligand-action.php]", LOG_PID | LOG_PERROR, LOG_LOCAL0);

session_start ();
$session_id = session_id ();
$debug = false;

function saveLigandFile($fileRequest, $fileIndex, $util, $session_dir, $session_id){
	
	// syslog(LOG_INFO|LOG_LOCAL0, "saveLigandFile(".json_encode($fileRequest).", $fileIndex) - START");
	
	$response = array ();
	
	$response['errorMessage'] = '';
	$response['state'] = '';
	$response['originalName'] = $fileRequest['name'];		
	$response['size'] = $fileRequest['size'];
	$response['index'] = $fileIndex;
	
	$fileId = $util->generateFileId();
	$fileNameId = "ligand_$fileId";
	$fileExtension = array_pop ( explode ( '.', $fileRequest['name']) );
	$response ["fileId"] = $fileNameId;
	$response ['fileIdWithExtension'] = "$fileNameId.$fileExtension";
	$response ['fileExtension'] = $fileExtension;
	
	$fileDirResponse = "apps/docking/session-files/$session_id/LIGAND/INPUT/";
	$response ["thumbnailUrl"] = $fileDirResponse . $fileRequest['name'];
	$response ["deleteUrl"] = $fileDirResponse . $fileRequest['name'];
	$response ["deleteType"] = "DELETE";
		
	if(FileValidator::isEmpty ( $fileRequest['tmp_name'])){		
		$response['state'] = 'save-error';
		$response['errorMessage'] = 'Empty file';	
	}else{
		
		//syslog(LOG_INFO|LOG_LOCAL0, "File tmp not empty:".$fileRequest['name']);
		
		$tmpFilePath = $fileRequest['tmp_name'];
						
		if(!($fileExtension == "pdb" || $fileExtension == "mol2" || $fileExtension == "sdf" || $fileExtension == "top")){
		
			$response['state'] = 'save-error';
			$response['errorMessage'] = 'Internal Error - save file code error 001';
			syslog(LOG_ERR|LOG_LOCAL0, $response['errorMessage']." - User was able to upload a not allowed file type (tmpFilePath: $tmpFilePath; fileNameId: $fileNameId; fileDirResponse: $fileDirResponse; fileExtension: $fileExtension)");
			
		}else {			
			
			//syslog(LOG_INFO|LOG_LOCAL0, "File extension allowed:".$fileRequest['name']);
			$dirToSave = "$session_dir/LIGAND/INPUT";
			$fileNameToSave = "$fileNameId.$fileExtension";
			$saveFileResult = $util->saveFile($dirToSave, $fileNameToSave,$tmpFilePath );			
			if(!$saveFileResult){
				
				$response['state'] = 'save-error';
				$response['errorMessage'] = 'Internal Error - save file code error 002';
				syslog(LOG_ERR|LOG_LOCAL0, $response['errorMessage'].' - Utils.saveFile could not save the file (tmpFilePath: $tmpFilePath; fileNameId: $fileNameId; fileDirResponse: $fileDirResponse; fileExtension: $fileExtension)');
				
			} else {
				
				//syslog(LOG_INFO|LOG_LOCAL0, "File save success:".$fileRequest['name']);
				
				if(!isset($fileDirResponse)){
					
					$response['state'] = 'save-error';
					$response['errorMessage'] = 'Internal Error - save file code error 003';
					syslog(LOG_ERR|LOG_LOCAL0, $response['errorMessage']." ERROR: Internal error when saving ligand file ($fileDirResponse)");
				  
				}else{
					
					// Enfin, se tudo der certo:
					$response['errorMessage'] = '';
					$response['state'] = 'save-success';						
					// syslog(LOG_INFO|LOG_LOCAL0, "tudo deu certo:".json_encode($file));
				}
				
			}
		}
		
	}
	
	// syslog(LOG_INFO|LOG_LOCAL0, "User upload a ligand file successfully: ".json_encode($file, JSON_UNESCAPED_SLASHES));
	// array_push ( $response ["file"], $file );
	
	// syslog(LOG_INFO|LOG_LOCAL0, "saveLigandFile(".json_encode($fileRequest).", $fileIndex) - END");
	return $response;
}

$action = '';
if (isset ( $_FILES ['file']) || isset($_FILES ['files'])) {
	$action = $_POST ['action'];
}else{
	$postdata = file_get_contents ( "php://input" );
	if(!empty($postdata)){
		$request = json_decode ( $postdata );
		if(isset($request->params->action)){
			$action = $request->params->action;
		}
		
	}	
}

if(isset ( $_GET ['action'])){
	$action = $_GET ['action'];
}

$response = array();
$util = new Utils();

$session_dir = "../session-files/$session_id";

if ( isset($action) && ($session_id!=null) ) {
	
	if($action=='PREPARE'){		
		
		$mmffligand = new MMFFligand ( $session_id, 'LIGAND' );
		$file = $request->params->file;
		//$prepare_result = $mmffligand->prepareMultFiles($files);
		$response = $mmffligand->prepareOneFile($file);
		
// 		if ($prepare_result['SUCCESS']) {
// 			$response['file'] = $prepare_result['file'];			
// 		} else {
// 			$response['operationStatus'] = 'ERROR';
// 			$response['errorMessage'] = array();
			
// 			foreach ($files as $file_key => $file_value){
// 				$index = array_search($file_key, $prepare_result['ERROR_FILES']);
// 				if(is_numeric($index)){
// 					$response['errorMessage'][$file_key] = 'Invalid File Structure';
// 				}
// 			}
// 		}
		
	} else if ($action=='PREPARE-MULT'){		
		
		if($debug){
			syslog(LOG_DEBUG|LOG_LOCAL0, "$action: start");
		}
		
		$mmffligand = new MMFFligand($session_id,'LIGAND' );
		$files = $request->params->files;
		// syslog(LOG_INFO|LOG_LOCAL0, "PREPARE-MULT! ".json_encode($files));
		
		foreach ($files as $file_key => $file_value){
			$response[] = $mmffligand->prepareOneFile($file_value);
		}
		
		if($debug){
			syslog(LOG_DEBUG|LOG_LOCAL0, "$action: finish");
		}
		
	} else if($action=='SELECT-TEST-FILE'){		
		
		$test_type = $request->params->test_type;
		
		$util->resetLigandStructureDir($session_id);
		$fileId = $util->generateFileId();
		$fileExtension = "";
		if ($test_type == 'SINGLE_DOCKING') {
			$fileExtension = "pdb";
			#$testFileName = "test_caq_ref.$fileExtension";
			$testFileName = "1hpv_ligand.$fileExtension";
			$nameWithExtension = "ligand_$fileId.$fileExtension";			
		} else if ($test_type == 'VIRTUAL_SCREENING') {
			#$fileExtension = "mol2";
			$fileExtension = "sdf";
			#$testFileName = "test_zinc.$fileExtension";
			$testFileName = "bindingdb_vs_hiv.$fileExtension";
			$nameWithExtension = "ligand_$fileId.$fileExtension";
		}
		
		//shell_exec("cp ../test-files/$testFileName ../session-files/$session_id/LIGAND/INPUT/$nameWithExtension");
		
		$files[0] = (object) [
				'fileId' =>"ligand_$fileId",
				'name' => $testFileName,
				'fileExtension' => $fileExtension,
				'nameWithExtension' => $nameWithExtension,
				'fileIdWithExtension' => "ligand_$fileId.$fileExtension",
				'originalName' => $testFileName,
				'size' => filesize("../test-files/$testFileName"),
				'hidrogen' => false
		];
		
// 		$mmffligand = new MMFFligand ( $session_id, 'LIGAND' );
// 		$file = $files[0];		
// 		$response = $mmffligand->prepareOneFile($file);
		
		$response['operationStatus'] = 'SUCCESS';
		//$response['files'] = $files;
		$response['file'] = $files[0];
		
	} if($action=='SAVE-TEST-FILE'){		
		
		$file = $request->params->file;
		$originalName = $file->originalName;
		$nameWithExtension = $file->nameWithExtension;
		
		if(!is_dir("../session-files/$session_id/LIGAND/INPUT")){
			mkdir("../session-files/$session_id/LIGAND/INPUT");
		}
		
		$saveCmd = "cp ../test-files/$originalName ../session-files/$session_id/LIGAND/INPUT/$nameWithExtension"; 
		shell_exec($saveCmd);
		
		$response['operationStatus'] = 'SUCCESS';
		
		
	} else if ($action == 'DOWNLOAD-FILE'){
		$file_type = $_GET['type'];
		$dir = "../session-files/$session_id/LIGAND";
		
		switch ($file_type) {
			case 'zip' :
				shell_exec("cd $dir; zip -r LIGAND.zip *;");
				header ( "Location: $dir/LIGAND.zip" );
				break;
			case 'topZip' :
					if(!file_exists("$dir/ligand_top_files.zip")){
						mkdir("$dir/ligand_top_files");
						foreach ( glob ( "$dir/OUTPUT/*" ) as $dir_id ) {
							foreach ( glob ( "$dir_id/*.top" ) as $top_file ) {
									copy($top_file, "$dir/ligand_top_files/" . basename($top_file));
							}
						}
						
						shell_exec("cd $dir; zip -r ligand_top_files.zip ligand_top_files; rm -rf ligand_top_files");
					}
					
					header ( "Location: $dir/ligand_top_files.zip" );
					break;
			case 'map' :
					header ( "Location: $dir/ligand_mapfile.csv" );
					break;
			case 'tableZip' :
					if(!file_exists("$dir/table.zip")){
						mkdir("$dir/table_files");
						foreach ( glob ( "$dir/OUTPUT/ligand_*" ) as $dir_id ) {
							
							$pos = strrpos($dir_id, "/"); //   ../session-files/hfdhqtvqkvoh5sipn3i7fjcqg0/LIGAND/OUTPUT/ligand_f908e9864b
							$ligandId = substr($dir_id, $pos+1, strlen($dir_id)-1);
							
							//Valid structure:
							//update file with valid structure
							$validStructureCommand = "cd $dir/OUTPUT/$ligandId/; ls -1 *.top | wc -l";
							$topCount = shell_exec($validStructureCommand);
							$topCount = preg_replace('~[\r\n]+~', '', $topCount);//removing \n
							
							if($topCount>0){
								//mkdir("$dir/statistics_files/$ligandId");
								copy("$dir_id/obprop/obprop.csv", "$dir/table_files/$ligandId-obprop.csv");
							}
							
						}
						shell_exec("cd $dir; zip -r table.zip table_files; rm -rf table_files");
					}
					
					header ( "Location: $dir/table.zip" );
					break;
			default :
				break;
		}
// REMOVIDO - essa informação ja vem ao executar o mmffligand
// 	} else if($action=='checkIfHasOneTopFile'){

// 		$response['checkIfHasOneTopFileResponse'] = 'FALSE';		
// 		$files = $request->params->files;
		
// 		if(isset($files)){
			
// 			$filesArray = (array)$files;
			
// 			if(count($filesArray)==1){
			
// 				$fileArray = (array)$filesArray[0];
// 				$id = $fileArray['name'];
// 				$rows = exec("cd ../session-files/$session_id/LIGAND/OUTPUT/$id; ls *.top",$output,$sessionFileError);
// 				while(list(,$row) = each($output)){
// 					$trete = $row;
// 				}
// 				$numOfTopFiles = count($output);
// 				if($numOfTopFiles==1){ //$output - array with each top file founded
// 					$response['checkIfHasOneTopFileResponse'] = 'TRUE';
// 				}
// 			}			
// 		}


	} else if($action=='checkTopHasSelectedTorsion'){
		
		if($debug){
			syslog(LOG_DEBUG|LOG_LOCAL0, "$action: start");
		}
		
		$response['status'] = 'FALSE';
		
		$file = $request->params->file;
		$fileId = $request->params->file->fileId;
		$fileIdUnderline = $fileId."_1";
		
		$targetFile = "../session-files/$session_id/LIGAND/OUTPUT/$fileId/$fileIdUnderline.top";
		
		if(is_file($targetFile)){
			
			$checkTopHasSelectedTorsionCommand = "cat $targetFile;";
			if($debug){
				syslog(LOG_DEBUG|LOG_LOCAL0, "checkTopHasSelectedTorsionCommand: $checkTopHasSelectedTorsionCommand");
			}			
			
			$resultTopContent = shell_exec($checkTopHasSelectedTorsionCommand);			
			
			$arr = explode("\n", $resultTopContent);
			for ($i = 0; $i < count($arr); $i++) {
					
				$stringTarget = substr( $arr[$i], 0, 9 );
				if($debug){
					syslog(LOG_DEBUG|LOG_LOCAL0, $stringTarget."(".$arr[$i].")");
				}	
					
				if( $stringTarget === '$SELECTED'){
					// $lineArray = explode("   ", $arr[$i]);
					$lineArray = preg_split('/\s+/', $arr[$i]);
					
					if($debug){
						syslog(LOG_DEBUG|LOG_LOCAL0, json_encode($lineArray));
					}	
					
					if(isset($lineArray[1]) && $lineArray[1] != '0'){
						$response['status'] = 'SUCCESS';
					}else{
						$response['status'] = 'FAILED';
					}
					break;
				}
				
			}
			
		} else {
			syslog(LOG_ERR|LOG_LOCAL0, "$action: failed to access the target file: ".$targetFile);
		}
		
		if($debug){
			syslog(LOG_DEBUG|LOG_LOCAL0, "$action: finish");
		}
		
	} else if($action=='removeFile'){
		
		$response['removeFileResponse'] = '';
		
		try {
			$fileIdWithExtension = $request->params->fileIdWithExtension;
			$fileId = preg_replace ( '/\\.[^.\\s]{2,4}$/', '', $fileIdWithExtension ); // retirando extensão
			
			shell_exec("rm     ../session-files/$session_id/LIGAND/INPUT/$fileIdWithExtension");
			shell_exec("rm -rf ../session-files/$session_id/LIGAND/OUTPUT/$fileId/");
			
			//Rebuild/delete ligand_mapfile
			$sessionFileRow = exec("find ../session-files/$session_id/LIGAND/INPUT/ -maxdepth 1 -type f",$fileOutput,$sessionFileError);
			if($sessionFileRow == ""){//remove file
				shell_exec("rm ../session-files/$session_id/LIGAND/ligand_mapfile.csv");				
			}else{//update file
				$updateCommand = "cd ../session-files/$session_id/LIGAND/; sed '/$fileId/d' ligand_mapfile.csv | tee ligand_mapfile.csv";
				shell_exec($updateCommand);
			}
			
			$response['removeFileResponse'] = 'SUCCESS';
		} catch (Exception $e) {
			$response['removeFileResponse'] = 'FAIL';
		}		
	} else if($action == 'clearLigandSession'){
		
		$util = new Utils();		
		$session_dir = "../session-files/$session_id";
		if (! file_exists ( $session_dir ) || ! is_dir ( $session_dir )) {
			mkdir ( $session_dir );
		}
		
		$util->resetLigandStructureDir($session_id);
		
		$data['status'] = 'ERR';
		$session_dir = "../session-files/$session_id/LIGAND";
		
		if (file_exists ($session_dir) || is_dir ($session_dir) ) {
			
			$result = $util->clearDir($session_dir);
			if($result){
				$response['status'] = 'OK';
			}
		
		}
		
	} else if($action == 'SAVE-FILE'){
		
		$fileRequest = $_FILES ['file'];
		$fileIndex = $_POST['fileIndex'];		
		$response = saveLigandFile($fileRequest, $fileIndex, $util, $session_dir, $session_id);
				
	} else if($action == 'SAVE-FILES'){
		
		if($debug){
			syslog(LOG_DEBUG|LOG_LOCAL0, "$action: start");
		}
		
		$filesRequest = $_FILES['files'];		
		
		/* Convert format, as below: */
		// From: :{"name":["fda-having-reference_first2.sdf","isabella_1.mol2"],"type":["application\/octet-stream","application\/octet-stream"],"tmp_name":["\/tmp\/phpfccWhs","\/tmp\/phpjo7ygI"],"error":[0,0],"size":[9149,4406]}
		// To: :[{"name":"fda-having-reference_first2.sdf","type":"application\/octet-stream","tmp_name":"\/tmp\/phpfccWhs","error":0,"size":9149},{"name":"isabella_1.mol2","type":"application\/octet-stream","tmp_name":"\/tmp\/phpjo7ygI","error":0,"size":4406}]
		// syslog(LOG_INFO|LOG_LOCAL0, "$action: \$_FILES mult format:".json_encode($filesRequest));
		$files = array();
		for ($i=0; $i<count($filesRequest['name']); $i++){
			$files[$i] = array(
				'name' => $filesRequest['name'][$i],
				'type' => $filesRequest['type'][$i],
				'tmp_name' => $filesRequest['tmp_name'][$i],
				'error' => $filesRequest['error'][$i],
				'size' => $filesRequest['size'][$i]
			);
		}		
		//syslog(LOG_INFO|LOG_LOCAL0, "$action: \$_FILES single format:".json_encode($files));
		
		// Save each file
		foreach($files as $key=>$value){
			$response[] = saveLigandFile($value, $key, $util, $session_dir, $session_id);
		}
		
		if($debug){
			syslog(LOG_DEBUG|LOG_LOCAL0, "$action: finish");
		}
		
		// syslog(LOG_INFO|LOG_LOCAL0, "$action: response:".json_encode($response));
		
	} else if ($action=='SEND-TO-DOCK'){
		$docking_dir = "../session-files/$session_id/DOCKING";
		if(!file_exists($docking_dir) || !is_dir($docking_dir)){
			mkdir($docking_dir);
		}
		
		if(!file_exists("$docking_dir/LIGAND") || !is_dir("$docking_dir/LIGAND")){
			mkdir("$docking_dir/LIGAND");
		} else {
			shell_exec("rm -r $docking_dir/LIGAND/*");
		}
		
		shell_exec("cp -r ../session-files/$session_id/LIGAND/* $docking_dir/LIGAND/");

	} else if ($action=='CHECK_EQUALS'){
		
		$utils = new Utils();
		
		$files = $request->params->files;
		$response = $utils->checkEqualFiles($files,$session_dir."/LIGAND/INPUT/");

	} else if ($action=='SAVE-RESOURCE-FILES'){
		$resource = $request->params->resource;
		
		syslog(LOG_INFO|LOG_LOCAL0, "action:$action: resource:$resource");
		
		$targetFolder = "../session-files/$session_id/LIGAND";
		if(!is_dir($targetFolder)){
			mkdir($targetFolder);			
		} 
		
		if(is_dir($targetFolder)){

			$illegalChar = array("..", ",", "?", "!", ":", ";", "+", "<", ">", "%", "~", "€", "$", "[", "]", "{", "}", "@", "&", "#", "*", "„");
      			$resource = str_replace($illegalChar, "", $resource);

			
			//$sourceFolder = __DIR__."/../../../datasets/compounds/".$resource;  // compounds é dir exclusivo do banco de ligands
			$sourceFolder = $GLOBALS['DATASETS_DIR'] ."/compounds/".$resource;  // compounds é dir exclusivo do banco de ligands
			
			if(is_dir($sourceFolder)){				
								
				// Build specific queue
				$mapFileContent = "";
				$response['queue_resources'] = [];
				$queue = scandir($sourceFolder);
				
				syslog(LOG_INFO|LOG_LOCAL0, "action:$action: start to create ...");
				shell_exec("mkdir -p $targetFolder/INPUT;");
				shell_exec("mkdir -p $targetFolder/OUTPUT;");
				$commandInput = "";
				$commandOutput = "";
				for($i=0;$i<count($queue);$i++){
					if( ($queue[$i] != "info.txt") && ($queue[$i] != ".") && ($queue[$i] != "..")){
						
						$fileId = $util->generateFileId();
						$pathInfo = pathinfo($queue[$i]);
						$fileIdWithExtension = "ligand_$fileId.".$pathInfo['extension'];
						$fileIdWithNoExtension = "ligand_$fileId";
						
						$mapFileContent .= "$queue[$i],$fileIdWithExtension\n";
						
						// run.php precisa disso!
						// input
						// shell_exec("mkdir -p $targetFolder/INPUT; cp $sourceFolder/$queue[$i] $targetFolder/INPUT/$fileIdWithExtension");
						// shell_exec("mkdir -p $targetFolder/INPUT; ln -s $sourceFolder/$queue[$i] $targetFolder/INPUT/$fileIdWithExtension");
						//$commandInput .= "ln -s $sourceFolder/$queue[$i] $targetFolder/INPUT/$fileIdWithExtension;";
						$commandInput = "ln -s $sourceFolder/$queue[$i] $targetFolder/INPUT/$fileIdWithExtension;";
						shell_exec($commandInput);
						// output
						// shell_exec("mkdir -p $targetFolder/OUTPUT/ligand_$fileId; cp $sourceFolder/$queue[$i] $targetFolder/OUTPUT/ligand_$fileId/$fileIdWithExtension");
						// shell_exec("mkdir -p $targetFolder/OUTPUT/ligand_$fileId; ln -s $sourceFolder/$queue[$i] $targetFolder/OUTPUT/ligand_$fileId/$fileIdWithExtension");
						//$commandOutput .= "mkdir -p $targetFolder/OUTPUT/ligand_$fileId; ln -s $sourceFolder/$queue[$i] $targetFolder/OUTPUT/ligand_$fileId/$fileIdWithExtension;";
						$commandOutput = "mkdir -p $targetFolder/OUTPUT/ligand_$fileId; ln -s $sourceFolder/$queue[$i] $targetFolder/OUTPUT/ligand_$fileId/$fileIdWithExtension;";
						shell_exec($commandOutput);
						
						$response['queue_resources'][] = [
							"fileId"=>$queue[$i],
							"validStructure"=>1,
							"fileIdWithExtension"=>$fileIdWithExtension,
							"fileId"=>$fileIdWithNoExtension
						];		
					}
				}
				//shell_exec($commandInput);
				//shell_exec($commandOutput);
				syslog(LOG_INFO|LOG_LOCAL0, "action:$action: finish to create");
				$response['valid'] = count($response['queue_resources']);
				// syslog(LOG_INFO|LOG_LOCAL0, "action:$action: queue:".json_encode($response['queue_resources']));		
				
				// Build map file
				$mapFileLocation = "../session-files/$session_id/LIGAND";
				if(is_dir($mapFileLocation)){
					$mapFileNameIdPath = "$mapFileLocation/ligand_mapfile.csv";		
					$header = "";
					if(!file_exists($mapFileNameIdPath)){
						$header = '"Original file name","Coded name"'."\n";
					}
					$mapFile = fopen($mapFileNameIdPath, 'a');
					fwrite($mapFile, $header.$mapFileContent);
					fclose($mapFile);					
							
					http_response_code(200);
				} else {
					$response['err'] = 'could not create map file';
					http_response_code(500);
				}

			} else {
				$response['err'] = 'could not access resource folder';
				http_response_code(500);
			}
		} else {
			$response['err'] = 'could not source folder error';
			http_response_code(500);
		}
		
	}
	
}

echo json_encode ( $response );

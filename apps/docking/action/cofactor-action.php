<?php
include "../mmffligand/mmffligand.php";
include "../lib/utils/utils.php";
include '../lib/utils/file-validator.php';

session_start ();
$session_id = session_id ();

$action = '';

if (isset ( $_FILES ['file']) || isset($_FILES ['files'])) {
	$action = $_POST ['action'];
}else{
	$postdata = file_get_contents ( "php://input" );
	if(!empty($postdata)){
		$request = json_decode ( $postdata );
		$action = $request->params->action;
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

		$mmffligand = new MMFFligand ( $session_id, "COFACTOR" );
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

	}else if($action=='SELECT-TEST-FILE'){
	
		$test_type = $request->params->test_type;
	
		$util->resetCofactorStructureDir($session_id);
		$fileId = $util->generateFileId();
		$fileExtension = "";
		if ($test_type == 'SINGLE_DOCKING') {
			$fileExtension = "pdb";
			//$testFileName = "cofactor.$fileExtension";
			$testFileName = "1hpv_water.$fileExtension";
			$nameWithExtension = "cofactor_$fileId.$fileExtension";
		} 
		
	// 	else if ($test_type == 'VIRTUAL_SCREENING') {
	// 		$fileExtension = "mol2";
	// 		$testFileName = "test_zinc.$fileExtension";
	// 		$nameWithExtension = "ligand_$fileId.$fileExtension";
	// 	}
	
		//shell_exec("cp ../test-files/$testFileName ../session-files/$session_id/LIGAND/INPUT/$nameWithExtension");
	
		$files[0] = (object) [
				'fileId' =>"cofactor_$fileId",
				'name' => $testFileName,
				'fileExtension' => $fileExtension,
				'nameWithExtension' => $nameWithExtension,
				'fileIdWithExtension' => "cofactor_$fileId.$fileExtension",
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
	
		$cofactor_dir = "../session-files/$session_id/COFACTOR";
		if(!file_exists($cofactor_dir) || !is_dir($cofactor_dir)){
			mkdir($cofactor_dir);
		}
		
		if(!is_dir("../session-files/$session_id/COFACTOR/INPUT")){
			mkdir("../session-files/$session_id/COFACTOR/INPUT");
		}
	
		$saveCmd = "cp ../test-files/$originalName ../session-files/$session_id/COFACTOR/INPUT/$nameWithExtension";
		shell_exec($saveCmd);
	
		$response['operationStatus'] = 'SUCCESS';
	
	
	} else if ($action == 'DOWNLOAD-FILE'){
		$file_type = $_GET['type'];
		$dir = "../session-files/$session_id/COFACTOR";
	
		switch ($file_type) {
			case 'zip' :
				shell_exec("cd $dir; zip -r COFACTOR.zip *;");
				header ( "Location: $dir/COFACTOR.zip" );
				break;
			case 'topZip' :
				if(!file_exists("$dir/cofactor_top_files.zip")){
					mkdir("$dir/cofactor_top_files");
					foreach ( glob ( "$dir/OUTPUT/*" ) as $dir_id ) {
						foreach ( glob ( "$dir_id/*.top" ) as $top_file ) {
							copy($top_file, "$dir/cofactor_top_files/" . basename($top_file));
						}
					}
	
					shell_exec("cd $dir; zip -r cofactor_top_files.zip cofactor_top_files; rm -rf cofactor_top_files");
				}
					
				header ( "Location: $dir/cofactor_top_files.zip" );
				break;
			case 'map' :
				header ( "Location: $dir/cofactor_mapfile.csv" );
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
		$response['status'] = 'FALSE';
	
		$file = $request->params->file;
		$fileId = $request->params->file->fileId;
		$fileIdUnderline = $fileId."_1";
	
		$checkTopHasSelectedTorsionCommand = "cd ../session-files/$session_id/COFACTOR/OUTPUT/$fileId; cat $fileIdUnderline.top;";
		$resultTopContent = shell_exec($checkTopHasSelectedTorsionCommand);
	
		$arr = explode("\n", $resultTopContent);
		for ($i = 0; $i < count($arr); $i++) {
	
			if(substr( $arr[$i], 0, 9 ) === '$SELECTED'){
				$lineArray = explode("   ", $arr[$i]);
				if($lineArray[1] != '0'){
					$response['status'] = 'SUCCESS';
				}else{
					$response['status'] = 'FAILED';
				}
				break;
			}
				
		}
	
	} else if($action=='removeFile'){
	
		$response['removeFileResponse'] = '';
	
		try {
			$fileIdWithExtension = $request->params->fileIdWithExtension;
			$fileId = preg_replace ( '/\\.[^.\\s]{2,4}$/', '', $fileIdWithExtension ); // retirando extensão
				
			shell_exec("rm     ../session-files/$session_id/COFACTOR/INPUT/$fileIdWithExtension");
			shell_exec("rm -rf ../session-files/$session_id/COFACTOR/OUTPUT/$fileId/");
				
			//Rebuild/delete cofactor_mapfile
			$sessionFileRow = exec("find ../session-files/$session_id/COFACTOR/INPUT/ -maxdepth 1 -type f",$fileOutput,$sessionFileError);
			if($sessionFileRow == ""){//remove file
				shell_exec("rm ../session-files/$session_id/COFACTOR/cofactor_mapfile.csv");
			}else{//update file
				$updateCommand = "cd ../session-files/$session_id/COFACTOR/; sed '/$fileId/d' cofactor_mapfile.csv | tee cofactor_mapfile.csv";
				shell_exec($updateCommand);
			}
				
			$response['removeFileResponse'] = 'SUCCESS';
		} catch (Exception $e) {
			$response['removeFileResponse'] = 'FAIL';
		}
	} else if($action == 'clearCofactorSession'){
	
		$util = new Utils();
		$session_dir = "../session-files/$session_id";
		if (! file_exists ( $session_dir ) || ! is_dir ( $session_dir )) {
			mkdir ( $session_dir );
		}
	
		$util->resetCofactorStructureDir($session_id);
	
		$data['status'] = 'ERR';
		$session_dir = "../session-files/$session_id/COFACTOR";
	
		if (file_exists ($session_dir) || is_dir ($session_dir) ) {
				
			$result = $util->clearDir($session_dir);
			if($result){
				$response['status'] = 'OK';
			}
	
		}
	
	} else if($action == 'SAVE-FILES'){
	
		$util = new Utils();
		if (! file_exists ( $session_dir ) || ! is_dir ( $session_dir )) {
			mkdir ( $session_dir );
		}
	
		$util->resetCofactorStructureDir($session_id);
	
		// 		$ligandDir = "../session-files/$session_id/LIGAND";
		// 		if (!file_exists ( $ligandDir)) {
		// 			mkdir ( $ligandDir );
		// 		}
	
		// 		$inputDir = "../session-files/$session_id/LIGAND/INPUT/";
		// 		if (!file_exists ( $inputDir)) {
		// 			mkdir ( $inputDir );
		// 		}
	
		$response = array ();
		$response ["files"] = array ();
		//$response['operationStatus'] = 'STARTING';
		$response['operationStatus'] = 'SUCCESS';
	
		//$aa = ini_get('post_max_size');
	
		$tam = count($_FILES ['files']['name']);
	
		$files = array ();
	
		for ($i = 0; $i < $tam; $i++) {
	
			$file = array ();
			//$fileId = substr ( md5 ( microtime () ), rand ( 0, 26 ), 10 );
			$fileId = $util->generateFileId();
			$fileExtension = array_pop ( explode ( '.', $_FILES ['files'] ['name'][$i] ) );
			$fileDirResponse = "";
	
			$file ["size"] = $_FILES ['files'] ['size'][$i];
	
			if(FileValidator::isEmpty ( $_FILES ['files']['tmp_name'][$i] )){
				$response['operationStatus'] = 'ERROR';
				$response['errorMessage'][$i] = 'Empty file';
			}else{
					
				$tmpFilePath = $_FILES ['files']['tmp_name'][$i];
				$cofactorFileNameId = "cofactor_$fileId";
					
				$dirToSave = "$session_dir/COFACTOR/INPUT";
				$fileNameToSave = "$cofactorFileNameId.$fileExtension";
				$fileDirResponse = "apps/docking/session-files/$session_id/COFACTOR/INPUT/";
					
				if(!($fileExtension == "pdb" || $fileExtension == "mol2" || $fileExtension == "sdf" || $fileExtension == "top")){
					$response['operationStatus'] = 'ERROR';
					$response['errorMessage'][$i] = 'File type not allowed';
				}else {
					//saveFile ( $_FILES ['files'], $dirToSave, $fileNameToSave,$tmpFilePath );
					//saveFile ($dirToSave, $fileNameToSave,$tmpFilePath );
					$util->saveFile ($dirToSave, $fileNameToSave,$tmpFilePath );
				}
					
				if (FileValidator::isEmpty($dirToSave."/".$fileNameToSave)){
					$response['operationStatus'] = 'ERROR';
					$response['errorMessage'][$i] = 'Empty file';
				}
					
				$file ["name"] = $cofactorFileNameId;
				$file ['nameWithExtension'] = "$cofactorFileNameId.$fileExtension";
				$file ['hidrogen'] = "";
					
				if(isset($fileDirResponse)){
					$file ["thumbnailUrl"] = $fileDirResponse . $_FILES ['files'] ['name'][$i];
					$file ["deleteUrl"] = $fileDirResponse . $_FILES ['files'] ['name'][$i];
					$file ["deleteType"] = "DELETE";
					//$file ["state"] = "saved";
					$file ['originalName'] = $_FILES ['files'] ['name'][$i];
	
				}else{
					$response['operationStatus'] = 'ERROR';
					$response['errorMessage'][$i] = 'Internal error';
				}
					
			}
			array_push ( $response ["files"], $file );
		}
	} else if($action == 'SAVE-FILE'){
	
		//sleep(10);
	
		$fileRequest = $_FILES ['file']; // ONE FILE!
		$fileIndex = $_POST ['fileIndex'];
	
		$response = array ();
		$response ["file"] = array ();
		$response['operationStatus'] = 'SUCCESS';
	
		$file = array ();
		$fileId = $util->generateFileId();
		$fileExtension = array_pop ( explode ( '.', $fileRequest['name']) );
		$fileDirResponse = "";
	
		$file ["size"] = $fileRequest['size'];
	
		if(FileValidator::isEmpty ( $fileRequest['tmp_name'])){
			$response['operationStatus'] = 'ERROR';
			$response['errorMessage'][$i] = 'Empty file';
		}else{
			$tmpFilePath = $fileRequest['tmp_name'];
			$cofactorFileNameId = "cofactor_$fileId";
				
			$dirToSave = "$session_dir/COFACTOR/INPUT";
			$fileNameToSave = "$cofactorFileNameId.$fileExtension";
			$fileDirResponse = "apps/docking/session-files/$session_id/COFACTOR/INPUT/";
				
			if(!($fileExtension == "pdb" || $fileExtension == "mol2" || $fileExtension == "sdf" || $fileExtension == "top")){
				$response['operationStatus'] = 'ERROR';
				$response['errorMessage'] = 'File type not allowed';
			}else {
				$util->saveFile ($dirToSave, $fileNameToSave,$tmpFilePath );
			}
				
			if (FileValidator::isEmpty($dirToSave."/".$fileNameToSave)){
				$response['operationStatus'] = 'ERROR';
				$response['errorMessage'] = 'Empty file';
			}
				
			//$file ["name"] = $cofactorFileNameId;
			$file ["originalName"] = $fileRequest['name'];
			$file ["fileId"] = $cofactorFileNameId;
			$file ['fileIdWithExtension'] = "$cofactorFileNameId.$fileExtension";
			$file ['fileExtension'] = $fileExtension;
				
			if(isset($fileDirResponse)){
				$file ["thumbnailUrl"] = $fileDirResponse . $fileRequest['name'];
				$file ["deleteUrl"] = $fileDirResponse . $fileRequest['name'];
				$file ["deleteType"] = "DELETE";
				//$file ["state"] = "pending";
				//$file ['originalName'] = $fileRequest['name'];
				$file ['index'] = $fileIndex;
				$file ['saveResult'] = 'success';
	
			}else{
				$file ['saveResult'] = 'fail';
				$response['operationStatus'] = 'ERROR';
				$response['errorMessage'] = 'Internal error';
			}
				
		}
		array_push ( $response ["file"], $file );
	
	} else if ($action=='SEND-TO-DOCK'){
		$docking_dir = "../session-files/$session_id/DOCKING";
		if(!file_exists($docking_dir) || !is_dir($docking_dir)){
			mkdir($docking_dir);
		}
	
		if(!file_exists("$docking_dir/COFACTOR") || !is_dir("$docking_dir/COFACTOR")){
			mkdir("$docking_dir/COFACTOR");
		} else {
			shell_exec("rm -r $docking_dir/COFACTOR/*");
		}
	
		shell_exec("cp -r ../session-files/$session_id/COFACTOR/* $docking_dir/COFACTOR/");
	
	}else if ($action=='CHECK_EQUALS'){
			
		$utils = new Utils();
		
		$files = $request->params->files;
		$response = $utils->checkEqualFiles($files,$session_dir."/COFACTOR/INPUT/");	
	
	}
}

echo json_encode ( $response );
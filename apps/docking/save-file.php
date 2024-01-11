<?php
include 'lib/utils/file-validator.php';
function saveFile($file, $path_to, $fileNameIdWithExtension) {
	if (! file_exists ( $path_to ) || ! is_dir ( $path_to )) {
		mkdir ( $path_to );
	} else {
		clearDir ( $path_to );
	}
	move_uploaded_file ( $file ['tmp_name'], "$path_to/" . $fileNameIdWithExtension );
	return $file;
}
function clearDir($dirPath) {
	if (! is_dir ( $dirPath )) {
		throw new InvalidArgumentException ( "$dirPath must be a directory" );
	}
	if (substr ( $dirPath, strlen ( $dirPath ) - 1, 1 ) != '/') {
		$dirPath .= '/';
	}
	$files = glob ( $dirPath . '*', GLOB_MARK );
	foreach ( $files as $file ) {
		if (is_dir ( $file )) {
			clearDir ( $file );
			rmdir ( $file );
		} else {
			unlink ( $file );
		}
	}
}

session_start ();
$session_id = session_id ();
if ((isset ( $_FILES ['files'] ) || isset ( $_FILES ['file'] )) && isset ( $_POST ['fileType'] ) && $session_id != null) {
	$session_dir = "session-files/$session_id";
	if (! file_exists ( $session_dir ) || ! is_dir ( $session_dir )) {
		mkdir ( $session_dir );
	}
	
	$response = array ();
	$response ["files"] = array ();
	$response['operationStatus'] = 'STARTING';
	
	$file_type = $_POST ['fileType'];
	
	$fileNameId = substr ( md5 ( microtime () ), rand ( 0, 26 ), 10 );
	
	$name = "";
	if(isset($_FILES ['files']['name'][0])){
		$name = $_FILES ['files']['name'][0];
	} else {
		$name = $_FILES ['files']['name'];
	}
	$fileExtension = array_pop ( explode ( '.', $name ) );
	
	switch ($file_type) {
		case 'PROTEIN' :
			if(!($fileExtension == "pdb" || $fileExtension == "in")){
				$response['operationStatus'] = 'ERROR';
				$response['errorMessage'] = 'File type not allowed';
			}else{
				$fileNameId = "protein_$fileNameId";
				saveFile ( $_FILES ['files'], "$session_dir/PROTEIN", "$fileNameId.$fileExtension" );
			}
			break;
// 		case 'LIGAND' :
// 			if(!($fileExtension == "pdb" || $fileExtension == "mol2" || $fileExtension == "sdf" || $fileExtension == "top")){
// 				$response['operationStatus'] = 'ERROR';
// 				$response['errorMessage'] = 'File type not allowed';
// 			}
// 			$fileNameId = "ligand_$fileNameId";
// 			saveFile ( $_FILES ['files'], "$session_dir/LIGAND", "$fileNameId.$fileExtension" );			
// 			break;
		case 'COFACTORS' :
			saveFile ( $_FILES ['files'], "$session_dir/COFACTORS", "$fileNameId.$fileExtension" );
			break;
	}
	
	if($response['operationStatus'] != 'ERROR'){
		if (FileValidator::isEmpty ( "$session_dir/$file_type/$fileNameId.$fileExtension" )) {
			$response['operationStatus'] = 'ERROR';
			$response['errorMessage'] = 'Empty file';
		} else {
			$response['operationStatus'] = 'SUCCESS';
			$file = array ();
			// $file["name"] = $_FILES ['files']['name'];
			$file ["name"] = $fileNameId;
			$file ["size"] = $_FILES ['files'] ['size'];
			$file ["thumbnailUrl"] = "apps/docking/session-files/$session_id/$file_type/" . $_FILES ['files'] ['name'];
			$file ["deleteUrl"] = "apps/docking/session-files/$session_id/$file_type/" . $_FILES ['files'] ['name'];
			$file ["deleteType"] = "DELETE";
			$file ["state"] = "pending";
			$file ['nameWithExtension'] = "$fileNameId.$fileExtension";
			array_push ( $response ["files"], $file );
		}
	}
	echo json_encode ( $response );
}
?>

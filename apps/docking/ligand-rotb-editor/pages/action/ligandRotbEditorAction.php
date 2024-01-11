<?php
include '../../conf/globals-ligand-rotb-editor.php';
include $GLOBALS['LIGAND_ROTB_EDITOR_LIB_PARSER'];

$actionType = "";
$fileNameId = "";
$edittedJson = "";

// if POST
if($_SERVER['REQUEST_METHOD'] == "POST"){
	
	$postdata = file_get_contents("php://input");	
	$request = json_decode($postdata);
	
	$edittedJson = $request->params->edittedJson;
	$actionType = $request->params->type;
	$fileNameId = $request->params->fileNameId;
	
} else if(isset($_REQUEST['type']) && !empty($_REQUEST['type'])){ // else "GET"
	$actionType = $_REQUEST['type'];
	$fileNameId = $_REQUEST['fileNameId'];		
}else{
	$data['status'] = 'ERR';
	exit();
}


// ID da sessão
session_start();
$session_id = session_id();

//Check is has the top file
$topFolder = $GLOBALS['USER_SESSION_FILES_FOLDER']."$session_id/LIGAND/OUTPUT/$fileNameId/";
$topFileNameFullPath = "";
if(is_file($topFolder.$fileNameId."_1.top")){
	$topFileNameFullPath = $topFolder.$fileNameId."_1.top";
	$fileNameId = $fileNameId."_1.top";
}else if(is_file($topFolder.$fileNameId.".top")){
	$topFileNameFullPath = $topFolder.$fileNameId.".top";
	$fileNameId = $fileNameId.".top";
}else{
	$data['status'] = 'ERR';
	echo json_encode($data);
	exit();
}

if(isset($fileNameId)){
	$ligandRotbParser = new LigandRotbParser();
}else{
	$data['status'] = 'ERR';
	echo json_encode($data);
	exit();
}


switch($actionType){
	
	case "getJsonTest":
		$data = array();

		$result = $ligandRotbParser->generateJsonFromTopFile(
				"/home/iuri/workspace/DockThor-3.0/apps/docking/ligand-rotb-editor/lib/test/".$fileNameId,
				"/home/iuri/workspace/DockThor-3.0/apps/docking/ligand-rotb-editor/lib/test/");
		if($result!=false){
			$data['json'] = $result;
			$data['status'] = 'OK';
		}else{
			$data['status'] = 'ERR';
		}
		
		echo json_encode($data);
		break;
		
	case "getJson":

		$data = array();
		$result = $ligandRotbParser->generateJsonFromTopFile($topFolder.$fileNameId,$topFolder);
		if($result!=false){
			$data['json'] = $result;
			$data['status'] = 'OK';
		}else{
			$data['status'] = 'ERR';
		}

		echo json_encode($data);
		break;
		
	case "updateTop":
		$result = $ligandRotbParser->updateTopFile(
				$edittedJson,
				$topFileNameFullPath,
				$topFileNameFullPath);
		
		if($result){
			$data['status'] = 'OK';
		}else{
			$data['status'] = 'ERR';
		}
		
		echo json_encode($data);
		break;
		
	default:
		echo '{"status":"INVALID"}';
}


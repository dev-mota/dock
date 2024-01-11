<?php
include '../../conf/globals-protein-editor.php';
include $GLOBALS['PROTEIN_EDITOR_LIB_PARSER'];

$actionType = "";
$edittedJson = "";
$pdbFileRandomId = ""; 
$chainFileNamesArray = array();

// if POST
if($_SERVER['REQUEST_METHOD'] == "POST"){ 
	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata);
	$actionType = $request->params->type;
	$edittedJson = $request->params->edittedJson;
	$pdbFileRandomId = $request->params->pdbFileRandomId;
	$chainFileNamesArray = $request->params->chainFileNamesArray; 
}else{ // if GET
	if(isset($_REQUEST['type']) && !empty($_REQUEST['type'])){	
		$actionType = $_REQUEST['type'];	
		$pdbFileRandomId = $_REQUEST['pdbFileRandomId'];
		$chainFileNamesArray = $_REQUEST['chainFileNamesArray'];
	}
}

// ID da sessão
session_start();
$session_id = session_id();

$sessionUserFolder = $GLOBALS['USER_SESSION_FILES_FOLDER']."$session_id/PROTEIN/";

if(isset($pdbFileRandomId)){
	$proteinParser = new ProteinParser($sessionUserFolder,$pdbFileRandomId,$chainFileNamesArray);
}else{
	$data['status'] = 'ERR';
	echo json_encode($data);
	exit();
}


switch($actionType){
	case "getJson":
		
		$data = array();		
		$result = $proteinParser->generateJsonFromChainFilesAndPdbFile();
		if($result!=null){			
			$data['json'] = $result;
			$data['status'] = 'OK';					
		}else{
			$data['status'] = 'ERR';
		}			
		
		echo json_encode($data);
		break;
		
	case "sendJson":
		
		if($edittedJson!=null || $edittedJson!=""){			
// 			$edittedJsonAsString = json_encode($edittedJson,JSON_PRETTY_PRINT);
			
// 			openlog('dockingapp', LOG_NDELAY, LOG_USER);
// 			syslog(LOG_DEBUG, $edittedJsonAsString);
			
			$result = $proteinParser->uploadEdittedJson($edittedJson);
			
			if($result){
				$result = $proteinParser->updateChainFiles();
				if($result){
					$data['status'] = 'OK';
				}else{
					$data['status'] = 'ERR';
				}
			}else{
				$data['status'] = 'ERR';
			}

		}else{
			$data['status'] = 'ERR';
		}	
		
		echo json_encode($data);
		break;
	default:
		echo '{"status":"INVALID"}';
}


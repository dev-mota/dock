<?php
include_once '../lib/preparedResourcesLib.php'; 

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

$baseFolder = [
	"ligand"=>"compounds",
	"protein"=>"targets/covid-19"
];

if ( isset($action) && ($session_id!=null) ) {

	if ($action == "LOAD-TARGETS"){
		
        $type = $request->params->type; // test or target
		$folder = $baseFolder[$type]; 
		
		syslog(LOG_INFO|LOG_LOCAL0, "Action: $action, type: $type folder:$folder");
		
		$response["data"] = array ();
		
		try{
			
			$preparedResourcesLib = new PreparedResourcesLib();
			$data = $preparedResourcesLib->loadStructureDynamic($folder);
			
			// syslog(LOG_INFO|LOG_LOCAL0, "SUCCESS");
			$response ["data"] = $data;	
		}catch(Exception $e){
			syslog(LOG_INFO|LOG_LOCAL0, $e->getMessage());
			http_response_code(500);
		}
		
	} else if ($action == "GET-INFO"){
	
		$type = $request->params->type;
		$selectedPath = $request->params->selectedPath;
		$path = $baseFolder[$type]."/".$selectedPath;
		
		syslog(LOG_INFO|LOG_LOCAL0, "action:$action type:$type path:$path");
		
		$preparedResourcesLib = new PreparedResourcesLib();		
		$info = $preparedResourcesLib->getLeafInfo($path);
		
		syslog(LOG_INFO|LOG_LOCAL0, "action:$action info:$info");
		
		if($info!=null && is_string($info)){
			$response ["info"] = $info;	
			http_response_code(200);
		}else {
			http_response_code(500);
		}
		
	} else if ($action == "GET-PROTEIN-FILE-PATH"){
		$type = $request->params->type;
		$selectedPath = $request->params->selectedPath;
		$path = $baseFolder[$type]."/".$selectedPath;
		
		//syslog(LOG_INFO|LOG_LOCAL0, "action:$action type:$type path:$path");
		
		$preparedResourcesLib = new PreparedResourcesLib();
		$resultPath = $preparedResourcesLib->getProteinPath($path);
		
		//syslog(LOG_INFO|LOG_LOCAL0, "action:$action result:$resultPath");
		
		if($resultPath!=null && is_string($resultPath)){
			$response ["path"] = $resultPath;	
			http_response_code(200);
		}else {
			http_response_code(500);
		}
		
	} else if($action == "GET-PROTEIN-FILE-PATH"){
		
	}
}

echo json_encode ( $response );

?>
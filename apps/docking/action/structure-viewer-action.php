<?php

include_once "../lib/utils/StructureViewerConfigLib.php";

$response = array();
$response ['status'] = 'ERR';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $action = $request->action;
} else{
    exit();
}

switch ($action) {
    
    case "getStructureViewerType" :
        $configLib = new StructureViewerConfigLib();
        $viewerType = $configLib->getStructureViewerType();
        
        $response['status'] = 'OK';
        $response['data'] = $viewerType;
        
        break;        
    default :
        $response['status'] = 'ERR';
}

echo json_encode ( $response );
?>
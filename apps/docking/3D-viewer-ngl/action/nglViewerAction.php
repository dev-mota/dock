<?php

include_once "../lib/DockingStructureHelper.php";
include_once "../lib/BlindDockingLib.php";

session_start();
$session_id = session_id();

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
    
    case "getFilePaths" :      
        
        if( ($request->structureType != null) && ($request->step != null) ){
            
            $structureType = $request->structureType;
            
            $step = $request->step;
            if( ($step == 'upload') || ($step=='docking') ){
                $basePath = "../../session-files/$session_id";
            } else if ($step == 'results'){
                $jobId = $request->jobId;
                $basePath = "../../daemon/jobs/$jobId";
            }     
            
            $structureHelper = new DockingStructureHelper();
            $response = $structureHelper->getFilePaths($basePath, $structureType, $step);
        }else{
            $response['status'] = 'ERR';
        }
        
        
        break;
    case "calcBlindDocking" :
        
        $protein_file_name = $request->prepName;
        
        $blindDockingLib = new BlindDockingLib();
        $blindDockingGrid = $blindDockingLib->calcBlindDocking($protein_file_name, $session_id);
        
        if($blindDockingLib == null){
            $response['status'] = 'ERR';
            $response['data'] = null;
        }else{
            $response['status'] = 'OK';
            $response['data'] = [];
            $response['data']['grid'] = $blindDockingGrid;
        }
        
        break;
    case "getSessionId" :    

        $response['status'] = 'OK';
        $response['data'] = $session_id;
        
        break;
    default :
        $response['status'] = 'ERR';
}

echo json_encode ( $response );
?>
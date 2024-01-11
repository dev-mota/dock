<?php
$response = array();
$response ['status'] = 'ERR';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    // Case POST
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $action = $request->action;
} else if(isset($_REQUEST['action']) && !empty($_REQUEST['action'])){
    // Case GET
    $action = $_REQUEST['action'];
}
else{
    // If not POST, GET or no 'action'
    exit();
}

switch ($action) {
    
    case "getLigandOutputPaths" :

        $resultPath = array();
        $outPutFolders = glob("../testFiles/LIGAND/OUTPUT/*", GLOB_ONLYDIR);
        
        foreach ($outPutFolders as $outPutFolder) {
            
            $pathArray = explode('/', $outPutFolder);
            $folderName = end($pathArray);
            
            $filePaths = glob("$outPutFolder/new_*");            
            if(count($filePaths)==0){
                $filePaths = glob("$outPutFolder/*.sdf");
            }
            
            $paths = array();
            foreach ($filePaths as $filePath) {
                // Name
                $filePathArray = explode('/', $filePath);
                $fileName = end($filePathArray);
                
                // Index
                $pathArray = explode('/', $filePath);
                $nameAndExtArray = explode('.', end($pathArray));
                $nameArray = explode('_', $nameAndExtArray[0]);
                $index = end($nameArray);
                
                // Paths
                $paths[] = ["index"=>$index,"name"=>$fileName,"path"=>$filePath];
            }
            
            $resultPath[] = ["name"=>$folderName, "paths"=>$paths];
        }
        $response['status'] = 'OK';
        $response['data'] = $resultPath;
        
        break;
    default :
        $response['status'] = 'ERR';
}

echo json_encode ( $response );
?>
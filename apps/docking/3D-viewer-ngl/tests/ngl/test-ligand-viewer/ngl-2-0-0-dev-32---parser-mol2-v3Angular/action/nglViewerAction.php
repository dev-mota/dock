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

function sortByStructureIndex($a, $b)
{
    $a = $a['structureIndex'];
    $b = $b['structureIndex'];
    
    if ($a == $b) return 0;
    return ($a < $b) ? -1 : 1;
}

switch ($action) {
    
    case "getLigandOutputPaths" :

        $resultPath = array();
        $ligandPath = "../testFiles/LIGAND";
        $outPutFolders = glob("$ligandPath/OUTPUT/*", GLOB_ONLYDIR);
        
        foreach ($outPutFolders as $outPutFolder) {
            
            $pathArray = explode('/', $outPutFolder);
            $folderName = end($pathArray);
            
            // Real input file name
            $inputFileName = "";
            $file = fopen("$ligandPath/ligand_mapfile.csv", 'r');
            while (($line = fgetcsv($file)) !== FALSE) {
                //$line is an array of the csv elements
                if($line[1]==$folderName){
                    $inputFileName = $line[0];
                }                
            }
            $inputFileNameArray = explode('.', $inputFileName);
            $inputFileNameNoExt = $inputFileNameArray[0];            
            
            $filePaths = glob("$outPutFolder/new_*");            
            if(count($filePaths)==0){
                $filePaths = glob("$outPutFolder/*.sdf");
            }
            
            $paths = array();
            foreach ($filePaths as $filePath) {
                // Name
                $filePathArray = explode('/', $filePath);
                $fileName = end($filePathArray);
                $fileNameArray = explode(".", $fileName);
                $fileNameNoExt = $fileNameArray[0];
                
                // Index
                $pathArray = explode('/', $filePath);
                $nameAndExtArray = explode('.', end($pathArray));
                $nameArray = explode('_', $nameAndExtArray[0]);
                $index = (int)end($nameArray);
                
                // Paths
                $paths[] = ["structureIndex"=>$index,"name"=>$fileName, "nameNoExt"=>$fileNameNoExt, "path"=>$filePath];                
            }
            
            usort($paths, 'sortByStructureIndex');
            $resultPath[] = ["name"=>$folderName, "input"=>$inputFileName, "inputNoExt"=>$inputFileNameNoExt, "paths"=>$paths];
        }
        $response['status'] = 'OK';
        $response['data'] = $resultPath;
        
        break;
    default :
        $response['status'] = 'ERR';
}

echo json_encode ( $response );
?>
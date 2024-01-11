<?php
include_once "../../../../lib/utils/utils.php";

//session_start();
//$session_id = session_id();
//$basePath = "../session-files/$session_id";
$basePath = "../testFiles/";

$utils = new Utils();

$response = array();
$response ['status'] = 'ERR';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $action = $request->action;
} else{
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
    
    case "getLigandFilePaths" :

        $resultPath = array();
        $outPutFolders = array();
        $mapFileName = "";
            
        $outPutFolders = glob("$basePath/LIGAND/OUTPUT/*", GLOB_ONLYDIR);
        $mapFileName = "$basePath/LIGAND/ligand_mapfile.csv";
        
        foreach ($outPutFolders as $outPutFolder) {
            
            $pathArray = explode('/', $outPutFolder);
            $folderName = end($pathArray);
            
            // Real input file name
            $inputFileName = "";
            $file = fopen("$mapFileName", 'r');
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
    case "getProteinFilePaths" :
        
        $proteinPath = "$basePath/PROTEIN";
        $responseFilePath = "";
        
        // Get protein file path (only one, with in or pdb extension)
        $filePaths = glob("$proteinPath/*.in");
        if(count($filePaths)==0){ // if do not has '.in', then, get the '.pdb' one
            $filePaths = glob("$proteinPath/*.sdf");
        }
        if(count($filePaths)==1){ // only one file must exist
            
            $pathParts = pathinfo( $filePaths[0] );
            if ($pathParts['extension'] == "in") {
                $newFile = $utils->convertInToPDB("$proteinPath", $pathParts['basename']);
                $responseFilePath = "$proteinPath/$newFile";
            }else{
                $responseFilePath = $filePaths[0];
            }
            
            $response['status'] = 'OK';
            $response['data'] = $responseFilePath;
            
        }else{
            $response['status'] = 'ERR';
        }
        
        break;
    default :
        $response['status'] = 'ERR';
}

echo json_encode ( $response );
?>
<?php
include_once "../lib/utils/utils.php";

/**
 * Código desenvolvido por Eduardo Krempser, e adaptado por Iuri Malinoski para uso neste portal
 */

session_start();
$session_id = session_id();
$utils = new Utils();

$type = $_GET ["type"];

$filePath = "";

function defineFilePathForProtein($session_id, $type, $arquivo, $utils){
    
    $path_parts = pathinfo ( "../session-files/$session_id/$type/$arquivo" );
    
    if ($path_parts ['extension'] == "in") {
        $arquivo = $utils->convertInToPDB("../session-files/$session_id/$type/", $arquivo);
    }
    $filePath = "../session-files/$session_id/PROTEIN/$arquivo";
    return $filePath;
}

function getFilePathsForLigands($session_id, $type){
    
    $resultPath = array();
    $outPutFolders = glob("../session-files/$session_id/$type/OUTPUT/*");
    foreach ($outPutFolders as $outPutFolder) {
        
        $pathArray = explode('/', $outPutFolder);
        $folderName = end($pathArray);
        
        // Try to get _new files, otherwise, get to get sdf ones 
        $resultPath[$folderName] = glob("$outPutFolder/new_*");
        if(count($resultPath[$folderName])==0){
            $resultPath[$folderName] = glob("$outPutFolder/*.sdf");
        }
    }
    
    return $resultPath;
    
}
    
if($type == "PROTEIN"){
    
    $arquivo = $_GET ["file"];
    $filePath = defineFilePathForProtein($session_id, $type, $arquivo, $utils);
    include 'view/protein-viewer.php';
    
} else if($type == "LIGAND" || $type == "COFACTOR"){
    
    $arrayPathFiles = getFilePathsForLigands($session_id, $type);
    if(count($arrayPathFiles)>0){
        if($type == "LIGAND"){
            include 'view/ligand-viewer.php';
        } else{
            include 'view/error.html';
        }
    } else{
        include 'view/error.html';
    }
    
} else if ($type == "DOCKING") {
        
//         $path_parts = pathinfo ( "../session-files/$session_id/$type/PROTEIN/$arquivo" );
//         if ($path_parts ['extension'] == "in") {
//             $arquivo = $utils->convertInToPDB("../session-files/$session_id/$type/PROTEIN", $arquivo);
//         }
//         $filePath = "../session-files/$session_id/DOCKING/PROTEIN/$arquivo";
        
//         if(isset($_GET ["cofactors"])){
//             $cofactorPaths = json_decode($_GET ["cofactors"], true);
//             foreach ($cofactorPaths as $cofactor){
//                 $cofactorFileName = $cofactor['fileIdWithExtension'];
//                 $path_parts = pathinfo ("../session-files/$session_id/DOCKING/COFACTOR/OUTPUT/" . $cofactor['fileId']. "/" . $cofactor['fileIdWithExtension'] );
//                 if ($path_parts ['extension'] == "top") {
//                     $cofactorFileName = $utils->convertTopToPDB($_SERVER['DOCUMENT_ROOT']."/".(explode('/', $_SERVER['REQUEST_URI'])[1])."/apps/docking/session-files/$session_id/DOCKING/COFACTOR/OUTPUT/" . $cofactor['fileId'], $cofactor['fileIdWithExtension']);
//                 }
                
//                 $loadAppend = "$loadAppend load APPEND ../session-files/$session_id/DOCKING/COFACTOR/OUTPUT/" . $cofactor['fileId'] . "/$cofactorFileName" . ";";
//                 // 				$filePath ="../session-files/$session_id/DOCKING/LIGAND/INPUT/" .$cofactor['fileIdWithExtension'];
//             }
            
//             if($loadAppend != ""){
//                 // 				$loadAppend = "$loadAppend frame *;display 1.1, 2.1";
//                 $loadAppend = "$loadAppend frame *;display *";
//             }
//         }
        
//         $xGridCenter = isset($_GET ["xGridCenter"]) ? $_GET ["xGridCenter"] : 0;
//         $yGridCenter = isset($_GET ["yGridCenter"]) ? $_GET ["yGridCenter"] : 0;
//         $zGridCenter = isset($_GET ["zGridCenter"]) ? $_GET ["zGridCenter"] : 0;
        
//         $xGridSize = isset($_GET ["xGridSize"]) ? $_GET ["xGridSize"] : 0;
//         $yGridSize = isset($_GET ["yGridSize"]) ? $_GET ["yGridSize"] : 0;
//         $zGridSize = isset($_GET ["zGridSize"]) ? $_GET ["zGridSize"] : 0;
        
//         $width = "350";
//         $heigth = "350";
        
} else if ($type == "RESULTS") {
        
//         $width = "350";
//         $heigth = "350";
        
//         $jobID = $_GET ["jobID"];
        
//         $extension = pathinfo ( "../session-files/$session_id/RESULTS/$jobID/RESULT/$arquivo", PATHINFO_EXTENSION );
//         if ($extension == "in") {
//             $arquivo = $utils->convertInToPDB("../daemon/jobs/$jobID/PROTEIN/", $arquivo);
//         }
//         $filePath = "../daemon/jobs/$jobID/PROTEIN/$arquivo";
        
//         $ligandsPaths = array();
        
//         if(isset($_GET ["ligands"])){
            
//             $snapCSVFilePath = "../session-files/$session_id/RESULTS/$jobID/RESULT/snap.txt";
//             $snapCSVFile = fopen($snapCSVFilePath, "r");
            
            
            
//             if($snapCSVFile != false) {
                
//                 while (!feof($snapCSVFile)) {
                    
                    
//                     $line = fgets($snapCSVFile); // (do not use "$file" more than once!)
//                     $line = substr($line, 0, strlen($line) - 1);
                    
//                     if(strlen($line) > 4) {
//                         array_push($ligandsPaths, $line);
//                     }
                    
//                 }
//             }
            
//             fclose($snapCSVFile);
            
            
//             foreach ($ligandsPaths as $ligand){
//                 $loadAppend = "$loadAppend load APPEND ../session-files/$session_id/RESULTS/$jobID/RESULT/result-$ligand.mol2;";
//             }
//         }
        
//         $cofactorPaths = array();
//         if(isset($_GET ["cofactors"])){
//             $cofactorPaths = json_decode($_GET ["cofactors"], true);
//             foreach ($cofactorPaths as $cofactor){
//                 $cofactorFileName = $cofactor;
//                 $path_parts = pathinfo ("../daemon/jobs/$jobID/COFACTOR/$cofactor");
//                 if ($path_parts ['extension'] == "top") {
//                     $cofactorFileName = $utils->convertTopToPDB($_SERVER['DOCUMENT_ROOT']."/".(explode('/', $_SERVER['REQUEST_URI'])[1])."/apps/docking/daemon/jobs/$jobID/COFACTOR/", $cofactor);
//                 }
                
//                 $loadAppend = "$loadAppend load APPEND ../daemon/jobs/$jobID/COFACTOR/$cofactorFileName;";
//             }
            
//             $cofactorIndex = (count($ligandsPaths) + 2);
//             $referenceIndex = $cofactorIndex + (count($cofactorPaths));
            
//             if(count($cofactorPaths) == 1){
//                 $cofactorIndex = "$cofactorIndex.1";
//             }
//         } else {
//             $referenceIndex = (count($ligandsPaths) + 2);
//         }
        
//         if(isset($_GET ["reference"])){
//             $reference = $_GET ["reference"];
//             // $loadAppend = "$loadAppend load APPEND ../daemon/jobs/$jobID/result/$reference;";
//             $loadAppend = "$loadAppend load APPEND ../session-files/$session_id/RESULTS/$jobID/RESULT/$reference;";
            
//         }
        
//         if($loadAppend != ""){
//             $loadAppend = "$loadAppend frame *;display 1.1, 2.1;";
            
//             if(isset($_GET ["cofactors"]) ){
//                 $loadAppend .= " display ADD $cofactorIndex;";
//             }
            
//             if(isset($_GET ["reference"]) ){
//                 $referenceIndex = "$referenceIndex.1";
//                 $loadAppend .= " display ADD $referenceIndex;";
//             }
//         }

} else{
    include 'view/error.html';
}   
?>



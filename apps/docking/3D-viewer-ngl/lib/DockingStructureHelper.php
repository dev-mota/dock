<?php
include_once "../../lib/utils/utils.php";

class DockingStructureHelper
{
    
    private $utils;
    private $debug;
    
    public function __construct(){
        $this->utils = new Utils();
        $this->debug = false;
    }
    
    private function sortByStructureIndex($a, $b)
    {
        $a = $a['structureIndex'];
        $b = $b['structureIndex'];
        
        if ($a == $b) return 0;
        return ($a < $b) ? -1 : 1;
    }
    
    /** Check if has any file inside. It is enouth to say thats prepared =) */
    private function checkStructureFolderIsPrepared($folder){
        $result = glob($folder."/*");
        if(count($result)!=0){
            return true;
        }else{
            return false;
        }
    }
    
    public function getFilePaths($basePath, $structureType, $step){
        
        $structureTypeUppercase = strtoupper($structureType);
        $basePathStructure = "$basePath/$structureTypeUppercase";
        
        if($this->debug){
            syslog(LOG_DEBUG|LOG_LOCAL0, "start getFilePaths($basePath, $structureType, $step) - basePathStructure=$basePathStructure");
        }
        
        if( ($structureType=='ligand' || $structureType=='cofactor') && ($step=='upload' || $step=='docking') ){
            
            /**
             * Preparando o diretório caso 'upload', 'docking' ou 'results'
             */
            
            $basePathStructure = "";
            
            if($step=='upload') {
                $basePathStructure = "$basePath/$structureTypeUppercase/OUTPUT/";
            }else if ($step=='results'){
                $basePathStructure = "$basePath/$structureTypeUppercase/";
            }else if($step=='docking'){
                $basePathStructure = "$basePath/DOCKING/$structureTypeUppercase/OUTPUT";
            }else {
                $response['status'] = 'ERR';
                return $response;
            }
            
            if(!$this->checkStructureFolderIsPrepared($basePathStructure)){
                $response['status'] = 'NOT_PREPARED_YET';
                return $response;
            }
            
            $resultPath = array();
            $outPutFolders = array();
            $mapFileName = "";
            $mapFile = $structureType."_mapfile.csv"; // Nota: mapfile só tem para lignad e cofactors
            
            $outPutFolders = glob("$basePathStructure/*", GLOB_ONLYDIR);
            $mapFileName = "$basePathStructure/../$mapFile";
            
            if(count($outPutFolders)==0){
                $response['status'] = 'NOT_PREPARED_YET';
                return $response;
            }
            
            // Percorrendo o diretorio OUTPUT:
            foreach ($outPutFolders as $outPutFolder) {
                
                $pathArray = explode('/', $outPutFolder);
                $folderName = end($pathArray);
                
                // Real input file name
                $inputFileName = "";
                $inputRandomFileName = "";
                $file = fopen("$mapFileName", 'r');
                while (($line = fgetcsv($file)) !== FALSE) {
                    //$line is an array of the csv elements
                    if($line[1]==$folderName){
                        $inputFileName = $line[0];
                        $inputRandomFileName = $line[1];
                    }
                }
                $inputFileNameArray = explode('.', $inputFileName);
                $inputFileNameNoExt = $inputFileNameArray[0];
                $inputExt = $inputFileNameArray[1];
                
                /** Hydrogens */
                $filePaths = glob("$outPutFolder/new_*");
                
                /** If has no hydrogens files, try get another files */
                if(count($filePaths)==0){
                    
                    $filePaths = glob("$outPutFolder/*.sdf");
                    
                    if(count($filePaths)==0){
                        
                        $filePaths = glob("$outPutFolder/*.top");
                        
                        if(count($filePaths)==0){
                            $response['status'] = 'ERR';
                            return $response;    
                        }
                        
                    }
                    
                }
                
                $paths = array();
                foreach ($filePaths as $filePath) {
                    
                    $filePathArray = explode('/', $filePath);
                    $fileName = end($filePathArray);
                    $fileNameArray = explode(".", $fileName);
                    $fileNameNoExt = $fileNameArray[0];
                    $fileExt = $fileNameArray[1];
                    
                    // If the file is top, the pdb conversion is necessary (NGL do not read .top)
                    
                    if($fileExt == 'top'){
                        
                        $pathParts = pathinfo($filePath);                        
                        $pathTopFile = $pathParts['dirname']."/".$pathParts['basename'];
                        
                        if($this->debug){
                            syslog(LOG_DEBUG|LOG_LOCAL0, "is top - filePath:$filePath; basePathStructure=$basePathStructure; pathTopFile=$pathTopFile pathParts=".json_encode($pathParts));
                        }
                        
                        if(is_file($pathTopFile)){
                            
                            if($this->debug){
                                syslog(LOG_DEBUG|LOG_LOCAL0, "top found");
                            }
                            
                            $newPdbFileName = $this->utils->convertTopToPDB("../".$pathParts['dirname'], $pathParts['basename']);
                            $pathPdbFile = $pathParts['dirname']."/".$newPdbFileName;
                            
                            if(is_file($pathPdbFile)){
                            
                                if($this->debug){
                                    syslog(LOG_DEBUG|LOG_LOCAL0, "success on top to pdb conversion!");
                                }
                                
                               $filePath = $pathPdbFile;
                            } else {
                                syslog(LOG_ERR|LOG_LOCAL0, "Could not find the new pdb file: $pathPdbFile");
                            }
                            
                        } else {
                            syslog(LOG_ERR|LOG_LOCAL0, "3D viewer - ligand tab - INTERNAL ERROR - top not found");
                        }
                        
                    }
                    
                    // Index
                    $pathArray = explode('/', $filePath);
                    $nameAndExtArray = explode('.', end($pathArray));
                    $nameArray = explode('_', $nameAndExtArray[0]);
                    $index = (int)end($nameArray);
                    
                    // Paths
                    $paths[] = ["structureIndex"=>$index,"name"=>$fileName, "nameNoExt"=>$fileNameNoExt, "path"=>$filePath];
                }
                
                usort($paths, array('DockingStructureHelper','sortByStructureIndex'));
                $resultPath[] = ["name"=>$folderName, "input"=>$inputFileName, "inputNoExt"=>$inputFileNameNoExt, "paths"=>$paths];
            }
            
            $response['status'] = 'OK';
            $response['data'] = $resultPath;
            
            return $response;
            
        } else if($structureType=='protein' || $step=='results'){
            
            $response = array();
                        
            if($this->checkStructureFolderIsPrepared($basePathStructure)){
                
                $responseFilePath = "";
                
                /* Try to get '_pre.pdb' or '.in' file or '.pdb' */
                $filePaths = glob("$basePathStructure/*_prep.pdb");
                if(count($filePaths)==0){
                    $filePaths = glob("$basePathStructure/*.in");
                }
                if(count($filePaths)==0){                    
                    $filePaths = glob("$basePathStructure/*.pdb");
                }
                if(count($filePaths)==0){
                    $filePaths = glob("$basePathStructure/*.top");
                }
                
                $paths = array();
                for ($i = 0; $i < count($filePaths); $i++) {
                
                    $pathParts = pathinfo( $filePaths[$i] );
                    if ($pathParts['extension'] == "in") {
                        $newFile = $this->utils->convertInToPDB("$basePathStructure", $pathParts['basename']);
                        $responseFilePath = "$basePathStructure/$newFile";
                    } else if ($pathParts['extension'] == "top") {
                        $newFile = $this->utils->convertTopToPDB("../$basePathStructure", $pathParts['basename']);
                        $responseFilePath = "$basePathStructure/$newFile";
                    } else {
                        $responseFilePath = $filePaths[$i];
                    }
                    
                    $paths[] = ["structureIndex"=>$i,"name"=>"N/A", "nameNoExt"=>"N/A", "path"=>$responseFilePath];
                }
                
                $resultPath[] = ["name"=>"N/A", "input"=>"N/A", "inputNoExt"=>"N/A", "paths"=>$paths];
                $response['data'] = $resultPath;
                $response['status'] = 'OK';
                
            } else {
                $response['status'] = 'NOT_PREPARED_YET';
            }
            
            return $response;
        } else{
            return null;
        }
        
    }
}


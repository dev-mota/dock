<?php
require_once __DIR__.'/../../environment/datasets-environment.php';
require __DIR__.'/../../../../lib/SimpleConnectDb.php';

class PreparedResourcesLib {

    private $connection = null;
    
    public function __construct(){
        $instance = SimpleConnectDb::getInstance();
        $this->connection = $instance->getConnection();        
    }

    function loadStructureDynamic($folder){
    
        $result = null;
        
        if($folder != null){
            //$baseFolder = __DIR__."/../../../../datasets/".$folder;
	    $baseFolder = $GLOBALS['DATASETS_DIR']."/".$folder;
        
            if(is_dir($baseFolder)){
                
                $result = $this->loadRecursiveDir($baseFolder);
                // syslog(LOG_INFO|LOG_LOCAL0, "result: ".json_encode($result, JSON_PRETTY_PRINT));
                
            } else {
                syslog(LOG_ERR|LOG_LOCAL0, "base dir failed: ".$baseFolder);
            }
        } else {
            syslog(LOG_ERR|LOG_LOCAL0, "type failed: ".$type);
        }
        
        return $result;
    }
    
    function loadRecursiveDirComplete($folder) {
        
        /* Sample
        "name": "Nsp12-RdRp",
        "elements": [
            {
                "name": "<Wild_type>",
                "elements": [
                    {
                        "name": "PDBcode_7bv2-noRNA",
                        "elements": {
                            "files": [
                                {
                                    "fileName": "7bv2_wildType_noRNA.in",
                                    "path": "\/targets\/covid-19\/Nsp12-RdRp\/<Wild_type>\/PDBcode_7bv2-noRNA\/7bv2_wildType_noRNA.in"
                                },
                                {
                                    "fileName": "7bv2_wildType_noRNA.in",
                                    "path": "\/targets\/covid-19\/Nsp12-RdRp\/<Wild_type>\/PDBcode_7bv2-noRNA\/7bv2_wildType_noRNA.in"
                                }
                            ],
                            "info": "..."
                        }
        */
        
        $elements = scandir($folder);
        
        // Check if is leaf
        $isLeaf = false;
        foreach($elements as $element){
            if($element == "info.txt"){
                $isLeaf = true;
                break;
            }
        }
        
        $result = array();
        foreach($elements as $element){
            
            if(substr($element, 0, 1) != "."){ // avoid '.' and '..'
                if($isLeaf){
                    if($element == "info.txt"){
                        // $result['info'] = file_get_contents($folder."/".$element); ;
                    } else {
                        
                        if(!isset($result['files'])){
                            $result['files'] = array();
                        }
                        
                        // From:    "\/media\/sf_eclipse-workspace\/DockThor-3.0\/apps\/docking\/prepared-files-app\/lib\/..\/..\/..\/..\/datasets\/compounds\/Name\/pH range\/Conformation2\/file4.in
                        // To:      "\/compounds\/Name\/pH range\/Conformation2\/file4.in
                        $fullPathArray = explode("datasets", ($folder."/".$element) );
                        $path = $fullPathArray[count($fullPathArray)-1];
                        $size = filesize($folder."/".$element);
                        
                        $result['files'][] = [
                            "fileName"=>$element,
                            "name"=>$element,
                            "path"=>$path,
                            "size"=>$size
                        ];
                        
                    }                    
                } else {
                    if(is_dir($folder."/".$element)){
                        $result[] = ["name"=>$element, "elements"=>$this->loadRecursiveDir($folder."/".$element)];
                    }    
                }    
            }
             
        }
        return $result;
    }
    
    function loadRecursiveDir($folder) {
        
        $elements = scandir($folder);
        
        // Check if is leaf
        $isLeaf = false;
        foreach($elements as $element){
            if($element == "info.txt"){
                $isLeaf = true;
                break;
            }
        }
        
        $result = array();
        foreach($elements as $element){
            
            if(substr($element, 0, 1) != "."){ // avoid '.' and '..'
                if(!$isLeaf){
                    if(is_dir($folder."/".$element)){
                        $result[] = ["name"=>$element, "elements"=>$this->loadRecursiveDir($folder."/".$element)];
                    }    
                }    
            }
             
        }
        return $result;
    }
    
    function getLeafInfo($path){
        
        // syslog(LOG_INFO|LOG_LOCAL0, "getLeafInfo: $path");
        
        #$targetFolder = __DIR__."/../../../../datasets/".$path;
	$targetFolder = $GLOBALS['DATASETS_DIR']."/".$path;

        $info = null;
        
        if(is_dir($targetFolder)){
            // syslog(LOG_INFO|LOG_LOCAL0, "isdir");            
            $info = file_get_contents($targetFolder."/info.txt");                   
        } else {
            $info = null;
        }
        
        return $info;
    }
    
    // Get the first occurrence, except info.txt
    function getProteinPath($path){
        //$targetFolder = __DIR__."/../../../../datasets/".$path;
	$targetFolder = $GLOBALS['DATASETS_DIR']."/".$path;
        $info = null;
        
        $result = null;
        if(is_dir($targetFolder)){
            
            $elements = scandir($targetFolder);
            foreach($elements as $element){
                // syslog(LOG_INFO|LOG_LOCAL0, $element);
                if($element != "info.txt" && $element!="." && $element!=".."){
                    $result = $path."/".$element;
                    break;
                }
            }
        }
        
        return $result;
    }
    
    function statisticRegisterSubmission($portalId, $fileId, $path){
        
        try{
            $sql = "INSERT INTO target_file (`portal_id`, `file_id`, `path`) VALUES (?, ?, ?);";
        
            $stmt = $this->connection->prepare($sql);
            
            if($stmt===false){
                syslog(LOG_ERR|LOG_LOCAL0, "statisticRegisterSubmission: ".$this->connection->error);
                return false;
            } else {
                $bindResult = $stmt->bind_param( "sss", $portalId, $fileId, $path);
            
                if($bindResult === false){
                    syslog(LOG_ERR|LOG_LOCAL0, "statisticRegisterSubmission: ".$stmt->error);
                } else {
                    $executeResult = $stmt->execute();
                    if($executeResult===false){
                        syslog(LOG_ERR|LOG_LOCAL0, "statisticRegisterSubmission: ".$stmt->error);
                    } else {
                        $stmt->store_result ();            
                        $stmt->close();
                        $this->connection->close();
                        return true;        
                    }                    
                }                
            }            
             
        } catch (Exception $e){            
            syslog(LOG_ERR|LOG_LOCAL0, $e->getMessage());	
            return false;      
        }        
    }

}

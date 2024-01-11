<?php
class StructureViewerConfigLib{
    
    public function getStructureViewerType(){
        $response = false;
        $ini_array = parse_ini_file(__DIR__."/../../conf/structure-viewer-config.ini");
        return $ini_array['appViewerType'];
    }
}
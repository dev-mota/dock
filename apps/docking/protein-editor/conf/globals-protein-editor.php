<?php
/*************************
Automatic static variables
*************************/

include_once $_SERVER['DOCUMENT_ROOT']."/".(explode('/', $_SERVER['REQUEST_URI'])[1])."/conf/globals-dockthor.php";
// include_once $_SERVER['DOCUMENT_ROOT']."/conf/globals-dockthor.php";

// include_once "../../../../conf/globals-dockthor.php";


$conf_array = parse_ini_file("protein-editor-config.ini");

$GLOBALS['PROTEIN_EDITOR_RELATIVE_PATH'] = $conf_array['PROTEIN_EDITOR_RELATIVE_PATH']; 
// ex.: apps/docking/protein-editor

$GLOBALS['PROTEIN_EDITOR_LIB_PARSER'] = $GLOBALS['DOCKTHOR_PATH'].$GLOBALS['PROTEIN_EDITOR_RELATIVE_PATH']."/lib/ProteinParser.php"; 
// ex.: /var/www/html/DockThor-3.0/apps/docking/protein-editor/lib/ProteinParser.php
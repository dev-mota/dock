<?php
/*************************
Automatic static variables
*************************/

include_once $_SERVER['DOCUMENT_ROOT']."/".(explode('/', $_SERVER['REQUEST_URI'])[1])."/conf/globals-dockthor.php";

$conf_array = parse_ini_file("conf.ini");

$GLOBALS['LIGAND_ROTB_EDITOR_APP_PATH'] = $conf_array['appPath']; 
// ex.: apps/docking/ligand-rotb-editor

$GLOBALS['LIGAND_ROTB_EDITOR_LIB_PARSER'] = $GLOBALS['DOCKTHOR_PATH'].$GLOBALS['LIGAND_ROTB_EDITOR_APP_PATH']."/lib/LigandRotbParser.php"; 
// ex.: /var/www/html/DockThor-3.0/apps/docking/ligand-rotb-editor/lib/LigandRotbParser.php.php
<?php
//Dockthor
$GLOBALS['DOCKTHOR_PROJECT_NAME'] = explode('/', $_SERVER['REQUEST_URI'])[1]; // ex.: DockThor-3.0 (only for apache)
$GLOBALS['DOCKTHOR_PATH'] = $_SERVER['DOCUMENT_ROOT']."/".$GLOBALS['DOCKTHOR_PROJECT_NAME']."/"; // ex.: /var/www/html/DockThor-3.0/
//$GLOBALS['DOCKTHOR_URL'] = $_SERVER['SERVER_NAME']."/".$GLOBALS['DOCKTHOR_PROJECT_NAME']."/"; // ex.: 146.134.100.66/DockThor-3.0/
$GLOBALS['DOCKTHOR_URL'] = "https://dockthor.lncc.br/v2/";
//$GLOBALS['DOCKTHOR_URL'] = "http://orion.lncc.br:1025/DockThor-3.0/";
$GLOBALS['USER_SESSION_FILES_FOLDER'] = $GLOBALS['DOCKTHOR_PATH']."apps/docking/session-files/";
// ex.: /var/www/html/DockThor-3.0/apps/docking/session-files/hfdhqtvqkvoh5sipn3i7fjcqg0/PROTEIN/
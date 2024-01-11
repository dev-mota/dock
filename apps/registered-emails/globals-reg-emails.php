<?php

include_once $_SERVER['DOCUMENT_ROOT']."/".(explode('/', $_SERVER['REQUEST_URI'])[1])."/conf/globals-dockthor.php";

// Newsfeed Classes
$GLOBALS['REGEMAILS_LIB_DATABASE'] = $GLOBALS['DOCKTHOR_PATH']."apps/registered-emails/lib/DB.php";
?>
<?php
/*************************
Automatic static variables
*************************/

include_once $_SERVER['DOCUMENT_ROOT']."/".(explode('/', $_SERVER['REQUEST_URI'])[1])."/conf/globals-dockthor.php";

$conf_array = parse_ini_file("newsfeed-config.ini");

// Newsfeed
$GLOBALS['NEWSFEED_RELATIVE_PATH'] = $conf_array['NEWSFEED_RELATIVE_PATH']; // ex.: apps/newsfeed/
$GLOBALS['NEWSFEED_URL'] = $GLOBALS['DOCKTHOR_URL'].$GLOBALS['NEWSFEED_RELATIVE_PATH']; // ex.: 146.134.100.66/DockThor-3.0/apps/newsfeed/
$GLOBALS['NEWSFEED_PATH'] = $GLOBALS['DOCKTHOR_PATH']."/".$GLOBALS['NEWSFEED_RELATIVE_PATH']."/";

// Newsfeed Images
$GLOBALS['NEWSFEED_IMAGES_PARTIAL_PATH'] = $GLOBALS['NEWSFEED_RELATIVE_PATH']."images/"; // ex.: apps/newsfeed/images/
$GLOBALS['NEWSFEED_IMAGES_FULL_PATH'] = $GLOBALS['DOCKTHOR_PATH'].$GLOBALS['NEWSFEED_IMAGES_PARTIAL_PATH']; // ex.: /var/www/html/DockThor-3.0/apps/newsfeed/images/

// Newsfeed Mail
$GLOBALS['NEWSFEED_MAIL_SENDER'] = $conf_array['NEWSFEED_MAIL_SENDER'];

// Newsfeed Classes
$GLOBALS['NEWSFEED_LIB_DATABASE'] = $GLOBALS['DOCKTHOR_PATH'].$GLOBALS['NEWSFEED_RELATIVE_PATH']."lib/DB.php";
$GLOBALS['NEWSFEED_HELPER_CLASS'] = $GLOBALS['DOCKTHOR_PATH'].$GLOBALS['NEWSFEED_RELATIVE_PATH']."lib/helper.php";
$GLOBALS['NEWSFEED_PHPMailer_PHPMailerAutoload'] = $GLOBALS['DOCKTHOR_PATH'].$GLOBALS['NEWSFEED_RELATIVE_PATH']."lib/PHPMailer-master/PHPMailerAutoload.php";
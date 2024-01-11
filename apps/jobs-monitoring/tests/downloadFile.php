<?php
// $target = __DIR__."/../../docking/daemon/jobs/".$portalId."/result/".$portalId.".zip";


//         header( "Content-Type: application/x-zip" );
//         header( "Content-Disposition: attachment; filename=$zipfilename" );
//         header('Pragma: no-cache');
//         readfile($target);

$target = __DIR__."/../../docking/daemon/jobs/gmmsb_admin_test_complete_id1259814125/result/gmmsb_admin_test_complete_id1259814125.zip";

header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=zip.zip");
header("Content-Type: application/zip");
header("Content-Transfer-Encoding: binary");
$readFileResult = readfile($target);
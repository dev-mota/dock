<?php 
require_once ("../../../conf/globals-dockthor.php");
require_once '../../docking/lib/sinapad-rest/rest-php-adapter.php';

$postdata = file_get_contents ( "php://input" );
if(!empty($postdata)){
    $request = json_decode ( $postdata );
    $jobId = $request->params->serviceJobId;

    if($jobId != null){
        $uuid = rest_login();
        $responseCode = rest_cancel($jobId, $uuid);
        rest_logout($uuid);
        if($responseCode == '200'){
            echo 'SUCCESS';
        } else {
            echo "ERROR : $responseCode";
        }
    }
}
?>
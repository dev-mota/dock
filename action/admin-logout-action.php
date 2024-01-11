<?php
$session_started = session_start();
unset ($_SESSION['dockthor_admin_login_failed']);
unset ($_SESSION['dockthor_admin_login']);

if($session_started ==true){
	$response['operationStatus'] = 'SUCCESS';
} else{
	$response['operationStatus'] = 'ERROR';
}

echo json_encode ( $response );

?>
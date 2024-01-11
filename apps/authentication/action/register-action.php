<?php
require_once ("../../../conf/globals-dockthor.php");
if (isset ( $_FILES ['files'])) {
	$user = $_POST ['name'];
	$email = $_POST ['email'];
	$passwd = $_POST ['passwd'];
} else {
	$postdata = file_get_contents ( "php://input" );
	$request = json_decode ( $postdata );
	$user = $request->params->name;
	$email = $request->params->email;
	$passwd = $request->params->passwd;
}

//TODO:put values in bd


//send email to user
$headers = "Content-type: text/html; charset=utf-8" . "\r\n";

$constants = array(
		'VAR_USER' => $user,
		'VAR_LOG_IN_LINK' => $GLOBALS['DOCKTHOR_URL'] . "index.php?tab=LOGIN&page=LOGIN",
		'VAR_EMAIL' => $email,
		'VAR_PASSWORD' => $passwd
);
$message = strtr(file_get_contents("../email-templates/cad-user.html"), $constants);
$subject= "DockThor Account";

$sentmail = true;
$sentmail = mail($email, $subject, $message, $headers, '-fdockthor@lncc.br');
if ( $sentmail == false ){
	$response['operationStatus'] = 'ERROR';
}else {
	$response['operationStatus'] = 'SUCCESS';
}

echo json_encode ( $response );
?>

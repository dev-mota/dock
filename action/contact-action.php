<?php
require_once ("../conf/globals-contact.php");
if (isset ( $_FILES ['files'])) {
	$user = $_POST ['user'];
	$email = $_POST ['email'];
	$subject = $_POST ['subject'];
	$message = $_POST ['message'];
	$recaptcha = $_POST ['recaptcha'];
} else {
	$postdata = file_get_contents ( "php://input" );
	$request = json_decode ( $postdata );
	$user = $request->params->user;
	$email = $request->params->email;
	$subject = $request->params->subject;
	$message = $request->params->message;
	$recaptcha = $request->params->recaptcha;
}

$postdata = http_build_query(
		array(
				'secret' => '6LdzTzAUAAAAAMHOPgyFR9x8DeSefxBFSBaREHZn', //secret KEy provided by google
				'response' => $recaptcha,                    // g-captcha-response string sent from client
				'remoteip' => $_SERVER['REMOTE_ADDR']
		)
		);

//Build options for the post request
$opts = array('http' =>
		array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postdata
		)
);


//Create a stream this is required to make post request with fetch_file_contents
$context  = stream_context_create($opts);

/* Send request to Googles siteVerify API */
$captcha_response=file_get_contents("https://www.google.com/recaptcha/api/siteverify",false,$context);
$captcha_response = json_decode($captcha_response, true);

if ($captcha_response["success"] === true){

		$headers = "Content-type: text/html; charset=utf-8" . "\r\n";
	
		$final_message = "from: ".$user."<br><br>Email:".$email."<br><br>".$message;
		$sentmails = true;
		
		foreach ( $GLOBALS['emails'] as $i => $value ){
			$sentmail = mail($emails[$i], $subject, $final_message, $headers);
			if ( $sentmail == false ){
				$sentmails = false;
			}
		}
}else{
	$sentmails = false;
}

if ( $sentmails == true ){
	$response['operationStatus'] = 'SUCCESS';
}else{
	$response['operationStatus'] = 'ERROR';
}

echo json_encode ( $response );
?>

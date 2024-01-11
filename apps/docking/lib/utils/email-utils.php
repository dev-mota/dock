<?php

class EmailUtils {
 	//private static $from = "dockthor@lncc.br";
	
	public static function sendEmail($toArray, $subject, $body, $isHTML) {
 		//$headers = "From: " . self::$from;
		$headers = "";
		if($isHTML){
			$headers = "Content-type: text/html; charset=utf-8" . "\r\n";
		}
		
		$to = self::convertEmailsArrayToEmailsString($toArray);


		//$from='-f'.$GLOBALS['emails']["email1"];
		$from = "-fdockthor@lncc.br";
	
		return mail ( $to, $subject, $body, $headers, $from);
	}
	
	private static function convertEmailsArrayToEmailsString($toArray){
		$to = "";
		foreach ( $toArray as $index => $email ) {
			if ($index == (count ( $toArray ) - 1)) {
				$to .= "$email";
			} else {
				$to .= "$email, ";
			}
		}
		return $to;
	}
}

?>

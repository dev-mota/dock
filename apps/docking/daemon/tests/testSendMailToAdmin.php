<?php

$GLOBALS['emailsTest'] = [
	"email1" => "malinoski.iuri@gmail.com",
	"email2" => "iuri@lncc.br",
];


		foreach ($GLOBALS['emailsTest'] as $i => $value){
			ini_set ( "SMTP", "smtp.sinapad.lncc.br" );
			ini_set ( "sendmail_from", "dockthor@lncc.br" );
			$headers = "From: DockThor <dockthor@lncc.br>\r\n";
			$headers .= "Reply-To: malinoski.iuri@gmail.com\r\n";
			$body = "JOB ID: --- ";
			$body .= "\nDOCKTHOR ID: ------";
			$body .= "\n--------index.php?tab=DOCKING&page=RESULTS&jobId=-----";
			// mail ( $emails[$i], "Docking Error - Status DONE and failed to download - ", $body, $headers );
			mail ( $value, "Docking Error - Status DONE and failed to download - ", $body, $headers );
		}

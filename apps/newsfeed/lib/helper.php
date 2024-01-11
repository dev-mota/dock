<?php

require $GLOBALS['NEWSFEED_PHPMailer_PHPMailerAutoload'];

class Helper {
	
	public function __construct(){
		//$this->sendMail();
	}

	/*
	 * Get page footer message content and replace unsubscribe url and user mail variables
	 * @param array: contants
	 * @param string: path to file
	 */
	public function getPageFooterParsed($unsubscribeUlr, $userEmail){
		$constants = array(
				'VAR_SYSTEM__UNSUBSCRIBE_URL' => $unsubscribeUlr,
				'VAR_SYSTEM__USER_MAIL' => $userEmail
		);
		$footerTemplate = file_get_contents($GLOBALS['NEWSFEED_PATH'].'message-template/messageFooter.html');
		return strtr($footerTemplate, $constants);
	}
	
	/*
	 * Get page header message content
	 * * @param string: project url (ex.: )
	 */
	public function getPageHeader(){
		$constants = array(
				'VAR_SYSTEM__DOCKTHOR_PATH' => $GLOBALS['DOCKTHOR_PATH'],				
		);
		
 		$headerTemplate = file_get_contents($GLOBALS['NEWSFEED_PATH'].'message-template/messageHeader.html');		
 		return strtr($headerTemplate, $constants);
	}
	
	public function sendMail($fromMail,$toMail,$subject,$body,$imagePath='',$unsubscribeUrl){

		$mail = new PHPMailer;
		
		$mail->setFrom($fromMail, $subject);
		$mail->addAddress($toMail, '');
		$mail->Subject = $subject;
		$mail->isHTML(true);
		
		$mail->Body = '';
		
		// header body message
		
		//$mail->AddEmbeddedImage($GLOBALS['DOCKTHOR_PATH']."images/header/logo_teste.png", "my-attach1", "header1","base64", "application/octet-stream");
		//$mail->AddEmbeddedImage($GLOBALS['DOCKTHOR_PATH']."images/header/img_right.png",  "my-attach2", "header2","base64", "application/octet-stream");
		//$mail->AddEmbeddedImage($GLOBALS['DOCKTHOR_PATH']."images/header/slogan.png",     "my-attach3", "header3","base64", "application/octet-stream");
		$mail->Body .= $this->getPageHeader();
		
		// body message (text and/or image in body)
		$mail->Body .= $body;
		if($imagePath!=''){
			$mail->AddEmbeddedImage($imagePath, "my-attach", 'image',"base64", "application/octet-stream");
			$mail->Body .= '<p align="center"><img alt="PHPMailer" src="cid:my-attach"> </p>';
		}
		
		// footer body message
		$footerMessage = $this->getPageFooterParsed($unsubscribeUrl,$toMail);
		$mail->Body .= $footerMessage;		
		
		if(!$mail->send()) {
			error_log('Newsfeed DockThor - [ERROR] Message was not sent: '.$mail->ErrorInfo);
			return false;
		} else {
			return true;
		}
	}
	
	public function sendMailTest(){
		//https://github.com/PHPMailer/PHPMailer/wiki/Tutorial
		//http://www.ustrem.org/en/articles/send-mail-using-phpmailer-en/
		//https://github.com/Synchro/PHPMailer/tree/master/examples
		$mail = new PHPMailer;
		
		$mail->setFrom('iuri@isinapad', 'Iuri');
		$mail->addAddress('malinoski.iuri@gmail.com', 'Iuri');
		$mail->Subject = 'An HTML Message 2';
		$mail->isHTML(true);
		
		$mail->AddEmbeddedImage("/home/iuri/workspace/DockThor-3.0/images/dockthor_portal_4.png", "my-attach", "/home/iuri/workspace/DockThor-3.0/images/dockthor_portal_4.png",
				"base64", "application/octet-stream");
		$mail->Body = 'Embedded Image: <img alt="PHPMailer" src="cid:my-attach"> Here is an image!';
		
		
		if(!$mail->send()) {
			error_log('Message was not sent.');
			error_log('Mailer error: ' . $mail->ErrorInfo);
		} else {
			error_log('Message has been sent.');
		}
	}
}

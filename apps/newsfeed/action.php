<?php
include 'globals-newsfeed.php';
include $GLOBALS['NEWSFEED_LIB_DATABASE'];//TODO change this name as helper class
include $GLOBALS['NEWSFEED_HELPER_CLASS'];

$db = new DB();
$helper = new Helper();

if(isset($_REQUEST['type']) && !empty($_REQUEST['type'])){

    $type = $_REQUEST['type'];
    switch($type){
		case "getNewsfeedList":
			$sql = '
			SELECT na.`date`, e.email, n.`subject`, n.body, n.image FROM newsletter_accounting na
			INNER JOIN newsletter n ON na.newsletter_id=n.id
			INNER JOIN email_subscription e ON na.email_subscription_id=e.id
			ORDER BY na.`date` DESC;
			';
            $data['records'] = $db->select($sql);
            $data['status'] = 'OK';
			
            //mask email (malinoski.iuri@gmail.com -> mal****@gmail.com)
            $i = 0;
            foreach ($data['records'] as $row){ 
            	$maskEmail = substr($row["email"], 0, 3).'****'.substr($row["email"], strpos($row["email"], "@"));
            	$data['records'][$i]['email'] = $maskEmail;
            	$i++;
            }
            
            /** DEBUG */
            /* foreach ($data['records'] as $row) {
				error_log(
					$row["date"] . " - " . 
					$row["email"]. " - ".
					$row["subject"]. " - ".
					$row["body"]. " - ".
					$row["image"]
				);
            }*/
            
            /** DEBUG */
            // error_log( print_r($data['records'], TRUE) );
			
            echo json_encode($data);
            break;            
		case "sendNewsfeed":
			$data['status'] = 'ERR';
            $data['msg'] = 'Some problem occurred in send newsfeed';

			// Get data from page
			$subject = $_POST['subject'];
			$body = $_POST['body'];
			$fileName = ''; // image name will be update later if has file upload
			
			//Save the newsletter
			$userData = array(
				'subject' => $subject,
				'body' => $body,
				'image' => $fileName 
			);
			$insert = $db->insert('newsletter',$userData);	
			if($insert){
				$data['data'] = $insert;
				$newsletterId = $data['data']['id'];
				
				///check if has image and save in db
				if(!empty($_FILES['file']['tmp_name'])){
					$data['data'] = $insert;
					
					$fileName = $newsletterId.'-'.$_FILES['file']['name'];
					
					//upload image to hard disk
					$imageFullPathDestination = $GLOBALS['NEWSFEED_IMAGES_FULL_PATH'].$fileName;
					move_uploaded_file( $_FILES['file']['tmp_name'] , $imageFullPathDestination );
					
					///update image name in db
					$userData = array(
						'image' => $fileName
					);
					$condition = array('id' => $newsletterId);
					$update = $db->update('newsletter',$userData,$condition);
					
					if($update==false){
						$data['status'] = 'ERR';
						$data['msg'] = 'Some problem occurred in upload file.';
						break;
					}
				}else{
					///update db
					$userData = array(
						'image' => ''
					);
					$condition = array('id' => $newsletterId);
					$update = $db->update('newsletter',$userData,$condition);
				}

				/// Get all emails
				$records = $db->select("SELECT * FROM email_subscription WHERE status='A';");
				
				foreach ($records as $record) {
					//error_log($record["id"] . "-" . $record["email"]);
					
					$emaildestinatario = $record["email"];
					$idEmail = $record["id"];
					$emailsender = $GLOBALS['NEWSFEED_MAIL_SENDER'];
					$unsubscribeUrl = $GLOBALS['NEWSFEED_URL'].'unsubscribe.php?id='.$idEmail.'&email='.$emaildestinatario;
					
					$imageFullPathDestination = '';
					
					if(!empty($fileName)){
 						$imageFullPathDestination=$GLOBALS['NEWSFEED_IMAGES_FULL_PATH'].$fileName;
					}			
					
					$result = $helper->sendMail($emailsender,$emaildestinatario,$subject,$body,$imageFullPathDestination,$unsubscribeUrl);
						
					if($result){
						$data['status'] = 'OK';
						$data['msg'] = 'Newsfeed test sent!';
						
						//insert newsletter_accounting in db
						$newsletter_accounting_data = array(
								'newsletter_id' => $newsletterId,
								'email_subscription_id' => $record["id"]
						);
						$insert = $db->insert('newsletter_accounting',$newsletter_accounting_data);
					}else{
						$data['status'] = 'ERR';
						$data['msg'] = 'PHPMailer error - Message was not sent (see Apache error log)';
					}
				}				
			}	
			
			$data['status'] = 'OK';
			$data['msg'] = 'Newsfeed sent!!';
			
			echo json_encode($data); // send data for the page, such as ERR/OK msg status
			break;
		case "sendTest":
			$data['status'] = 'ERR';
			$data['msg'] = 'ERROR';
		
			// Get data from page
			$subject = $_POST['subject'];
			$body = $_POST['body'];
			$emaildestinatario = $_POST['email'];
			
			$emailsender = $GLOBALS['NEWSFEED_MAIL_SENDER'];
			$unsubscribeUrl = $GLOBALS['NEWSFEED_URL'].'unsubscribe.php?id=0&email='.$emaildestinatario;
			$fileName = ''; // if empty (''), dont try to send later

			///check if has image and save in harddisk
			$imageFullPathDestination = '';
			if(!empty($_FILES['file']['tmp_name'])){
					
				$fileName = $_FILES['file']['name'];
					
				//upload image to hard disk
				$imageFullPathDestination = $GLOBALS['NEWSFEED_IMAGES_FULL_PATH'].$fileName;
				move_uploaded_file( $_FILES['file']['tmp_name'] , $imageFullPathDestination );
			}
			
			$result = $helper->sendMail($emailsender,$emaildestinatario,$subject,$body,$imageFullPathDestination,$unsubscribeUrl);
			
			if($result){
				$data['status'] = 'OK';
				$data['msg'] = 'Newsfeed test sent!';
			}else{
				$data['status'] = 'ERR';
				$data['msg'] = 'PHPMailer error - Message was not sent (see Apache error log)';
			}
								
			echo json_encode($data); // send data for the page (ERR/OK msg status)
			break;
		case "unsubscribe":
			$email = $_GET['email'];
			$id = $_GET['id'];
			
			if($id==0){
				$data['status'] = 'OK';
				$data['msg'] = 'This is a unsubscribe test.';
			}else{
				$result = $db->simpleUpdate("UPDATE email_subscription SET status='I' WHERE id=".$id." AND email='".$email."';");
				
				if($result){
					$data['status'] = 'OK';
					$data['msg'] = 'Unsubscribe successful';
				}else{
					$data['status'] = 'ERR';
					$data['msg'] = 'Some problem occurred in unsubscribe process.';
				}
			}

			
			
            echo json_encode($data);
            break;
		case "subscribe":
			$email = $_GET['email'];
            		
			$result = $db->simpleUpdate("INSERT IGNORE INTO email_subscription(email) VALUES('$email')");
            
			if($result){
            	$data['status'] = 'OK';
            	$data['msg'] = 'Unsubscribe successful';
            }else{
            	$data['status'] = 'ERR';
            	$data['msg'] = 'Some problem occurred in unsubscribe process.';
            }
            
            echo json_encode($data);
            break;
		default:
            echo '{"status":"INVALID"}';
    }
}

<?php
include '../globals-reg-emails.php';
include $GLOBALS['REGEMAILS_LIB_DATABASE'];//TODO change this name as helper class

$db = new DB();

$data['status'] = 'TRYING';
$sql = 'SELECT email_subscription.email, email_subscription.status FROM email_subscription ORDER BY email_subscription.id desc;';
$data['records'] = $db->select($sql);
$data['status'] = 'OK';
	
$i = 0;
foreach ($data['records'] as $row){
	$maskEmail = substr($row["email"], 0, 3).'****'.substr($row["email"], strpos($row["email"], "@"));
	$data['records'][$i]['email'] = $maskEmail;
	if($row["status"]=="A"){
		$data['records'][$i]['status'] = "ACTIVE";
	}else if($row["status"]=="T"){
		$data['records'][$i]['status'] = "TEST";
	}
	$i++;
}

echo json_encode($data);

?>

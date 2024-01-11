<?php
require_once ("config/globals-daemon.php");
require_once ("../../../conf/globals-contact.php");
require_once ("../lib/sinapad-rest/rest-php-adapter.php");
require_once ("../lib/utils/email-utils.php");

include "../job-properties-mananger.php";

/** https://tools.ietf.org/html/rfc5424#section-6.3 */
openlog("[dockthor-log][daemon-check-finished.php]", LOG_PID | LOG_PERROR, LOG_LOCAL0);

$uuid = $argv [1];
$portal_id = $argv [2];
$jobPropertiesMananger = JobPropertiesMananger::getInstance();
$job_properties = $jobPropertiesMananger->getJobProperties($portal_id);

$status = "";
foreach ( $job_properties [$portal_id] ['submissions'] as $service_job_id => $value ) {
	$status = $job_properties [$portal_id] ['submissions'] [$service_job_id]['job-service-status'];
	$csgrid_id = $service_job_id;
}

$exists = false;
if ($status == "DONE") {
	$results_dir = $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/result/";
	
	//verifica md5
	$md5 = "md5sum ".$results_dir."/".$portal_id.".zip";// | awk -F ' ' '{print $1}' > ~/teste.md5";
	$md5_generated = shell_exec($md5);
	$md5_generated = substr($md5_generated,0,strpos($md5_generated," "));
	//inseri o \n pois no arquivo de md5 gerado pelo csgrid tem um \n no final do md5...
	$md5_generated = $md5_generated ."\n";
	
	$md5_get = shell_exec("cat ".$results_dir."/".$portal_id.".md5");
	
// 	$file=fopen("/home/vivian/teste.md5", "w");
// 	fwrite($file,"*".$md5_generated."*\n*".$md5_get."*");
// 	fclose($file);
	
	if($md5_generated==$md5_get){
		$exists = true;
	}
}

if ($exists) {
	if (filesize ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/result/$portal_id.zip" ) > 0) {
		rest_delete ( null, $portal_id, $uuid );
		rename ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking_finished/$portal_id", $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/success/$portal_id" );
	
		$constants = array(
				'VAR_DELETE_DATE' => date("d/m/Y",  mktime (0, 0, 0, date("m"), date("d")+30, date("Y"))),
				'VAR_JOB_LINK' => $GLOBALS['DOCKTHOR_URL'] . "index.php?tab=DOCKING&page=RESULTS&jobId=$portal_id"
		);
	
		$message = strtr(file_get_contents("../email-templates/job-finished.html"), $constants);
	
		$mailResponse = EmailUtils::sendEmail($job_properties [$portal_id] ['email'], "[DockThor] JOB $portal_id FINISHED", $message, true);
		
		if($mailResponse){
		    
		    syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id - Mail status sent to ".json_encode($job_properties [$portal_id] ['email'])."!");
		    
		    $job_properties[$portal_id]['submissions'][$csgrid_id]['mail-sent-finish-job'] = date("Y-m-d H:i:s");
		    $saveJobPropertiesResult = $jobPropertiesMananger->saveJobProperties($portal_id, $job_properties);
		    
		    if($saveJobPropertiesResult){
		        syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id - The property was file updated with sent mail date for finished job! ".json_encode($job_properties));
		    }else{
		        syslog(LOG_INFO|LOG_LOCAL0, "ERROR - Job $portal_id - Some error occurred when update the property file ".json_encode($job_properties));
		    }
		    
		}else{
		    syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id - mail status sent to ".$job_properties [$portal_id] ['email']."... error!");
		}
	} else {
		rename ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking_finished/$portal_id", $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/running/$portal_id" );
	}
} else {
	
    // Move job from checking to downloading_error
    syslog(LOG_INFO|LOG_LOCAL0, "ERROR - Moving job $portal_id from checking to downloading_error!");
    	rename ( $GLOBALS['DOCKTHOR_PATH']."/apps/docking/daemon/checking_finished/$portal_id", $GLOBALS['DOCKTHOR_PATH']."/apps/docking/daemon/downloading_error/$portal_id" );
    	
    // Send notification error to admin
    	syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id - Sending notification error to admin");
	foreach ($GLOBALS['emails'] as $i => $value){
        ini_set ( "SMTP", "smtp.sinapad.lncc.br" );
        ini_set ( "sendmail_from", "dockthor@lncc.br" );
        $headers = "From: DockThor <dockthor@lncc.br>\r\n";
        //$headers .= "Reply-To: dockthor@lncc.br\r\n";
        $body = "JOB ID: $csgrid_id";
        $body .= "\nDOCKTHOR ID: $portal_id";
        $body .= "\n".$GLOBALS['DOCKTHOR_URL']."index.php?tab=DOCKING&page=RESULTS&jobId=$portal_id";
		// mail ( $emails[$i], "Docking Error - Status DONE and failed to download - $portal_id", $body, $headers );
        mail ( $value, "Docking Error - Status DONE and failed to download - $portal_id", $body, $headers );
    }

}
?>

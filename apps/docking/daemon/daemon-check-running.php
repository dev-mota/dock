<?php
//require_once ("../environment/environment-config.php");
require_once ("config/globals-daemon.php");
require_once ("../lib/sinapad-rest/rest-php-adapter.php");
require_once ("../lib/utils/email-utils.php");

include "../job-properties-mananger.php";

/** https://tools.ietf.org/html/rfc5424#section-6.3 */
openlog("[dockthor-log][daemon-check-running.php]", LOG_PID | LOG_PERROR, LOG_LOCAL0);

$uuid = $argv [1];
$portal_id = $argv [2];

$jobPropertiesMananger = JobPropertiesMananger::getInstance();
$job_properties = $jobPropertiesMananger->getJobProperties($portal_id);

$status = "";
foreach ( $job_properties [$portal_id] ['submissions'] as $service_job_id => $value ) {
    
    syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id $service_job_id; checking status .... ");
    
    if (strpos($service_job_id, 'Dock@Dock.') === 0) {
        // It starts with 'Dock@Dock'
        $result = rest_status ( $service_job_id, $uuid );
        $status = $result->{'status'};
        $job_properties [$portal_id] ['submissions'] [$service_job_id]['job-service-status'] = $status;
        syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id $service_job_id; checking status response: $status");
    }else{
        syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id has no status yet in csgrid");
        //$mailResponse = mail("dockthor@lncc.br", "[DockThor] Daemon warning" , "Job $portal_id has invalid service_job_id: $service_job_id", "Content-type: text/html; charset=utf-8" . "\r\n", '-fdockthor@lncc.br');
    }
	
}

$jobPropertiesMananger->saveJobProperties($portal_id, $job_properties);

$results_dir = $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/result/"; 
if ($status == "DONE") {
	rename ( $GLOBALS['DOCKTHOR_PATH']."apps/docking/daemon/checking/" . $portal_id, $GLOBALS['DOCKTHOR_PATH']."apps/docking/daemon/downloading/" . $portal_id );
	if(file_exists($results_dir) && is_dir($results_dir)){
		shell_exec("rm -rf $results_dir/*");
	} else {
		mkdir ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/result/" );
	}
	rest_download ( "$portal_id/OUTPUT", "dockthor.zip", $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/result/$portal_id.zip", $uuid );
	rest_download ( "$portal_id/OUTPUT", "dockthor.md5", $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/result/$portal_id.md5", $uuid );
	
	rename ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/downloading/$portal_id", $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/finished/$portal_id" );
	
} else if ($status == "FAILED") {
	if(file_exists($results_dir) && is_dir($results_dir)){
		shell_exec("rm -rf $results_dir/*");
	} else {
		mkdir ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/result/" );
	}
	rest_download ( "$portal_id/OUTPUT", "dockthor.zip", $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/jobs/$portal_id/result/$portal_id.zip", $uuid );
	rest_delete ( null, $portal_id, $uuid );
	rename ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking/$portal_id", $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/error/$portal_id" );
} else if ($status == "UNDETERMINED") {
	rest_delete ( null, $portal_id, $uuid );
        rename ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking/$portal_id", $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/undetermined/$portal_id" );
	
}else if ($status == "CANCELLED") {
	rest_delete ( null, $portal_id, $uuid );
	rename ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking/$portal_id", $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/cancelled/$portal_id" );
} else {
	rename ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking/$portal_id", $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/running/$portal_id" );
}
?>

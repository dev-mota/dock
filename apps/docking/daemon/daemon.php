<?php     
require_once ("config/globals-daemon.php");
require_once ("../lib/sinapad-rest/rest-php-adapter.php");
require_once ("../job-properties-mananger.php");

/** https://tools.ietf.org/html/rfc5424#section-6.3 */
openlog("[dockthor-log][daemon.php]", LOG_PID | LOG_PERROR, LOG_LOCAL0);

syslog(LOG_INFO|LOG_LOCAL0, "################");
syslog(LOG_INFO|LOG_LOCAL0, "# Start daemon #");

$uuid = "";
if (file_exists ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/uuid.txt" )) {
	$uuid = file_get_contents ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/uuid.txt" );
}
if (rest_user_info ( $uuid ) != 200) {
	
	rest_logout ( $uuid );
	$uuid = rest_login ();

	file_put_contents ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/uuid.txt", $uuid );
}
if (! isset ( $uuid ) || empty ( $uuid )) {
	die ();
}

/** Mecanismo para verificar se a grade esta OK*/
$resourcesResult = rest_get_resources($uuid);
// $skipOneLigants = false;

/** "ALTO"           - If 1 ligand sends to altix; if > 1 ligands sends to sdumont"
 *  "SDUMONT_ONLY"   - All jobs to sdumont
 */ 
$submissionType = "TRADICIONAL";

/*if($resourcesResult->code != "200"){
    syslog(LOG_INFO|LOG_LOCAL0, "ERROR - Could not login at csgrid!");
    die();
}else{
    
    if(isset($resourcesResult->elements)){
        
        $resourcesResultView = array();
        $resourcesResultView['code'] = $resourcesResult->code;
        $resourcesResultView['elements'] = array();
        foreach($resourcesResult->elements->element as $key => $value){
            
            // Build elements ($resource[]) to print on debug
            $resource['name'] = $value->name;
            $resource['code'] = $value->code;
            if(isset($value->nodes) && isset($value->nodes->nodes)){
                $resource['nodesCount'] = count($value->nodes->nodes);
            }
            $resource['numOfJobs'] = $value->numOfJobs;
            $resource['numOfProc'] = $value->numOfProc;            
            array_push($resourcesResultView['elements'], $resource);
            
            // Check altix - case off, all submission will be sent to sdumont
            if($value->name == "sga-lncc-sge-altix-xe_rm1model"){                

                //if(true){ // isso simula a altix off (comente a linha abaixo)
                if(count($value->nodes->nodes) == 0){
                    syslog(LOG_INFO|LOG_LOCAL0, "Get CSGrid resouces: altix - zero nodes");
                    $submissionType = "DOCKING_ALTERNATIVE";
//                  $skipOneLigants = true;
                }
                
            }
            
        }
        
        syslog(LOG_INFO|LOG_LOCAL0, "Get CSGrid resouces: OK; Details: ".json_encode($resourcesResultView)); // JSON_PRETTY_PRINT nao formata bem no syslog
        
    }
}
*/

$submissionType = "TRADICIONAL";

$files = preg_grep ( '/^([^.])/', scandir ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/running" ) );

if(count($files)==0){
    syslog(LOG_INFO|LOG_LOCAL0, "Running jobs : 0");
}

foreach ( $files as $portal_id ) {
    
    syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; running");
	rename ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/running/$portal_id", $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking/$portal_id" );
	shell_exec ( "nohup php " . $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/daemon-check-running.php '$uuid' '$portal_id' > /dev/null 2>/dev/null &" );
	//shell_exec ( "nohup php " . $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/daemon-check-running.php '$uuid' '$portal_id' > /dev/null 2> error.log &" );
	//shell_exec ( "php " . $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/daemon-check-running.php '$uuid' '$portal_id'" );
	
}

$files_checking = preg_grep ( '/^([^.])/', scandir ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking" ) );

// syslog(LOG_INFO|LOG_LOCAL0, "Jobs in running folder: ".count($files));
// syslog(LOG_INFO|LOG_LOCAL0, "Jobs in checking folder: ".count($files_checking));

$num_jobs_submitted = count($files) + count($files_checking);


if ( $num_jobs_submitted < $GLOBALS ['MAX_NUMBER_JOBS'] ) {
	
	$files = preg_grep ( '/^([^.])/', scandir ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/pending" ) );
	
	if(count($files)==0){
	    syslog(LOG_INFO|LOG_LOCAL0, "Pending jobs : 0");
	}
	
	$count = 0;
	
	foreach ( $files as $portal_id ) {	    
	    
	    // Count ligants.
	    $jobPropertiesMananger = JobPropertiesMananger::getInstance();
	    $job_properties = $jobPropertiesMananger->getJobProperties($portal_id);
	    $howMuchLigants = count(end($job_properties[$portal_id] ['submissions'])['file-args']['l']); // ultima submissao (como feito no checking running)
	    syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id; pending; howMuchLigants=".$howMuchLigants);
	    
	    $runJob = true;
	    if($GLOBALS['DISABLE_SDUMONT']==true){
	        syslog(LOG_INFO|LOG_LOCAL0, "DISABLE_SDUMONT=true");
	        
	        if($howMuchLigants>1){
	            $runJob = false;
	            syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id will not be sent to run, due DISABLE_SDUMONT=true and countLigantsFromLastSubmission>1");
	            
	        }else if ( ($howMuchLigants==1) && ($submissionType=='DOCKING_ALTERNATIVE') ){
                $runJob = false;
                syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id will not be sent to run, due: DISABLE_SDUMONT=true; countLigantsFromLastSubmission=1; and altx is down");
                
            }
	        
	    } else {
			if ( ($howMuchLigants>1) && ($GLOBALS['DISABLE_VS']==true) ){
				$runJob = false;
				syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id will not be sent to run, due: DISABLE_VS=true; ligand>1");
			
			}
		}
	    
	    if($runJob){
            syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id will be sent pending folder and daemon-check-pending.php  will be executed");
	        rename ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/pending/$portal_id", $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/checking/$portal_id" );
	        shell_exec ( "nohup php " . $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/daemon-check-pending.php '$uuid' '$portal_id' '$submissionType' > /dev/null 2>/dev/null &" );
	        //shell_exec ( "nohup php " . $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/daemon-check-pending.php '$uuid' '$portal_id' '$submissionType' > /dev/null 2> error.log &" );
	        
	        $count++;
	        if ( $count == $GLOBALS ['MAX_NUMBER_JOBS'] ) {
	            break;
	        }
	    }
	    
		
	}
}else{
    syslog(LOG_INFO|LOG_LOCAL0, "Waiting - There are jobs in queue, because many jobs are running at this moment");    
}

$files = preg_grep ( '/^([^.])/', scandir ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/finished" ) );

foreach ( $files as $portal_id ) {    
    syslog(LOG_INFO|LOG_LOCAL0, "Job $portal_id - finished");
	rename ( $GLOBALS['DOCKTHOR_PATH']."/apps/docking/daemon/finished/$portal_id", $GLOBALS['DOCKTHOR_PATH']."/apps/docking/daemon/checking_finished/$portal_id" );
	shell_exec ( "nohup php ". $GLOBALS['DOCKTHOR_PATH']."/apps/docking/daemon/daemon-check-finished.php '$uuid' '$portal_id' > /dev/null 2>/dev/null &" );
	//shell_exec ( "nohup php ". $GLOBALS['DOCKTHOR_PATH']."/apps/docking/daemon/daemon-check-finished.php '$uuid' '$portal_id' > /dev/null 2> error.log &" );
}
?>

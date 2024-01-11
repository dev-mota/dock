<?php

require_once __DIR__.'/../../docking/lib/sinapad-rest/rest-php-adapter.php';

class JobMonitoringLib
{
    public function __construct(){
        openlog("[dockthor-log][JobMonitoringLib.php]", LOG_PID | LOG_PERROR, LOG_LOCAL0);
    }
    
    public function listJobs($from, $jobsPerPage, $selectedFilter, $jobNameToSearch, $selectedFilterNumberOfLigant, $jobDateFrom, $jobDateTo){
        
        $filter = '/\.\/(.*)/';
	if($jobNameToSearch!=null){ 
		//$filter = '~' . $jobNameToSearch . '~i';
		$jobsAllFolders = array("$jobNameToSearch");
	} else {

	exec("cd ../../docking/daemon/jobs;find . -maxdepth 1", $jobsAllFolders, $return_val);
       
        // Get all job folders
	//$jobsAllFolders = preg_grep ( $filter, scandir( "../../docking/daemon/jobs2" ) ); // exclusão de "." e ".."
	
	$jobsAllFolders = preg_grep ( $filter, $jobsAllFolders );

	$jobsAllFolders = array_values ( $jobsAllFolders ); // reordena inidice para começar de 0 mesmo após a exclusão de "." e ".."
        
        $jobs = array();
        
        // Add properties:
	$jobPropertiesMananger = JobPropertiesMananger::getInstance();

	}

	foreach ( $jobsAllFolders as $portalId ) {

	    $portalId = preg_replace("[./]", "", $portalId);

            $job = array();
            
            // Id
            $job['id'] = $portalId;
            
            // Property file
            $prop = $jobPropertiesMananger->getJobProperties($portalId);
            $job['property-file'] = $prop;
            
            // Portal submission date
            $job['submission-date'] = $job['property-file'][$portalId]['portal-submission-date'];
            
            // Daemon folders (Para cada folder, exrair os diretorios de interesse "checking", "pending". Exceto a pasta 'jobs')
            $relativeFolders = glob("../../docking/daemon/*/$portalId");
            $folders = array();
            $foundFilteredFolder = false;
            $countFoundedFounder = 0;
            foreach ( $relativeFolders as $folder ){
                
                $lastBarIndex = strrpos($folder, "/");
                $firstParse = substr($folder, 0, $lastBarIndex);
                $lastBarIndex = strrpos($firstParse, "/");
                $folder = substr($firstParse, $lastBarIndex+1, strlen($firstParse));
                if($folder!="jobs"){
                    $countFoundedFounder++;
                    array_push($folders,$folder);
                }
            }
            if($countFoundedFounder==0){
                array_push($folders,'no folder');
            }
            $job['folders'] = $folders;
            
            array_push($jobs, $job);
	}

        $resultJobs = array();
        
        // Filter by folder
        if($selectedFilter != 'any'){
            foreach ($jobs as $job ) {
                foreach ($job['folders'] as $folder ) {
                    if($selectedFilter==$folder){
                        array_push($resultJobs,$job);
                    }
                }
            }
        }else{
            $resultJobs = $jobs;
        }
        
        // Filter by quantity of ligants
        if($selectedFilterNumberOfLigant=='1' || $selectedFilterNumberOfLigant=='>1'){
            $filteredJobs = array();
            foreach ($resultJobs as $job ) {
                $found = false;
                foreach ($job['property-file'][$job['id']]['submissions'] as $submission) {
                    $ligantsQnt = count($submission["file-args"]["l"]);
                    if($selectedFilterNumberOfLigant=='1' && $ligantsQnt==1){
                        $found = true;
                    }else if($selectedFilterNumberOfLigant=='>1' && $ligantsQnt>1){
                        $found = true;
                    }
                }
                if($found){
                    array_push($filteredJobs,$job);
                }
                
            }
            $resultJobs = $filteredJobs;
        }
        
        // Filter by date
        $resultJobsFilteredByDate = array();
        if($jobDateFrom!=null || $jobDateTo!=null){
            
            $fromDate = strtotime($jobDateFrom);
            $toDate = strtotime($jobDateTo);
            $toDate = $toDate+86399; // add 86399 seconds. This make the day with 23:59 hour
            
            $filteredJobs = array();
            foreach ($resultJobs as $job ) {
                
                $jobDate = strtotime($job['property-file'][$job['id']]['portal-submission-date']);
                
                // only From
                if($fromDate!=null && $jobDateTo==false){
                    if($jobDate >= $fromDate){
                        array_push($resultJobsFilteredByDate, $job);
                    }
                }
                // only To
                else if($jobDateTo!=null && $fromDate==false){
                    if($jobDate <= $toDate){
                        array_push($resultJobsFilteredByDate, $job);
                    }
                }
                // both
                else{
                    if( ($jobDate >= $fromDate) && ($jobDate <= $toDate)){
                        array_push($resultJobsFilteredByDate, $job);
                    }
                }
                
            }
            
        }
        if(count($resultJobsFilteredByDate)>0){
            $resultJobs = $resultJobsFilteredByDate;
        }
        
        // order by submission date
        usort($resultJobs, function($a, $b) {
            $t1 = strtotime($b['submission-date']);
            $t2 = strtotime($a['submission-date']);
            return $t1 - $t2;
        });
            
            // Slice jobs (pagination)
            $slicedJobs = array_slice($resultJobs,$from,$jobsPerPage, true);
            
            // responde data
            $data['totalJobs'] = count($resultJobs);
            $data['jobs'] = $slicedJobs;
            return $data;
    }
    
    public function resubmitJob($portalJobId){
        
        $result = false;
        
	if($portalJobId != null){

		$illegalChar = array("..", "php", "html", ",", "?", "!", ":", ";", "-", "+", "<", ">", "%", "~", "€", "$", "[", "]", "{", "}", "@", "&", "#", "*", "„");
                $portalJobId = str_replace($illegalChar, "", $portalJobId);

            
            // caso esteja em cancelled, mova para pending
            $pathFrom = __DIR__."/../../docking/daemon/cancelled/$portalJobId";
            if(file_exists($pathFrom)){
                if($this->moveJobToPending($pathFrom, $portalJobId)){
                    $result = true;
                } else {
                    $result = false;
                }
            }
            
            // caso esteja (tambem) em error, mova para pending
            $pathFrom = __DIR__."/../../docking/daemon/error/$portalJobId";
            if(file_exists($pathFrom)){
                if($this->moveJobToPending($pathFrom, $portalJobId)){
                    $result = true;
                } else {
                    $result = false;
                }
            }
            
            // caso esteja (tambem) em success, mova para pending
            $pathFrom = __DIR__."/../../docking/daemon/success/$portalJobId";
            if(file_exists($pathFrom)){
                if($this->moveJobToPending($pathFrom, $portalJobId)){
                    $result = true;
                } else {
                    $result = false;
                }
            }
            
            // caso esteja (tambem) em success, mova para pending
            $pathFrom = __DIR__."/../../docking/daemon/checking/$portalJobId";
            if(file_exists($pathFrom)){
                if($this->moveJobToPending($pathFrom, $portalJobId)){
                    $result = true;
                } else {
                    $result = false;
                }
            }
            
            // Caso n for encontrado
            if($result == false){
                syslog(LOG_INFO|LOG_LOCAL0, "Error on resubmitJob job $portalJobId. Could not find job in 'cancelled', 'error' or 'success' folder");
            }
            
        }
        
        return $result;
    }
    
    private function moveJobToPending($pathFrom,$portalJobId){
        $jobPath = __DIR__."/../../docking/daemon/jobs/$portalJobId";
        $pendingPath = __DIR__."/../../docking/daemon/pending/$portalJobId";
        
        if(file_exists($jobPath)){
            if(rename ( $pathFrom, $pendingPath )){
                return true;
            } else {
                syslog(LOG_INFO|LOG_LOCAL0, "Error on resubmitJob job $portalJobId. Could not rename($pathFrom, $pendingPath");
                return false;
            }
        } else {
            syslog(LOG_INFO|LOG_LOCAL0, "Error on resubmitJob job $portalJobId. File not exists $jobPath");
            return false;
        }
    }
    public function cancelJob($serviceJobId){
        
        $responseCode = null;
        
        if($serviceJobId != null){
            $uuid = rest_login();
            
            if($uuid !=null){
                $responseCode = rest_cancel($serviceJobId, $uuid);
                rest_logout($uuid);
            }
            
        }
        
        return $responseCode;
        
    }
    
    public function checkResultForDownload($portalId,$serviceId){
        
        $target = __DIR__."/../../docking/daemon/jobs/$portalId/result/$portalId.zip";
        
        if (file_exists($target)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function downloadResults($portalId,$serviceId){
        
        $target = __DIR__."/../../docking/daemon/jobs/$portalId/result/$portalId.zip";
        
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=".$portalId."_".$serviceId.".zip");
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: binary");
        
        if(readfile($target)){
            return true;
        }else{
            return false;
        }
    }
}


<?php
//TODO: -Edson utilizar configurações de outro arquivo
require_once ("../../docking/daemon/config/globals-daemon.php");
include_once "../../docking/job-properties-mananger.php";
include_once "../lib/JobMonitoringLib.php";

// $postdata = file_get_contents("php://input");
// $request = json_decode($postdata);
// $action = $request->action;

$response = array();
$response ['status'] = 'ERR';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    // Case POST
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $action = $request->action;
} else if(isset($_REQUEST['action']) && !empty($_REQUEST['action'])){
    // Case GET
    $action = $_REQUEST['action'];
}
else{
    // If not POST, GET or no 'action'
    exit();
}

function isEnabled($func) {
    return is_callable($func) && false === stripos(ini_get('disable_functions'), $func);
}

switch ($action) {
    
case "getJobsInfoWithParam" :


	$from = $request->from;
	$jobsPerPage = $request->jobsPerPage;
	$selectedFilter = $request->selectedFilter;
	$jobNameToSearch = $request->jobNameToSearch;
	$selectedFilterNumberOfLigant = $request->selectedFilterNumberOfLigant;
        $jobDateFrom = $request->jobDateFrom;
        $jobDateTo = $request->jobDateTo;
                
	$jobMonitoringLib = new JobMonitoringLib();


        $data = $jobMonitoringLib->listJobs($from, $jobsPerPage, $selectedFilter, $jobNameToSearch, $selectedFilterNumberOfLigant, $jobDateFrom, $jobDateTo);

        $response = $data;
        $response['status'] = 'OK';
        
        break;
    case "resubmitJob":
        
        $portalJobId = $request->portalJobId;   
        $jobMonitoringLib = new JobMonitoringLib();
        if($jobMonitoringLib->resubmitJob($portalJobId)){
            $response['status'] = 'OK';
        }else{
            $response['status'] = "ERROR";
        }
        
        break;
    case "showPropertyFile":
        
	$portalJobId = $request->portalJobId;

	$illegalChar = array("..", "html", "php", ",", "?", "!", ":", ";", "-", "+", "<", ">", "%", "~", "€", "$", "[", "]", "{", "}", "@", "&", "#", "*", "„");
        
	$portalJobId = str_replace($illegalChar, "", $portalJobId);	

        $path = dirname(__FILE__)."/../../docking/daemon/jobs/$portalJobId/properties.json";
        $fileContent = null;
        if(file_exists($path)){
            $fileContent = file_get_contents($path);
        }
        
        $response['status'] = 'OK';
        $response['propertyContent'] = $fileContent;
        
        break;
    case "showPropertyFileTest":
        
        $testType = $request->testType;
        
        $testFolder = "";
        if($testType == 'short1lig'){
            $testFolder = "gmmsb_admin_test_fast";
        }else if($testType == 'complete1lig'){
            $testFolder = "gmmsb_admin_test_complete";
        } else if($testType == 'shortVs'){
            $testFolder = 'gmmsb_admin_test_fast_vs';
        } else if($testType == 'completeVs'){
            $testFolder = 'gmmsb_admin_test_complete_vs';
        }
        
	if($testFolder!=""){

	    $illegalChar = array("..", "html", "php", ",", "?", "!", ":", ";", "-", "+", "<", ">", "%", "~", "€", "$", "[", "]", "{", "}", "@", "&", "#", "*", "„");

            $testFolder = str_replace($illegalChar, "", $testFolder);
	
            $path = dirname(__FILE__)."/../test_job/$testFolder/properties.json";
            $fileContent = null;
            if(file_exists($path)){
                $fileContent = file_get_contents($path);
            }
            
            $response['status'] = 'OK';
            $response['propertyContent'] = $fileContent;
        }else{
            $response['error'] = "level 001";
            $response['status'] = "ERROR";
        }
        
        break;
    case "cancelJob":
        
        $serviceJobId = $request->serviceJobId;
        $response['status'] = "ERROR";
        
        $jobMonitoringLib = new JobMonitoringLib();
        $responseCode = $jobMonitoringLib->cancelJob($serviceJobId);
        if($responseCode=='200'){
            $response['status'] = 'OK';
        }else{
            $response['status'] = "ERROR";
            $response['errormessage'] = "error code $responseCode";
        }
        
        break;
    case "checkResultForDownload":
        $portalId = $request->portalId;
        $serviceId = $request->serviceId;
        
        $jobMonitoringLib = new JobMonitoringLib();
        $checkResultForDownloadResponse = $jobMonitoringLib->checkResultForDownload($portalId,$serviceId);
        
        if($checkResultForDownloadResponse){
            $response['status'] = 'OK';
        }else{
            $response['status'] = "ERROR";
        }
        
        break;
    case "downloadResults":
        
        $portalId = $_REQUEST['portalId'];
        $serviceId = $_REQUEST['serviceId'];
        
        $jobMonitoringLib = new JobMonitoringLib();
        $response = $jobMonitoringLib->downloadResults($portalId,$serviceId);
        
        break;
    case "submitTestJob":
        
        $testType = $request->testType;
        
        $testFolder = "";
        if($testType == 'short1lig'){
            $testFolder = "gmmsb_admin_test_fast";
        }else if($testType == 'complete1lig'){
            $testFolder = "gmmsb_admin_test_complete";
        } else if($testType == 'shortVs'){
            $testFolder = 'gmmsb_admin_test_fast_vs';
        } else if($testType == 'completeVs'){
            $testFolder = 'gmmsb_admin_test_complete_vs';
        }
        
        if($testFolder!=""){
            
            if (isEnabled('shell_exec')) {
                
                /** Create the test folder with new id */
                $digits = 10;
                $ramdonId = rand(pow(10, $digits-1), pow(10, $digits)-1);
                $portalId = $testFolder."_id".$ramdonId;
                shell_exec("cp -r ../test_job/$testFolder ../../docking/daemon/jobs/$portalId");
                
                /** Add/change some info **/
                $jobPropertiesMananger = JobPropertiesMananger::getInstance();
                $jobProperties = $jobPropertiesMananger->getJobProperties($portalId);   
                
                // Change job id
                $jobProperties[$portalId] = $jobProperties[$testFolder];
                unset($jobProperties[$testFolder]);
                
                // Add new date
                $jobProperties[$portalId]['portal-submission-date'] = date("Y-m-d H:i:s");
                
                // Save new properties
                $savePropertiesResult = $jobPropertiesMananger->saveJobProperties($portalId, $jobProperties);
                
                // Check result
                if($savePropertiesResult){
                    
                    shell_exec("echo pending | tee ../../docking/daemon/pending/$portalId");                    
                    $response['status'] = "OK";
                }else{
                    $response['error'] = "level 003";
                    $response['status'] = "ERROR";
                }
                
                
            }else{
                $response['error'] = "level 002";
                $response['status'] = "ERROR";
            }            
            
        }else{
            $response['error'] = "level 001";
            $response['status'] = "ERROR";
        }
        
        break;
    default :
        $response ['status'] = 'ERR';
}

echo json_encode ( $response );
?>

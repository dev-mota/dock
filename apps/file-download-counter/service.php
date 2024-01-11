<?php

require_once 'lib/DownloadCounter.php';

openlog("[dockthor-log][file-download-counter]", LOG_PID | LOG_PERROR, LOG_LOCAL0);
$request = file_get_contents("php://input");
$requestJson = json_decode($request);
$action = $requestJson->action;
$responseData['message'] = null;

if($_SERVER['REQUEST_METHOD'] == "PUT"){
    
    switch ($action) {     
        case "registerDatasetDownload":
            
		$filePath = $requestJson->filePath;
		$illegalChar = array("..", "php", "html", ",", "?", "!", ":", ";", "+", "<", ">", "%", "~", "€", "$", "[", "]", "{", "}", "@", "&", "#", "*", "„");
                $filePath = str_replace($illegalChar, "", $filePath);
            $isFileExist = file_exists(__DIR__."/../../".$filePath);

            if($isFileExist ){

                $downloadCounter = new DownloadCounter();
                $result = $downloadCounter->registerDownload($filePath);

                if($result){
                    $responseCode = 200;
                    $responseData['message'] = 'success';
                } else {
                    syslog(LOG_ERR|LOG_LOCAL0, "ERROR: registerDatasetDownload - internal lib fail");
                    $responseCode = 400;
                    $responseData['message'] = 'internal lib fail';
                }
                
            } else {
                syslog(LOG_ERR|LOG_LOCAL0, "ERROR: registerDatasetDownload - file not exists: ".$filePath);         
                $responseCode = 400;
                $responseData['message'] = 'action fail';
            }

            break;
        default :   
            syslog(LOG_ERR|LOG_LOCAL0, "ERROR: registerDatasetDownload - action fail");         
            $responseCode = 400;
            $responseData['message'] = 'action fail';
            break;
    } 

} else {
    syslog(LOG_ERR|LOG_LOCAL0, "ERROR: registerDatasetDownload - request method fail");         
    $responseCode = 400;
    $responseData['message'] = 'request method fail';
}

http_response_code($responseCode);
echo json_encode($responseData);


<?php
require_once ("../docking/daemon/config/globals-daemon.php");
require_once ("../docking/lib/sinapad-rest/rest-php-adapter.php");

/** https://tools.ietf.org/html/rfc5424#section-6.3 */
openlog("[dockthor-log][monitor.php]", LOG_PID | LOG_PERROR, LOG_LOCAL0);

function login(){
    
    // Create uuid
    if (file_exists ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/uuid.txt" )) {
        $uuid = file_get_contents ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/uuid.txt" );
    }
    
    // Se não estiver logado, o faça
    $restUserInfoResult = rest_user_info($uuid);
    if ( ($restUserInfoResult != 200) && ($restUserInfoResult != null)) {
        rest_logout ( $uuid );
        $uuid = rest_login ();
        file_put_contents ( $GLOBALS ['DOCKTHOR_PATH'] . "/apps/docking/daemon/uuid.txt", $uuid );
    }
    if (!isset ( $uuid ) || empty ( $uuid )) {
        return null;
    }else{
        return $uuid;
    }
}

function buildStatusDefault($sgaList){
    $statusArray = array();
    
    $statusArray['general'] = array();
    
    /** csgrid problems*/
    $statusArray['general']['csgrid'] = array();
    $statusArray['general']['csgrid']['problems'] = array();
    $statusArray['general']['csgrid']['problems']['unavailable'] = array();
    $statusArray['general']['csgrid']['problems']['unavailable']['status']  = false;
    $statusArray['general']['csgrid']['problems']['unavailable']['notified'] = false;
    
    /** rest problems*/
    $statusArray['general']['rest'] = array();
    $statusArray['general']['rest']['problems'] = array();
    $statusArray['general']['rest']['problems']['unavailable'] = array();
    $statusArray['general']['rest']['problems']['unavailable']['status']  = false;
    $statusArray['general']['rest']['problems']['unavailable']['notified'] = false;
    
    /** sgas problems */
    $statusArray['sgas'] = array();
    foreach($sgaList as $key => $value){
        $statusArray['sgas'][$value]['problems'] = array();
        $statusArray['sgas'][$value]['problems']['zero_nodes']['status'] = false;
        $statusArray['sgas'][$value]['problems']['zero_nodes']['notified'] = false;        
    }
    
    return $statusArray;
}

function createStatusFile($sgaList, $statusFileName){
    
    try{
        if (!file_exists($statusFileName)){
            
            $statusArray = buildStatusDefault($sgaList);
            
            $fp = fopen($statusFileName, 'w');
            fwrite($fp, json_encode($statusArray, JSON_PRETTY_PRINT));
            fclose($fp);
            
            syslog(LOG_INFO|LOG_LOCAL0, "Status file created with default values\n");
            
            return $statusArray;
        } else{
            return json_decode(file_get_contents($statusFileName), true);
        }
        
    }catch (Exception $e) {
        syslog(LOG_INFO|LOG_LOCAL0, "Erro: houve um problema ao criar o arquivo de status: ".$e->getMessage());
        return null;
    }
    return null;
}

function updateStatusFile($statusArrayUpdated, $statusFileName){
    try{
        $fp = fopen($statusFileName, 'w');
        fwrite($fp, json_encode($statusArrayUpdated, JSON_PRETTY_PRINT));
        fclose($fp);
        return true;
    }catch (Exception $e) {
        syslog(LOG_INFO|LOG_LOCAL0, "Erro: houve um problema ao atualizar o arquivo de status: ".$e->getMessage());
        return false;
    }
    return false;
}

function addInfo($resources){
    
    if($resources->code != "200"){
        return $resources;
    }else{
        
        // Array to include future problems of csgrid
        $resources->problems = array();
        
        if(isset($resources->elements)){
            foreach($resources->elements->element as $key => $value){
                
                // Count nodes
                if(isset($value->nodes) && isset($value->nodes->nodes)){
                    $resources->elements->element[$key]->nodesCount = count($value->nodes->nodes);
                }
            }            
        }
        
    }
    return $resources;
}

function checkProblems($resources, $sgaList, $statusArray){
    
    // Check if CSGrid is off
    if($resources->code != "200"){       
        $statusArray['general']['csgrid']['problems']['unavailable']['status'] = true;
        return $statusArray;
    }else{
        
        $statusArray['general']['csgrid']['problems']['unavailable']['status'] = false;
        if(isset($resources->elements)){
            
            foreach($resources->elements->element as $key => $value){
                
                foreach($sgaList as $sgaKey => $sgaValue){

                    if($value->name == $sgaValue){
                        
                        // Check problem: zero nodes
                        if($resources->elements->element[$key]->nodesCount==0){
                            $statusArray['sgas'][$sgaValue]['problems']['zero_nodes']["status"] = true;
                        }else{
                            $statusArray['sgas'][$sgaValue]['problems']["zero_nodes"]["status"] = false;
                        }
                    }
                    
                }
                
                
            }
        }
    }
    return $statusArray;
}

// function hasProblem($statusArray){
    
//     $foundProblem = false;
    
//     foreach($statusArray['problems'] as $key => $value){
        
//         if ($statusArray['problems'][$key]['status']){
//             $foundProblem = true;
//         }else{
//             foreach($statusArray['sgas'] as $sgaKey => $sgaValue){
//                 foreach($sgaValue['problems'] as $problemKey => $problemValue){
//                     if($problemValue['status']){
//                         $foundProblem = true;
//                     }
//                 }
//             }
//         }
        
//     }
    
//     return $foundProblem;    

// }

function sendMail($statusArray, $titleMessage, $message){
    
    $bodyMessage = "\n
        <p>$message</p>
        <p>Detalhe de todos os recursos monitorados:</p>
        <pre><code>".(json_encode ( $statusArray, JSON_PRETTY_PRINT ))."</code><pre><br>";
    
    //$mailResponse = mail("dockthor@lncc.br", $titleMessage, $bodyMessage, "Content-type: text/html; charset=utf-8" . "\r\n", '-fdockthor@lncc.br');
    $mailResponse = mail("isabella.alvimg@gmail.com", $titleMessage, $bodyMessage, "Content-type: text/html; charset=utf-8" . "\r\n", '-fdockthor@lncc.br');
    $mailResponse = mail("malinoski.iuri@gmail.com", $titleMessage, $bodyMessage, "Content-type: text/html; charset=utf-8" . "\r\n", '-fdockthor@lncc.br');
    if(!$mailResponse){
        return false;
    }
    
    syslog(LOG_INFO|LOG_LOCAL0, "An email was sented:
titleMessage: $titleMessage\n
statusArray: ". json_encode($statusArray, JSON_PRETTY_PRINT));
    
    return true;
    
}

function notify($statusArray,$statusFileName){
    
    /** $resourceTypeKey = general or sgas */
    foreach($statusArray as $resourceTypeKey => $resourceTypeValue){        
    
        /** $resource = rest or csgrid */
        foreach($resourceTypeValue as $resourceKey => $resourceValue){
            
            foreach($resourceValue['problems'] as $problemKey => $problemValue){
                
                if($problemValue['status']==true){
                    if($problemValue['notified']==false){
                        
                        /** Problem found */
                        
                        /** update status file */
                        $statusArray[$resourceTypeKey][$resourceKey]['problems'][$problemKey]['notified'] = true;
                        updateStatusFile($statusArray,$statusFileName);
                        
                        /** send mail */
                        sendMail($statusArray, "Resource Monitor - $resourceKey $problemKey", "Problema encontrado");
                    }else{
                        /** do nothing - the email was already sent*/
                    }
                } else if($problemValue['status']==false){
                    if($problemValue['notified']==true){
                        
                        /** Problem solved */
                        
                        /** update status file */
                        $statusArray[$resourceTypeKey][$resourceKey]['problems'][$problemKey]['notified'] = false;
                        updateStatusFile($statusArray,$statusFileName);
                        
                        /** send mail */
                        sendMail($statusArray, "Resource Monitor - $resourceKey $problemKey", "Problema solucionado");
                    }
                }
                
            }
            
        }
    }

}

function start(){
    
    /** simulate a unavailable services */
    $testRestOff = false;
    $testCsgridOff = false;
    $testSgaAltxRm1 = false; // sga-lncc-sge-altix-xe_rm1model
    $testSgaSdumCpu = false; // sga-lncc-snpd-sdumont-dockthor_cpu
    $testSgaSdumDck = false; // sga-lncc-snpd-sdumont-dockthor_dockthor
    
    $statusFileName = "status.json";
    
    $sgaList = array();
    array_push($sgaList, "sga-lncc-sge-altix-xe_rm1model");
    //array_push($sgaList, "sga-lncc-snpd-sdumont-dockthor_cpu");
    array_push($sgaList, "sga-lncc-snpd-sdumont-dockthor_dockthor");
    
    /** Create (if necessary) and get status file **/
    $statusArray = createStatusFile($sgaList, $statusFileName);
    // syslog(LOG_INFO|LOG_LOCAL0, "Status file: ".json_encode($statusArray, JSON_PRETTY_PRINT));
    
    /** login **/
    $uuid = null;
    $uuid = login();
    
    /** simulation */
    if($testRestOff == true){
        $uuid = null;
    }
    
    /** check login */
    if($uuid == null){
        syslog(LOG_INFO|LOG_LOCAL0, "Problem found - Failed to login (REST is up?)");        
        $statusArray['general']['rest']['problems']['unavailable']['status']  = true;
        notify($statusArray,$statusFileName);
    }else{
         /** send mail and update status file if necessary */
        $statusArray['general']['rest']['problems']['unavailable']['status']  = false;
        notify($statusArray,$statusFileName);        
        
         /** get resources */
         $resources = rest_get_resources($uuid);
        
         /** add more information */
         $resources = addInfo($resources);
         //syslog(LOG_INFO|LOG_LOCAL0, "CSGrid resouces: ".json_encode($resources, JSON_PRETTY_PRINT));
        
         /** simulation - csgrid off */
         if($testCsgridOff == true){ // force csgrid off
             $resources->code = "99999"; // any value different of 200
         }
         
         /** sinmulation = sga with zero nodes*/
         foreach($resources->elements->element as $key => $value){
             
             if($testSgaAltxRm1){
                 if($value->name == "sga-lncc-sge-altix-xe_rm1model"){
                     $value->nodesCount = 0;
                 }
             }
             
             if($testSgaSdumCpu){
                 if($value->name == "sga-lncc-snpd-sdumont-dockthor_cpu"){
                     $value->nodesCount = 0;
                 }
             }
             
             if($testSgaSdumDck){
                 if($value->name == "sga-lncc-snpd-sdumont-dockthor_dockthor"){
                     $value->nodesCount = 0;
                 }
             }
             
         }
         
         /** check sgas */
         $statusArray = checkProblems($resources, $sgaList, $statusArray);
         syslog(LOG_INFO | LOG_LOCAL0, "CSGrid resouces (checked): " . json_encode($statusArray, JSON_PRETTY_PRINT));        
         
         /** send mail and update status file if necessary */
         notify($statusArray,$statusFileName);
    }


}

start();





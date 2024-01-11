<?php
// require_once ("../../environment/environment-config.php");
//TODO: Edson - Mudar nome da pasta conf para properties
$rest_conf_array = parse_ini_file ( $GLOBALS ['DOCKTHOR_PATH'] . "apps/docking/conf/rest-config.ini" );
$CONFIG_REST_ROOT_URL = $rest_conf_array ['REST_ROOT_URL'];
$CONFIG_REST_SERVICE = $rest_conf_array ['REST_SERVICE'];
$CONFIG_REST_USER = $rest_conf_array ['REST_USER'];
$CONFIG_REST_CERTIFICATE = $rest_conf_array ['REST_CERTIFICATE'];
$CONFIG_REST_PROJECT = $rest_conf_array ['REST_PROJECT'];
$CONFIG_REST_APP_NAME = $rest_conf_array ['REST_APP_NAME'];
$CONFIG_REST_APP_VERSION_DOCKING = $rest_conf_array ['REST_APP_VERSION_DOCKING'];
$CONFIG_REST_APP_VERSION_DOCKING_ALTERNATIVE = $rest_conf_array ['REST_APP_VERSION_DOCKING_ALTERNATIVE'];
$CONFIG_REST_APP_VERSION_VIRTUALSCREENING = $rest_conf_array ['REST_APP_VERSION_VIRTUALSCREENING'];
$CONFIG_REST_APP_TIME_FACTOR = $rest_conf_array ['REST_APP_TIME_FACTOR'];
$CONFIG_REST_APP_VIRTUALSCREENING_MAX_CORES = $rest_conf_array ['REST_APP_VIRTUALSCREENING_MAX_CORES'];
$CONFIG_REST_DEBUG = $rest_conf_array ['REST_DEBUG'];

function rest_debug($label, $handle, $data, $response){
	global $CONFIG_REST_DEBUG;
	if($CONFIG_REST_DEBUG){
		$info = curl_getinfo($handle,  CURLINFO_HEADER_OUT);
		syslog(LOG_DEBUG|LOG_LOCAL0, "
############ debug-rest-detailed-start ($label) #################
#REQUEST
$info
data: ".json_encode($data, JSON_PRETTY_PRINT )."
#RESPONSE
$response
############ debug-rest-detailed-finish #################
		");
	}	
}

function rest_login() {
	
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	global $CONFIG_REST_USER;
	global $CONFIG_REST_CERTIFICATE;
	
	$url = "$CONFIG_REST_ROOT_URL/op/authentication/login-rsa";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'username' => "$CONFIG_REST_USER",
			'file' => new CurlFile("$CONFIG_REST_CERTIFICATE", 'multipart/form-data')
	);
	$headers = array (
			'Accept: application/json' 
	);
	$handle = curl_init ();
	
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt ( $handle, CURLOPT_CONNECTTIMEOUT, 120 );
	curl_setopt ( $handle, CURLOPT_TIMEOUT, 600 );	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, $data );
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);		
	
	$response = curl_exec ( $handle );
	rest_debug("rest_login", $handle, $data, $response);
	
	$obj = json_decode ( $response );	
	
	return $obj->{'uuid'};
}

function rest_logout($uuid) {
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	$url = "$CONFIG_REST_ROOT_URL/op/authentication/logout";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'uuid' => "$uuid" 
	);
	$headers = array (
			'Accept: application/json' 
	);
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt ( $handle, CURLOPT_CONNECTTIMEOUT, 120 );
	curl_setopt ( $handle, CURLOPT_TIMEOUT, 600 );
	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);	
	
	$response = curl_exec ( $handle );		
	rest_debug("rest_logout", $handle, $data, $response);
	
	$obj = json_decode ( $response );
	return $obj->{'code'};
}

function rest_user_info($uuid) {
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	$url = "$CONFIG_REST_ROOT_URL/op/authentication/info";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'uuid' => "$uuid" 
	);
	$headers = array (
			'Accept: application/json' 
	);
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);	
	
	$response = curl_exec ( $handle );
	rest_debug("rest_user_info", $handle, $data, $response);
	
	$obj = json_decode ( $response );
	
	$result = null;
	if(isset($obj->{'code'})){
	    $result = $obj->{'code'};
	}
	
	return $result;	
}

function rest_create_directory($parents, $name, $uuid) {
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	global $CONFIG_REST_PROJECT;
	
	$logout = false;
	if ($uuid == null) {
		$uuid = rest_login ();
		$logout = true;
	}
	$url = "$CONFIG_REST_ROOT_URL/op/file/create";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'uuid' => "$uuid",
			'project' => "$CONFIG_REST_PROJECT",
			'parents' => "$parents",
			'file' => "$name",
			'skipValidation' => "true",
			'directory' => "true" 
	);
	$headers = array (
			'Accept: application/json' 
	);
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);
	
	$response = curl_exec ( $handle );
	rest_debug("rest_create_directory", $handle, $data, $response);
	
	if ($logout) {
		rest_logout ( $uuid );
	}
	$obj = json_decode ( $response );
	
	return $obj->{'code'};
}

function rest_upload($parents, $file, $uuid) {
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	global $CONFIG_REST_PROJECT;
	$rest_logout = false;
	if ($uuid == null) {
		$uuid = rest_login ();
		$rest_logout = true;
	}
	$url = "$CONFIG_REST_ROOT_URL/op/file/upload";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'uuid' => "$uuid",
			'project' => "$CONFIG_REST_PROJECT",
			'parents' => "$parents",
			'file' => new CurlFile("$file", 'multipart/form-data'),
			'skipValidation' => "true",
			'override' => "true" 
	);
	$headers = array (
			'Accept: application/json' 
	);
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, $data );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);	
	
	$response = curl_exec ( $handle );
	rest_debug("rest_upload", $handle, $data, $response);
	
	if ($rest_logout) {
		rest_logout ( $uuid );
	}
	$obj = json_decode ( $response );
	
	return $obj->{'code'};
}

function rest_download_log($job_id, $local_destination, $uuid) {
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	global $CONFIG_REST_PROJECT;
	$rest_logout = false;
	if ($uuid == null) {
		$uuid = rest_login ();
		$rest_logout = true;
	}
	$url = "$CONFIG_REST_ROOT_URL/op/job-monitoring/log";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'uuid' => "$uuid",
			'project' => "$CONFIG_REST_PROJECT",
			'skipValidation' => "true",
			'jobId' => "$job_id" 
	);
	$headers = array (
			'Accept: application/octet-stream' 
	);
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);	
	
	$fp = fopen ( $local_destination, 'w' );
	curl_setopt ( $handle, CURLOPT_FILE, $fp );
	$response = curl_exec ( $handle );
	rest_debug("rest_download_log", $handle, $data, $response);
	
	curl_close ( $handle );
	fclose ( $fp );
	
	if ($rest_logout) {
		rest_logout ( $uuid );
	}
	
	
	return $response;
}

function rest_download($remote_file_parents, $remote_file, $local_destination, $uuid) {
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	global $CONFIG_REST_PROJECT;
	$rest_logout = false;
	if ($uuid == null) {
		$uuid = rest_login ();
		$rest_logout = true;
	}
	if ($remote_file_parents == null) {
		$remote_file_parents = "";
	}
	$url = "$CONFIG_REST_ROOT_URL/op/file/download";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'uuid' => "$uuid",
			'project' => "$CONFIG_REST_PROJECT",
			'parents' => "$remote_file_parents",
			'file' => "$remote_file" 
	);
	$headers = array (
			'Accept: application/octet-stream' 
	);
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);	
	
	$fp = fopen ( $local_destination, 'w' );
	curl_setopt ( $handle, CURLOPT_FILE, $fp );
	
	$response = curl_exec ( $handle );
	rest_debug("rest_download", $handle, $data, $response);
	
	curl_close ( $handle );
	fclose ( $fp );
	
	if ($rest_logout) {
		rest_logout ( $uuid );
	}
	
	return $response;
}

function rest_size($parents, $file, $uuid) {
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	global $CONFIG_REST_PROJECT;
	$rest_logout = false;
	if ($uuid == null) {
		$uuid = rest_login ();
		$rest_logout = true;
	}
	$url = "$CONFIG_REST_ROOT_URL/op/file/find";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'uuid' => "$uuid",
			'project' => "$CONFIG_REST_PROJECT",
			'parents' => "$parents",
			'skipValidation' => "true",
			'file' => "$file" 
	);
	$headers = array (
			'Accept: application/json' 
	);
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);	
	
	$response = curl_exec ( $handle );
	rest_debug("rest_size", $handle, $data, $response);
	
	if ($rest_logout) {
		rest_logout ( $uuid );
	}
	$obj = json_decode ( $response );
	if ($obj->{'code'} == 200) {
		return $obj->{'size'};
	}
	
	return - 1;
}

function rest_exists($parents, $file, $uuid) {
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	global $CONFIG_REST_PROJECT;
	$rest_logout = false;
	if ($uuid == null) {
		$uuid = rest_login ();
		$rest_logout = true;
	}
	if ($parents == null) {
		$parents = "";
	}
	$url = "$CONFIG_REST_ROOT_URL/op/file/exists";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'uuid' => "$uuid",
			'project' => "$CONFIG_REST_PROJECT",
			'parents' => "$parents",
			'skipValidation' => "true",
			'file' => "$file" 
	);
	$headers = array (
			'Accept: application/json' 
	);
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);	
	
	$response = curl_exec ( $handle );
	rest_debug("rest_exists", $handle, $data, $response);
	
	if ($rest_logout) {
		rest_logout ( $uuid );
	}
	$obj = json_decode ( $response );	
	
	return $obj->{'code'};
}

function rest_delete($parents, $file, $uuid) {
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	global $CONFIG_REST_PROJECT;
	$rest_logout = false;
	if ($uuid == null) {
		$uuid = rest_login ();
		$rest_logout = true;
	}
	if ($parents == null) {
		$parents = "";
	}
	$url = "$CONFIG_REST_ROOT_URL/op/file/delete";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'uuid' => "$uuid",
			'project' => "$CONFIG_REST_PROJECT",
			'parents' => "$parents",
			'skipValidation' => "true",
			'file' => "$file" 
	);
	$headers = array (
			'Accept: application/json' 
	);
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);	
	
	$response = curl_exec ( $handle );
	rest_debug("rest_delete", $handle, $data, $response);
	
	if ($rest_logout) {
		rest_logout ( $uuid );
	}
	$obj = json_decode ( $response );
		
	return $obj->{'code'};
}

function rest_run($args, $uuid, $extra_params, $slots, $submissionType) {
    
	// Special case - executed aways
    syslog(LOG_INFO|LOG_LOCAL0, "rest_run ($args, $uuid, $extra_params, $slots, $submissionType)");
    
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	global $CONFIG_REST_PROJECT;
	global $CONFIG_REST_APP_NAME;
	global $CONFIG_REST_APP_VERSION_DOCKING;
	global $CONFIG_REST_APP_VERSION_DOCKING_ALTERNATIVE;
	global $CONFIG_REST_APP_VERSION_VIRTUALSCREENING;
	global $CONFIG_REST_APP_TIME_FACTOR;
	global $CONFIG_REST_APP_VIRTUALSCREENING_MAX_CORES;
	
	$rest_logout = false;
	if ($uuid == null) {
		$uuid = rest_login ();
		$rest_logout = true;
	}
	
	// Feature to decides if jobs with 1 ligand will be sent do sdumont
	if($submissionType=="TRADICIONAL"){
	    $version=$CONFIG_REST_APP_VERSION_DOCKING;
	} else if ($submissionType=="DOCKING_ALTERNATIVE"){
	    $version=$CONFIG_REST_APP_VERSION_DOCKING_ALTERNATIVE;
	} else{
	    syslog(LOG_INFO|LOG_LOCAL0, "REST ADAPTER ERROR - submissionType was not defined (submissionType=$submissionType)");
	}
	
	// Special case - executed aways
	syslog(LOG_INFO|LOG_LOCAL0, "rest_run (version=$version)");
	
	if ($extra_params != "") {
		$timeLimit= $slots / $CONFIG_REST_APP_VIRTUALSCREENING_MAX_CORES;
		
		$timeLimit = intval($timeLimit);
		
		if ($timeLimit < 1) {
			
			$timeLimit=1;
		}
		
		$timeLimit=$timeLimit * $CONFIG_REST_APP_TIME_FACTOR;
		
		$extra_params="$extra_params;sga_requesting_walltime::$timeLimit";
		$version= $CONFIG_REST_APP_VERSION_VIRTUALSCREENING;
	}
	
	$url = "$CONFIG_REST_ROOT_URL/op/job-submission/run";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'uuid' => "$uuid",
			'project' => "$CONFIG_REST_PROJECT",
			'application' => "$CONFIG_REST_APP_NAME",
			//'version' => "$CONFIG_REST_APP_VERSION",
			'version' => "$version",
			'skipValidation' => "true",
			'args' => "$args",
			'extras' => "$extra_params"
	);
	$headers = array (
			'Accept: application/json' 
	);
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt ( $handle, CURLOPT_CONNECTTIMEOUT, 120 );
	curl_setopt ( $handle, CURLOPT_TIMEOUT, 600 );
	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);	
	
	$response = curl_exec ( $handle );
	rest_debug("rest_run", $handle, $data, $response);
	
	if ($rest_logout) {
		rest_logout ( $uuid );
	}
	$obj = json_decode ( $response );
		
	return $obj;
}

function rest_cancel($job_id, $uuid) {
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	global $CONFIG_REST_PROJECT;
	$rest_logout = false;
	if ($uuid == null) {
		$uuid = rest_login ();
		$rest_logout = true;
	}
	$url = "$CONFIG_REST_ROOT_URL/op/job-submission/cancel";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'uuid' => "$uuid",
			'project' => "$CONFIG_REST_PROJECT",
			'skipValidation' => "true",
			'jobId' => "$job_id" 
	);
	$headers = array (
			'Accept: application/json' 
	);
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);	
	
	$response = curl_exec ( $handle );
	rest_debug("rest_cancel", $handle, $data, $response);
	
	if ($rest_logout) {
		rest_logout ( $uuid );
	}
	$obj = json_decode ( $response );
		
	return $obj->{'code'};
}

function rest_status($job_id, $uuid) {
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	global $CONFIG_REST_PROJECT;
	$rest_logout = false;
	if ($uuid == null) {
		$uuid = rest_login ();
		$rest_logout = true;
	}
	$url = "$CONFIG_REST_ROOT_URL/op/job-monitoring/status";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'uuid' => "$uuid",
			'project' => "$CONFIG_REST_PROJECT",
			'skipValidation' => "true",
			'jobId' => "$job_id" 
	);
	$headers = array (
			'Accept: application/json' 
	);
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);	
	
	$response = curl_exec ( $handle );
	rest_debug("rest_status", $handle, $data, $response);
	
	if ($rest_logout) {
		rest_logout ( $uuid );
	}
	$obj = json_decode ( $response );
		
	return $obj;
}

function rest_get($job_id, $uuid) {
	global $CONFIG_REST_SERVICE;
	global $CONFIG_REST_ROOT_URL;
	global $CONFIG_REST_PROJECT;
	$rest_logout = false;
	if ($uuid == null) {
		$uuid = rest_login ();
		$rest_logout = true;
	}
	$url = "$CONFIG_REST_ROOT_URL/op/job-monitoring/get";
	$data = array (
			'service' => "$CONFIG_REST_SERVICE",
			'uuid' => "$uuid",
			'project' => "$CONFIG_REST_PROJECT",
			'skipValidation' => "true",
			'jobId' => "$job_id" 
	);
	$headers = array (
			'Accept: application/json' 
	);
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
	
	curl_setopt ( $handle, CURLOPT_POST, true );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);	
	
	$response = curl_exec ( $handle );
	rest_debug("rest_get", $handle, $data, $response);
	
	if ($rest_logout) {
		rest_logout ( $uuid );
	}
	$obj = json_decode ( $response );
	
	return $obj;
}

function rest_get_resources($uuid) {
    global $CONFIG_REST_ROOT_URL;
	
    $url = "$CONFIG_REST_ROOT_URL/op/resource-monitoring/list";
	
    $data = array (
        'service' => "CSGrid",
        'uuid' => "$uuid",
        'skipValidation' => "true"
    );
    $headers = array (
        'Accept: application/json'
    );
    $handle = curl_init ();
    curl_setopt ( $handle, CURLOPT_URL, $url );
    curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
    curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
    curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
    curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
    
    curl_setopt ( $handle, CURLOPT_POST, true );
    curl_setopt ( $handle, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
	
	curl_setopt ( $handle, CURLINFO_HEADER_OUT, true);	
	
    $response = curl_exec ( $handle );
    rest_debug("rest_get_resources", $handle, $data, $response);
	
	$obj = json_decode($response);
    	
	return $obj;
}

?>

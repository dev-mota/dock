<?php
$status = "UNKNOW";

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$job_id = $request->jobId;

if ($job_id != null ) {
	if (file_exists ( "daemon/cancelled/$job_id" )) {
		$status = "CANCELLED";
	}
	
	if (file_exists ( "daemon/checking/$job_id" )) {
		$status = "CHECKING";
	}
	
	if (file_exists ( "daemon/error/$job_id" )) {
		$status = "ERROR";
	}
	
	if (file_exists ( "daemon/pending/$job_id" )) {
		$status = "PENDING";
	}
	
	if (file_exists ( "daemon/running/$job_id" )) {
		$status = "RUNNING";
	}

	if (file_exists ( "daemon/paused/$job_id" )) {
                $status = "PAUSED";
        }
	
	if (file_exists ( "daemon/success/$job_id" )) {
		$status = "SUCCESS";
	}
}
echo $status;
?>

<?php
require_once '../environment/mmffligand-environment.php';
include '../lib/utils/file-validator.php';
include "mmffligand.php";

session_start ();
$session_id = session_id ();

$postdata = file_get_contents ( "php://input" );
$request = json_decode ( $postdata );
$action = $request->action;
$file_name = $request->fileName;

$response = array ();
if(!FileValidator::isEmpty("../session-files/" . $session_id . "/LIGAND/$file_name")) {
	$mmffligand = new MMFFligand ( $session_id );
	switch ($action) {
		case 'PREPARE' :
			$prepare_result = $mmffligand->prepare ( $file_name );
			if ($prepare_result['SUCCESS']) {
				$response['operationStatus'] = 'SUCCESS';
			} else {
				$response['operationStatus'] = 'ERROR';
				$response['errorMessage'] = 'Invalid File Structure';
			}
			break;
	}
} else {
	$response['operationStatus'] = 'ERROR';
	$response['errorMessage'] = $validation_result['ERROR_MESSAGE'];
}
echo json_encode($response);
?>
<?php
require_once '../environment/pdbthorbox-environment.php';
include '../lib/utils/file-validator.php';
include "pdbthorbox.php";

session_start ();
$session_id = session_id ();

$postdata = file_get_contents ( "php://input" );
$request = json_decode ( $postdata );
$action = $request->action;
$file_name = $request->fileName;

$validation_result = FileValidator::validatePDB("../session-files/" . $session_id . "/PROTEIN/$file_name");
$response = array ();
if($validation_result['VALID'] == true) {
	$mmffligand = new Pdbthorbox ( $session_id );
	switch ($action) {
		case 'PREPARE' :
			$prepare_result = $mmffligand->prepare ( $file_name );
			if ($prepare_result['SUCCESS']) {
				$response['operationStatus'] = 'SUCCESS';
				$response['chains'] = $prepare_result['CHAINS'];
			} else {
				$response['operationStatus'] = 'ERROR';
				$response['errorMessage'] = 'Invalid File Structure';
			}
			break;
		case 'REPREPARE' :
			$reprepare_result = $mmffligand->reprepare ( $file_name );
			if ($reprepare_result['SUCCESS']) {
				$response['operationStatus'] = 'SUCCESS';
				$response['chains'] = $reprepare_result['CHAINS'];
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
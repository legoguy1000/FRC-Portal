<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken(true,true);

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['endpoint']) || $formData['endpoint'] == '' || !isset($formData['key']) || $formData['key'] == '' || !isset($formData['authSecret']) || $formData['authSecret'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.')));
}


$userId = $authToken['data']['user_id'];

$id = uniqid();
$query = 'INSERT INTO notification_endpoints (`note_id`, `user_id`, `endpoint`, `auth_secret`, `public_key`) VALUES 
								('.db_quote($id).',
								 '.db_quote($userId).',
								 '.db_quote($formData['endpoint']).',
								 '.db_quote($formData['authSecret']).',
								 '.db_quote($formData['key']).')';
$result = db_query($query);
if($result) {
	$msg = 'Device Subscription Added';
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));
} else {
	$msg = 'Somethign went wrong';
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
}




?>
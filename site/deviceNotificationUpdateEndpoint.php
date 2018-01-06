<?php
include('includes.php');

$authToken = checkToken(true,true);

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['endpoint']) || $formData['endpoint'] == '' || !isset($formData['key']) || $formData['key'] == '' || !isset($formData['authSecret']) || $formData['authSecret'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.')));
}
$userId = $authToken['data']['user_id'];
$query = 'select * from notification_endpoints WHERE endpoint='.db_quote($formData['endpoint']);
$result = db_select_single($query);
if($result) {
	if($userId != $result['user_id']) {
		$query = 'UPDATE notification_endpoints SET user_id='.db_quote($userId).', auth_secret='.db_quote($formData['authSecret']).', public_key='.db_quote($formData['key']).' WHERE endpoint='.db_quote($formData['endpoint']);
		$result = db_query($query);
		if($result) {
			$msg = 'Device Subscription Endpoint Updated';
			die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));
		} else {
			$msg = 'Something went wrong';
			die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
		}
	}
	else {
		$msg = 'No Change';
		die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
	}
} else {
	$id = uniqid();
	$query = 'INSERT INTO notification_endpoints (`note_id`, `user_id`, `endpoint`, `auth_secret`, `public_key`) VALUES 
									('.db_quote($id).',
									 '.db_quote($userId).',
									 '.db_quote($formData['endpoint']).',
									 '.db_quote($formData['authSecret']).',
									 '.db_quote($formData['key']).')';
	$result = db_query($query);
	if($result) {
			$msg = 'Device Subscription Endpoint Added';
		die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));
	} else {
		$msg = 'Something went wrong';
		die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
	}
}



?>
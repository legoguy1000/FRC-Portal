<?php
include('includes.php');

$authToken = checkToken(true,true);

$json = file_get_contents('php://input');
$formData = json_decode($json,true);

if(!isset($formData['note_id']) || $formData['note_id'] == '' || !isset($formData['label']) || $formData['label'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.')));
}
$userId = $authToken['data']['user_id'];
$query = 'UPDATE notification_endpoints SET label='.db_quote($formData['label']).' WHERE note_id='.db_quote($formData['note_id']).' AND user_id='.db_quote($userId);
$result = db_query($query);
if($result) {
	$msg = 'Device Subscription Endpoint Updated';
  $endpoints = getNotifiationEndpointsByUser($userId);
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg, 'endpoints'=>$endpoints)));
} else {
	$msg = 'Something went wrong';
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
}


?>

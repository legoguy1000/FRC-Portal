<?php
include('includes.php');

$authToken = checkToken(true,true);

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['endpoint']) || $formData['endpoint'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.')));
}


$userId = $authToken['data']['user_id'];

$query = 'DELETE FROM notification_endpoints WHERE endpoint='.db_quote($formData['endpoint']).' AND user_id='.db_quote($userId);
$result = db_query($query);
if($result) {
	$msg = 'Device Subscription Removed';
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));
} else {
	$msg = 'Somethign went wrong';
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
}


?>
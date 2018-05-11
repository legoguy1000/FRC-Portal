<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken(true);
//die(json_encode($authToken));
$json = file_get_contents('php://input');
$formData = json_decode($json,true);

$userId = $authToken['data']['user_id'];

if(!isset($formData['method']) || $formData['method'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Notification method is required')));
}
if(!isset($formData['type']) || $formData['type'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Notification type is required')));
}
if(!isset($formData['value'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Value is required')));
}
if($formData['value'] == true) {
	$pref_id = uniqid();
	$query = 'INSERT INTO notification_preferences (pref_id,user_id,method,type) VALUES ('.db_quote($pref_id).','.db_quote($userId).','.db_quote($formData['method']).','.db_quote($formData['type']).')';
	$result = db_query($query);
	if($result) {

	} else {
		die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Something went wrong')));
	}
}
else if($formData['value'] == false) {
	$query = 'DELETE FROM notification_preferences WHERE user_id = '.db_quote($userId).' AND method = '.db_quote($formData['method']).' AND type = '.db_quote($formData['type']);
	$result = db_query($query);
	if($result) {

	} else {
		die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Something went wrong')));
	}
}

?>

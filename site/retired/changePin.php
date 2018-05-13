<?php
include('./includes.php');

$authToken = checkToken(true,true);
$userId = $authToken['data']['user_id'];

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['pin']) || $formData['pin'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'PIN cannot be blank!')));
}
if(!is_numeric($formData['pin'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'PIN must only be numbers 0-9!')));
}
if(strlen($formData['pin']) < 4 || strlen($formData['pin']) > 8) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'PIN must be between 4 to 8 numbers!!')));
}
$currentPIN = null;
$query = 'SELECT user_id, signin_pin FROM users  WHERE user_id='.db_quote($userId);
$result = db_select_single($query);
if(!is_null($result)) {
	$currentPIN = $result['signin_pin'];
	if($currentPIN != hash('SHA256', $formData['pin'])) {
		$query = 'UPDATE users SET signin_pin='.db_quote(hash('SHA256', $formData['pin'])).' WHERE user_id='.db_quote($userId);
		//die($query);
		$result = db_query($query);
		if($result) {
			die(json_encode(array('status'=>true, 'msg'=>'PIN has been changed')));
		} else {
			die(json_encode(array('status'=>false, 'msg'=>'Something went wrong')));
		}
	} else {
		die(json_encode(array('status'=>false, 'msg'=>'PIN must be changed to a different number')));
	}	
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong')));
}




?>
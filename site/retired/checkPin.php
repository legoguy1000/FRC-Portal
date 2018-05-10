<?php
include('./includes.php');

$authToken = checkToken(true,true);
$userId = $authToken['data']['user_id'];

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

if(!isset($formData['pin']) || $formData['pin'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'PIN number cannot be blank!')));
}

$where = ' WHERE signin_pin = '.db_quote(hash('sha256',$formData['pin'])).' AND user_id ='.db_quote($userId);
$query = userQuery($sel='',$joins='', $where, $order = '');
$result = db_select_single($query);
if(!is_null($result)) {
	die(json_encode(array('status'=>true, 'msg'=>'PIN is correct')));
}  else {
	die(json_encode(array('status'=>false, 'msg'=>'PIN is incorrect')));
}



?>
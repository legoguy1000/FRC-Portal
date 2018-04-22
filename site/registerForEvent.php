<?php
include('includes.php');

$authToken = checkToken(true,true);
$userId = $authToken['data']['user_id'];

$json = file_get_contents('php://input');
$formData = json_decode($json,true);

if(!isset($formData['event_id']) || $formData['event_id'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.')));
}
$query = 'SELECT * FROM event_requirements WHERE event_id='.db_quote($formData['event_id']).' AND user_id='.db_quote($userId);
$result = db_select_single($query);
if(!is_null($result)) {
	$query = 'UPDATE event_requirements SET registration="1" WHERE event_id='.db_quote($formData['event_id']).' AND user_id='.db_quote($userId);
	$result1 = db_query($query);
} else {
	$ereq_id = uniqid();
	$query = 'INSERT INTO event_requirements (ereq_id, event_id, user_id, registration) VALUES ('.db_quote($ereq_id).', '.db_quote($formData['event_id']).', '.db_quote($userId).', "1")';
	$result1 = db_query($query);
}
if($result1) {
	$msg = 'Registered';
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));
} else {
	$msg = 'Something went wrong';
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
}

?>
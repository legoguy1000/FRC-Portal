<?php
include('./includes.php');

$authToken = checkToken(true,true);
$user_id = $authToken['data']['user_id'];

$json = file_get_contents('php://input');
$formData = json_decode($json,true);

if(!isset($formData['start_time']) || $formData['start_time'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Start Time cannot be blank')));
}
if(!isset($formData['end_time']) || $formData['end_time'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'End Time cannot be blank')));
}
if(!isset($formData['comment']) || $formData['comment'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Comment cannot be blank')));
}

$request_id = uniqid();
$request_date = date('Y-m-d H:i:s');
$query = 'INSERT INTO missing_hours_requests (request_id, user_id, time_in, time_out, comment, request_date) VALUES
				('.db_quote($request_id).', '.db_quote($user_id).', '.db_quote($formData['start_time']).', '.db_quote($formData['end_time']).', '.db_quote($formData['comment']).', '.db_quote($request_date).')';
$result = db_query($query);


?>

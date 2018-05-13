<?php
include('includes.php');

$authToken = checkToken(true,true);

$json = file_get_contents('php://input');
$formData = json_decode($json,true);

$user_id = $authToken['data']['user_id'];
checkAdmin($user_id, $die = true);


if(!isset($formData['event_id']) || $formData['event_id'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Event ID is required')));
}
if(!isset($formData['type']) || $formData['type'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Event Type is required')));
}
if(!isset($formData['pocInfo'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'POC is required')));
}

$query = 'UPDATE events SET type='.db_quote($formData['type']).', poc='.db_quote($formData['pocInfo']['user_id']).' WHERE event_id='.db_quote($formData['event_id']);
$result = db_query($query);
if($result) {
	$event = getEvent($formData['event_id'], $reqs = false);
	$msg = 'Event updated.';
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg, 'data'=>$event)));
} else {
	$msg = 'Something went wrong';
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
}
?>

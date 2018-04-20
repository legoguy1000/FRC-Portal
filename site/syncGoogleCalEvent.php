<?php
include('includes.php');

$authToken = checkToken(true,true);

$json = file_get_contents('php://input');
$formData = json_decode($json,true);

$user_id = $authToken['data']['user_id'];
checkAdmin($user_id, $die = true);


if(!isset($formData['event_id']) || $formData['event_id'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.')));
}
$sync = false;
$event_id = $formData['event_id'];
$query = 'SELECT * FROM events WHERE event_id='.db_quote($event_id);
$result = db_select_single($query);
if(!is_null($result)) {
	$cal_id = $result['google_cal_id'];
	$sync = syncGoogleCalendarEvent($cal_id, $event_id);
	if($sync) {
		$event = getEvent($event_id, $reqs = false);
		$msg = 'Event synced.';
		die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg, 'data'=>$event)));
	} else {
		$msg = 'Something went wrong';
		die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
	}
} else {
 $msg = 'No event found';
 die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
}


?>

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
if(!isset($formData['rooms']) || !is_array($formData['rooms']) || empty($formData['rooms'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Room array is required')));
}

$query = 'SELECT * FROM event_rooms WHERE event_id='.db_quote($formData['event_id']);
$result = db_select($query);

db_begin_transaction();

foreach($result as $re) {
	$room_id = $re['room_id'];
	$roomArr = $formData['rooms'][$room_id];
	$userArr = array();
	foreach($roomArr as $room) {
		$userArr[] = db_quote($room['user_id']);
	}
	if(!empty($userArr) && count($userArr) <= 4) {
		$userStr = implode(', '.$userArr);
		$query = 'UPDATE event_requirements SET room_id='.db_quote($room_id).' WHERE event_id='.db_quote($formData['event_id']).' AND user_id IN ('.$userStr.')';
		$result = db_query($query);
	}
}

//Not Assigned a room
$roomArr = $formData['rooms']['non_select'];
$userArr = array();
foreach($roomArr as $room) {
	$userArr[] = db_quote($room['user_id']);
}
if(!empty($userArr) && count($userArr) <= 4) {
	$userStr = implode(', '.$userArr);
	$query = 'UPDATE event_requirements SET room_id=NULL WHERE event_id='.db_quote($formData['event_id']).' AND user_id IN ('.$userStr.')';
	$result = db_query($query);
}

$result = db_commit();
if($result) {
	$msg = 'Event room list updated.';
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));
} else {
	$msg = 'Something went wrong';
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
}
?>

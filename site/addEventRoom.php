<?php
include('./includes.php');

$authToken = checkToken(true,true);
$user_id = $authToken['data']['user_id'];
checkAdmin($user_id, $die = true);

$json = file_get_contents('php://input');
$formData = json_decode($json,true);


if(!isset($formData['event_id']) || $formData['event_id'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Event ID cannot be blank!')));
}
if(!isset($formData['user_type']) || $formData['user_type'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'User type cannot be blank!')));
}
if(!isset($formData['gender']) || $formData['gender'] == '' && $formData['user_type'] != 'Mentor') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Gender cannot be blank!')));
}

$room_id = uniqid();
$query = 'INSERT INTO event_roooms (room_id, event_id, user_type, gender) VALUES
		('.db_quote($room_id).',
		 '.db_quote($formData['event_id']).',
		 '.db_quote($formData['user_type']).',
		 '.db_quote($formData['gender']).')';
die($query);
$result = db_query($query);
if($result) {
	$rooms = getEventRoomList($formData['event_id']);
	die(json_encode(array('status'=>true, 'msg'=>'Room created', 'data'=>$rooms)));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong')));
}



?>

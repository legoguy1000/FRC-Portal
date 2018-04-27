<?php
include('./includes.php');

$authToken = checkToken(true,true);
$user_id = $authToken['data']['user_id'];
checkAdmin($user_id, $die = true);

$json = file_get_contents('php://input');
$formData = json_decode($json,true);


if(!isset($formData['name']) || $formData['name'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Name cannot be blank!')));
}
if(!isset($formData['type']) || $formData['type'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Event type cannot be blank!')));
}
if(!isset($formData['event_start']) || $formData['event_start'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Start Date cannot be blank!')));
}
if(!isset($formData['event_end']) || $formData['event_end'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'End Date cannot be blank!')));
}
if(strtotime($formData['event_start']) >= strtotime($formData['event_end'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Start Date must be before End Date.')));
}
if(!isset($formData['google_cal_id']) || $formData['google_cal_id'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Google calendar ID')));
}
$query = 'SELECT * FROM events WHERE google_cal_id='.db_quote($formData['google_cal_id']);
$result = db_select_single($query);
if(!is_null($result)) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Event already exists')));
}

$event_id = uniqid();
$query = 'INSERT INTO events (event_id, google_cal_id, name, type, event_start, event_end, details, location, payment_required, permission_slip_required, food_required, room_required, drivers_required) VALUES
		('.db_quote($event_id).',
		 '.db_quote($formData['google_cal_id']).',
		 '.db_quote($formData['name']).',
		 '.db_quote($formData['type']).',
		 '.db_quote($formData['event_start']).',
		 '.db_quote($formData['event_end']).',
		 '.db_quote($formData['details']).',
		 '.db_quote($formData['location']).',
		 '.db_quote(isset($formData['requirements']['payment']) && $formData['requirements']['payment'] ? true:false).',
		 '.db_quote(isset($formData['requirements']['permission_slip']) && $formData['requirements']['permission_slip'] ? true:false).',
		 '.db_quote(isset($formData['requirements']['food']) && $formData['requirements']['food'] ? true:false).',
		 '.db_quote(isset($formData['requirements']['room']) && $formData['requirements']['room'] ? true:false).',
		 '.db_quote(isset($formData['requirements']['drivers']) && $formData['requirements']['drivers'] ? true:false).')';
//die($query);
$result = db_query($query);
if($result) {
	$events = getAllEventsFilter();
	die(json_encode(array('status'=>true, 'msg'=>$formData['name'].' created', 'data'=>$events)));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong')));
}



?>

<?php
include('includes.php');

$authToken = checkToken(false,false);
$loggedInUser = $authToken['data']['user_id'];

$json = file_get_contents('php://input');
$formData = json_decode($json,true);

if(!isset($formData['event_id']) || $formData['event_id'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.')));
}
if(!is_bool($formData['registration'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request, no registration option.')));
}
$userId = $loggedInUser;
if(isset($formData['user_id']) && checkAdmin($loggedInUser, $die = false)) {
	$userId = $formData['user_id'];
}

$registrationBool = (bool) $formData['registration'];
$eventInfo = getEvent($formData['event_id'], $reqs = false);

if($registrationBool) {
	if(time() > $eventInfo['event_start_unix']) {
		die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Registration is closed.  Event has already started.')));
	} elseif((bool) $eventInfo['registration_date_unix'] != false && (time() > $eventInfo['registration_date_unix'])) {
		die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Registration is closed.  Registration deadline was '.date('F j, Y g:m A',$eventInfo['registration_date_unix']).'.')));
	}

	$query = 'SELECT * FROM event_requirements WHERE event_id='.db_quote($formData['event_id']).' AND user_id='.db_quote($userId);
	$eventRegExist = db_select_single($query);

	$query = 'SELECT * FROM event_cars WHERE event_id='.db_quote($formData['event_id']).' AND user_id='.db_quote($userId);
	$eventCarExist = db_select_single($query);

	db_begin_transaction();
	if(!is_null($eventRegExist)) {
		$query = 'UPDATE event_requirements SET registration="1", comments='.db_quote($formData['comments']).' WHERE event_id='.db_quote($formData['event_id']).' AND user_id='.db_quote($userId);
		db_query($query);
	} else {
		$ereq_id = uniqid();
		$query = 'INSERT INTO event_requirements (ereq_id, event_id, user_id, registration, comments) VALUES ('.db_quote($ereq_id).', '.db_quote($formData['event_id']).', '.db_quote($userId).', "1", '.db_quote($formData['comments']).')';
		db_query($query);
	}
	$user_type = $authToken['data']['user_type'];
	$can_drive = (bool) $formData['can_drive'];
	$drivers_req = (bool) $eventInfo['drivers_required'];
	if($user_type == 'Mentor' && $can_drive && $drivers_req && is_null($eventCarExist)) {
		$car_id = uniqid();
		$query = 'INSERT INTO event_cars (car_id, event_id, user_id, car_space) VALUES ('.db_quote($car_id).', '.db_quote($formData['event_id']).', '.db_quote($userId).', '.db_quote($formData['car_space']).')';
		db_query($query);
		$query = 'UPDATE event_requirements SET car_id='.db_quote($car_id).', can_drive="1" WHERE event_id='.db_quote($formData['event_id']).' AND user_id='.db_quote($userId);
		db_query($query);
	} else {
		$query = 'DELETE FROM event_cars WHERE event_id='.db_quote($formData['event_id']).' AND user_id='.db_quote($userId);
		db_query($query);
	}
	$result = db_commit();
	if($result) {
		$event = userEventInfo($userId, $year = null, $formData['event_id'], $return=array());
		$msg = 'Registered';
		die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg, 'data'=>$event)));
	} else {
		$msg = 'Something went wrong';
		die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
	}
} else {
	$query = 'DELETE FROM event_requirements WHERE event_id='.db_quote($formData['event_id']).' AND user_id='.db_quote($userId);
	$result = db_query($query);
	if($result) {
		$event = userEventInfo($userId, $year = null, $formData['event_id'], $return=array());
		$msg = 'Unregistered';
		die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg, 'data'=>$event)));
	} else {
		$msg = 'Something went wrong';
		die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
	}
}



?>

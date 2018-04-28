<?php
include('includes.php');

$authToken = checkToken(true,true);
$userId = $authToken['data']['user_id'];

$json = file_get_contents('php://input');
$formData = json_decode($json,true);

if(!isset($formData['event_id']) || $formData['event_id'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.')));
}
if(!is_null($formData['registration']) || !is_bool($formData['registration'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request, no registration option.')));
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
		$user_type = $authToken['data']['user_type'];
		$can_drive = (bool) $formData['can_drive'];
		if($user_type == 'Mentor' && $can_drive) {
			$query = 'SELECT * FROM event_cars WHERE event_id='.db_quote($formData['event_id']).' AND user_id='.db_quote($userId);
			$result = db_select_single($query);
			if(is_null($result)) {
				$car_id = uniqid();
				$query = 'INSERT INTO event_cars (car_id, event_id, user_id, car_space) VALUES ('.db_quote($car_id).', '.db_quote($formData['event_id']).', '.db_quote($userId).', '.db_quote($formData['car_space']).')';
				$result2 = db_query($query);
				if($result2) {
					$query = 'UPDATE event_requirements SET car_id='.db_quote($car_id).', can_drive="1" WHERE event_id='.db_quote($formData['event_id']).' AND user_id='.db_quote($userId);
					$result3= db_query($query);
				} else {
					$msg = 'Something went wrong';
					die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
				}
			} else {
				$msg = 'Something went wrong';
				die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
			}
		} else {
			$query = 'DELETE FROM event_cars WHERE event_id='.db_quote($formData['event_id']).' AND user_id='.db_quote($userId);
			$result2= db_query($query);
		}
		$msg = 'Registered';
		die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));
	} else {
		$msg = 'Something went wrong';
		die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
	}
} else {
	$query = 'DELETE FROM event_requirements WHERE event_id='.db_quote($formData['event_id']).' AND user_id='.db_quote($userId);
	$result = db_query($query);
	if($result) {
		$msg = 'Unregistered';
		die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));
	} else {
		$msg = 'Something went wrong';
		die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
	}
}



?>

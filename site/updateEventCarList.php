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
if(!isset($formData['cars']) || !is_array($formData['cars']) || empty($formData['cars'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Car array is required')));
}

$query = 'SELECT * FROM event_cars WHERE event_id='.db_quote($formData['event_id']);
$result = db_select($query);

db_begin_transaction();

foreach($result as $re) {
	$car_id = $re['car_id'];
	$carArr = $formData['cars'][$car_id];
	$userArr = array();
	foreach($carArr as $car) {
		$userArr[] = db_quote($car['user_id']);
	}
	if(!empty($userArr) && count($userArr) <= $re['car_space']) {
		$userStr = implode(', '.$userArr);
		$query = 'UPDATE event_requirements SET car_id='.db_quote($car_id).' WHERE event_id='.db_quote($formData['event_id']).' AND user_id IN ('.$userStr.')';
		$result = db_query($query);
	}
}

//Not Assigned a car
$carArr = $formData['cars']['non_select'];
$userArr = array();
foreach($carArr as $car) {
	$userArr[] = db_quote($car['user_id']);
}
if(!empty($userArr) && count($userArr) <= $re['car_space']) {
	$userStr = implode(', '.$userArr);
	$query = 'UPDATE event_requirements SET car_id=NULL WHERE event_id='.db_quote($formData['event_id']).' AND user_id IN ('.$userStr.')';
	$result = db_query($query);
}

$result = db_commit();
if($result) {
	$msg = 'Event car list updated.';
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));
} else {
	$msg = 'Something went wrong';
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
}
?>

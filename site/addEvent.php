<?php
include('./includes.php');

$authToken = checkToken(true,true);
$user_id = $authToken['data']['user_id'];
checkAdmin($user_id, $die = true);

$json = file_get_contents('php://input');
$formData = json_decode($json,true);


//Season can be blank
if(!isset($formData['season_id']) || $formData['season_id'] == '') {
	$season = 'NULL';
} else {
	$season= db_quote($formData['season_id']);
	$query = 'SELECT * FROM seasons WHERE season_id='.db_quote($formData['season_id']);
	$result = db_select_single($query);
	if(!is_null($result)) {
		if(!($formData['event_start'] >= $result['start_date'] && $formData['event_end'] <= $result['end_date'])) {
			die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Event dates are not within season.')));
		}
	} else {
		die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Season selection is incorrect.')));
	}
}
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
if(!(strtotime($formData['event_start']) >= $formData['event_end'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Start Date must be before End Date.')));
}
$event_id = uniqid();
$query = 'INSERT INTO events (event_id, season_id, name, type, event_start, event_end, exempt_hours) VALUES
		('.db_quote($event_id).', '.$season.', '.db_quote($formData['name']).', '.db_quote($formData['type']).','.db_quote($formData['event_start']).', '.db_quote($formData['event_end']).', '.db_quote($formData['exempt_hours']).')';
//die($query);
$result = db_query($query);
if($result) {
	$events = getAllEventsFilter();
	die(json_encode(array('status'=>true, 'msg'=>$formData['name'].' created', 'data'=>$events)));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong')));
}



?>

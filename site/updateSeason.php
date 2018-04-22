<?php
include('./includes.php');

$authToken = checkToken(true,true);
$user_id = $authToken['data']['user_id'];
checkAdmin($user_id, $die = true);

$json = file_get_contents('php://input');
$formData = json_decode($json,true);


if(!isset($formData['season_id']) || $formData['season_id'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Season ID is required')));
}
if(!isset($formData['year']) || $formData['year'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Year is required')));
}
if(!isset($formData['name'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Game Name is required')));
}
if(!isset($formData['start_date'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Start Date is required')));
}
if(!isset($formData['end_date'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'End Date is required')));
}
if(!isset($formData['bag_day'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Bag Date is required')));
}
if(!isset($formData['hour_requirement'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Hour Requirement is required')));
}

$query = 'UPDATE seasons SET  year='.db_quote($formData['year']).',
							game_name='.db_quote($formData['game_name']).',
							game_logo='.db_quote($formData['game_logo']).',
							start_date='.db_quote($formData['start_date']).',
							bag_day='.db_quote($formData['bag_day']).',
							end_date='.db_quote($formData['end_date']).',
							hour_requirement='.db_quote($formData['hour_requirement']).',
							join_spreadsheet='.db_quote($formData['join_spreadsheet']).'
							WHERE season_id = '.db_quote($formData['season_id']);
$result = db_query($query);
if($result) {
	$season = getSeason($formData['season_id'], $reqs = false)
	$msg = 'Season updated.';
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg, 'data'=>$season)));
} else {
	$msg = 'Something went wrong';
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
}
?>

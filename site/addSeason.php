<?php
include('./includes.php');

$authToken = checkToken(true,true);
$user_id = $authToken['data']['user_id'];
checkAdmin($user_id, $die = true);

$json = file_get_contents('php://input');
$formData = json_decode($json,true);


if(!isset($formData['year']) || $formData['year'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Year cannot be blank!')));
}
if(!isset($formData['game_name']) || $formData['game_name'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Name cannot be blank!')));
}
if(!isset($formData['start_date']) || $formData['start_date'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Start Date cannot be blank!')));
}
if(!isset($formData['bag_day']) || $formData['bag_day'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Bag Date cannot be blank!')));
}
if(!isset($formData['end_date']) || $formData['end_date'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'End Date cannot be blank!')));
}

$query = 'SELECT * FROM seasons WHERE year='.db_quote($formData['year']);
$result = db_select_single($query);
if(!is_null($result)) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Season for '.$formData['year'].' already exists')));
} else {
	$spreadsheetId = getSeasonMembershipForm($year);
	$spreadsheetId = $spreadsheetId==false ? '':$spreadsheetId;
	$seasons_id = uniqid();

	$start_date = new DateTime($formData['start_date']);
	$bag_day = new DateTime($formData['bag_day']);
	$end_date = new DateTime($formData['end_date']);

	$query = 'INSERT INTO seasons (season_id, year, game_name, game_logo, start_date, bag_day, end_date, join_spreadsheet) VALUES
			('.db_quote($seasons_id).', '.db_quote($formData['year']).', '.db_quote($formData['game_name']).', '.db_quote($formData['game_logo']).', '.db_quote($start_date->format('Y-m-d')).', '.db_quote($bag_day->format('Y-m-d')." 23:59:59").', '.db_quote($end_date->format('Y-m-d')." 23:59:59").','.db_quote($spreadsheetId).')';
	//die($query);
	$result = db_query($query);
	if($result) {
		$seasons = getAllSeasonsFilter();
		die(json_encode(array('status'=>true, 'msg'=>$formData['year'].' season created', 'data'=>$seasons)));
	} else {
		die(json_encode(array('status'=>false, 'msg'=>'Something went wrong')));
	}
}



?>

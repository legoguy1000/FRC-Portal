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
if(!isset($formData['game_logo']) || $formData['game_logo'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Game Logo URL cannot be blank!')));
}
$query = 'SELECT * FROM seasons WHERE year='.db_quote($formData['year']);
$result = db_select_single($query);
if(!is_null($result)) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Season for '.$formData['year'].' already exists')));
} else {
	$spreadsheetId = '';
	$client = new Google_Client();
	$client->setAuthConfigFile('./includes/secured/team-2363-portal-0c12aca54f1c.json');
	$client->setScopes(['https://www.googleapis.com/auth/drive.readonly']);
	$service = new Google_Service_Drive($client);
	$parameters = array(
		'corpora' => 'teamDrive',
		'q' => 'name contains "'.$formData['year'].'" and name contains "Membership" and name contains "(Responses)" and mimeType = "application/vnd.google-apps.spreadsheet"',
		'includeTeamDriveItems' => 'true',
		'supportsTeamDrives' => 'true',
		'teamDriveId' => '0AI0WovuxnF1zUk9PVA',
		'pageSize' => '1'
	);
	$files = $service->files->listFiles($parameters);
	$result = $files->getFiles();
	if(count($result) > 0) {
		$spreadsheetId = $result[0]['id'];
	}

	$seasons_id = uniqid();
	$query = 'INSERT INTO seasons (season_id, year, game_name, game_logo, start_date, bag_day, end_date, join_spreadsheet) VALUES
			('.db_quote($seasons_id).', '.db_quote($formData['year']).', '.db_quote($formData['game_name']).', '.db_quote($formData['game_logo']).', '.db_quote($formData['start_date']).', '.db_quote($formData['bag_day']).', '.db_quote($formData['end_date']).','.db_quote($spreadsheetId).')';
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

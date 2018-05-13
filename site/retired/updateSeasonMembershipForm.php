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

$spreadsheetId = getSeasonMembershipForm($formData['year']);
if($spreadsheetId != false) {
	$query = 'UPDATE seasons SET join_spreadsheet = '.db_quote($spreadsheetId).' WHERE year = '.db_quote($formData['year']);
	$result = db_query($query);
	if($result) {
		$data = array(
			'join_spreadsheet' => $spreadsheetId
		);
		die(json_encode(array('status'=>true, 'msg'=>'Form added', 'data'=>$data)));
	} else {
		die(json_encode(array('status'=>false, 'msg'=>'Something went wrong')));
	}
} else {
		die(json_encode(array('status'=>false, 'msg'=>'No form found')));
}





?>

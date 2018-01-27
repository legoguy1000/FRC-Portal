<?php
include('./includes.php');

$token = $_POST['token'];
$user_id = $_POST['user_id'];
$user_name = $_POST['user_name'];
$email = $_POST['text'];

$where = ' WHERE slack_id = '.db_quote($user_id).' OR slack_id = '.db_quote($user_name).($email != '' ? 'OR email = '.db_quote($email).' OR team_email = '.db_quote($email) : '');
$query = userQuery($sel='',$joins='', $where, $order = '');
//die($query);
$result = db_select_single($query);
if(!is_null($result)) {
	//die('yes');
	$userInfo = userSeasonInfo($user_id = $result['user_id'], $year = date('Y'));
	die($userInfo[0]['total']);
}  else {
	die('I don\'t know who you are.  Please include send me your email so I can ID you.');
}



?>

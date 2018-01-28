<?php
include('./includes.php');

$token = $_POST['token'];
$user_id = $_POST['user_id'];
$user_name = $_POST['user_name'];

$where = ' WHERE slack_id = '.db_quote($user_id);
$query = userQuery($sel='',$joins='', $where, $order = '');
//die($query);
$result = db_select_single($query);
if(!is_null($result)) {
	//die('yes');
	$userInfo = userSeasonInfo($user_id = $result['user_id'], $year = date('Y'));
	die($userInfo[0]['total']);
}  else {
	die('I don\'t know who you are.  Please use the /registerPortal command to register Slack user ID.');
}



?>

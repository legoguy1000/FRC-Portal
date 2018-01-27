<?php
include('./includes.php');

$token = $_POST['token'];
$user_id = $_POST['user_id'];
$user_name = $_POST['user_name'];
die($user_name);
$where = ' WHERE slack_id = '.db_quote($user_id).' OR slack_id = '.db_quote($user_name);
$query = userQuery($sel='',$joins='', $where, $order = '');
$result = db_select_single($query);
if(!is_null($result)) {
	die('yes');
	$userInfo = userSeasonInfo($user_id = $result['user_id'], $year = date('Y'));
	die($userInfo['total']);
}  else {
	die('no');
}



?>

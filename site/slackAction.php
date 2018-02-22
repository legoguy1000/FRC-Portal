<?php
include('./includes.php');

$data = array();
parse_str(file_get_contents("php://input"), $data);
$json = urldecode($data['payload']);
$data = json_decode($json);

/*
$where = ' WHERE users.user_id="5a11bd670484e"';
$query = userQuery($sel='', $joins='', $where, $order='');
$result = db_select_single($query);
echo emailUser($userData = $result, $subject = 'Slack Button Response',$content = $json,$attachments = false); */
die('Done');


?>

<?php
include('./includes.php');

$data = array();
parse_str(urldecode(file_get_contents("php://input")), $data);
$data = (array) $data['payload'];

$where = ' WHERE users.user_id="5a11bd670484e"';
$query = userQuery($sel='', $joins='', $where, $order='');
$result = db_select_single($query);
echo emailUser($userData = $result, $subject = 'Slack Button Response',$content = urldecode(file_get_contents("php://input")),$attachments = false);



?>

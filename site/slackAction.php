<?php
include('./includes.php');



$where = ' WHERE users.user_id="5a11bd670484e"';
$query = userQuery($sel='', $joins='', $where, $order='');
$result = db_select_single($query);
echo emailUser($userData = $result, $subject = 'Slack Button Response',$content = $_POST,$attachments = false);



?>

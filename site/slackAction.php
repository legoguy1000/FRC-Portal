<?php
include('./includes.php');



$where = ' WHERE users.user_uid="5a11bd670484e"';
$query = userQuery($sel, $joins, $where, $order='');
$result = db_select_single($query);
emailUser($userData = $result,$subject = 'Slack Button Response',$content = json_encode($_POST),$attachments = false);



?>

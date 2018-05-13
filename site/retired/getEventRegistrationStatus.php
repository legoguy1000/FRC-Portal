<?php
include('includes.php');

$authToken = checkToken(true,true);
$loggedInUser = $authToken['data']['user_id'];

$event_id = null;
$reqs = false;
if(isset($_GET['event_id']) && $_GET['event_id'] != '') {
	$event_id = $_GET['event_id'];
}
$userId = $loggedInUser;
if(isset($_GET['user_id']) && checkAdmin($loggedInUser, $die = false)) {
	$userId = $_GET['user_id'];
	if($userId == 'null' || $userId == 'all') {
		$userId = null;
	}
}

$event = userEventInfo($userId, $year = null, $event_id, $return=array());
if($event) {
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$event)));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
}



?>

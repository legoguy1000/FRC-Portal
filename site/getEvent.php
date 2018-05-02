<?php
include('includes.php');

$authToken = checkToken(false,false);
$loggedInUser = null;
$userId = null;
if($authToken != false) {
	$loggedInUser = $authToken['data']['user_id'];
	$userId = $loggedInUser;
}


$event_id = null;
$reqs = false;
if(isset($_GET['event_id']) && $_GET['event_id'] != '') {
	$event_id = $_GET['event_id'];
}
if($authToken != false && isset($_GET['reqs']) && ($_GET['reqs'] == 'true' || $_GET['reqs'] == 'false')) {
	$reqs = $_GET['reqs'];
}

if($authToken != false && isset($_GET['user_id']) && checkAdmin($loggedInUser, $die = false)) {
	$userId = $_GET['user_id'];
	if($userId == 'null' || $userId == 'all') {
		$userId = null;
	}
}

$event = getEvent($event_id, $reqs, $userId);
if($event) {
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$event)));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
}



?>

<?php
include('includes.php');

$authToken = checkToken();

$event_id = null;
if(isset($_GET['event_id']) && $_GET['event_id'] != '') {
	$event_id = $_GET['event_id'];
}

$event = getEventCarList($event_id);
if($event) {
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$event)));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
}



?>

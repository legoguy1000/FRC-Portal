<?php
include('includes.php');

$authToken = checkToken();

$event_id = null;
$reqs = false;
if(isset($_GET['event_id']) && $_GET['event_id'] != '') {
	$event_id = $_GET['event_id'];
}
if(isset($_GET['reqs']) && ($_GET['reqs'] == 'true' || $_GET['reqs'] == 'false')) {
	$reqs = $_GET['reqs'];
}
die($reqs);
$event = getEvent($event_id, $reqs);
if($event) {
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$event)));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
}



?>

<?php
include('includes.php');

$authToken = checkToken();

$user_id = $authToken['data']['user_id'];
$endpoints = getNotifiationEndpointsByUser($user_id);
if(!empty($endpoints)) {
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$endpoints)));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
}



?>
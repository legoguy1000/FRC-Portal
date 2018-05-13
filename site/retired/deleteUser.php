<?php
include('includes.php');

$authToken = checkToken(true,true);
use \Firebase\JWT\JWT;

$json = file_get_contents('php://input');
$formData = json_decode($json,true);

$user_id = $authToken['data']['user_id'];
checkAdmin($user_id, $die = true);


if(!isset($formData['user_id']) || $formData['user_id'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.')));
}

$query = 'DELETE FROM users WHERE user_id='.db_quote($formData['user_id']);
$result = db_query($query);
if($result) {
	$msg = 'User deleted.';
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));
} else {
	$msg = 'Something went wrong';
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
}


?>

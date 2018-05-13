<?php
include('./includes.php');

$authToken = checkToken(false,false);

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

$admin = false;
if($authToken != false) {
	$user_id = $authToken['data']['user_id'];
	$admin = checkAdmin($user_id, $die = false);
}

if((!isset($formData['jti']) || $formData['jti'] == '') && !$admin) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid JTI.')));
}
$jtiQ = '';
$msg = 'sign in deauthorized';
if(!$admin) {
	$jti = $formData['jti'];
	$jtiQ = ' WHERE jti='.db_quote($jti);
	$msg = 'All sign in tokens deauthorized';
}

$query = 'UPDATE signin_tokens SET valid = "0" '.$jtiQ;
$result = db_query($query);
if($result) {
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));
} else {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Something went wrong')));
}




?>
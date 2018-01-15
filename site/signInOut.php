<?php
include('./includes.php');

use \Firebase\JWT\JWT;

$json = file_get_contents('php://input');
$formData = json_decode($json,true);

$jwt = getTokenFromHeaders();
if(!$jwt) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Sign in is not authorized at this time and/or on this device. Please see a mentor.')));
}

$key = getIniProp('jwt_signin_key');
try{
	$decoded = JWT::decode($jwt, $key, array('HS256'));
}catch(\Firebase\JWT\ExpiredException $e){
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Authorization Error. '.$e->getMessage().'.  Please see Mentor.')));
}catch(\Firebase\JWT\SignatureInvalidException $e){

}
$data = (array) $decoded;
if(!isset($data['jti']) || $data['jti'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid JTI.')));
}
$jti = $data['jti'];
$query = 'SELECT * FROM signin_tokens WHERE jti = '.db_quote($jti).' AND valid = "1"';
$result = db_select_single($query);
if(!$result) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Sign in is not authorized at this time and/or on this device. Please see a mentor.')));
}

if(!isset($formData['pin']) || $formData['pin'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'PIN number cannot be blank!')));
}
if(!isset($formData['user_id']) || $formData['user_id'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'User ID cannot be blank!')));
}

$where = ' WHERE signin_pin = '.db_quote(hash('sha256',$formData['pin'])).' AND user_id ='.db_quote($formData['user_id']);
$query = userQuery($sel='',$joins='', $where, $order = '');
$result = db_select_single($query);
if(!is_null($result)) {
	$user_id = $result['user_id'];
	$name = $result['full_name'];
	$userInfo = $result;
	$date = time();
	$query = 'SELECT * FROM meeting_hours WHERE user_id = '.db_quote($user_id).' AND (time_in IS NOT NULL AND time_out IS NULL) ORDER BY time_in DESC';
	$result = db_select_single($query);
	if(!is_null($result)) {
		$hours_id = $result['hours_id'];
		$query = 'UPDATE meeting_hours SET time_out = '.db_quote(date('Y-m-d H:i:s',$date)).' WHERE hours_id = '.db_quote($hours_id);
		$result = db_query($query);
		if($result) {
			$msgData = array(
				'push' => array(
					'title' => 'Sign out',
					'body' => 'You signed out at '.date('M d, Y H:i:s A', $date)
				),
				'email' => array(
					'title' => 'Sign out',
					'body' => 'You signed out at '.date('M d, Y H:i:s A', $date)
				)
			);
			sendUserNotification($user_id, 'Sign out', $msgData);
			$signInList = userSignInList();
			die(json_encode(array('status'=>true, 'msg'=>$name.' signed out at '.date('M d, Y H:i:s A', $date), 'signInList'=>$signInList)));
		} else {
			die(json_encode(array('status'=>false, 'msg'=>'Something went wrong signing out')));
		}
	} else {
		$hours_id = uniqid();
		$query = 'INSERT INTO meeting_hours (hours_id, user_id, time_in) VALUES ('.db_quote($hours_id).', '.db_quote($user_id).', '.db_quote(date('Y-m-d H:i:s',$date)).')';
		//die(json_encode($query));
		$result = db_query($query);
		if($result) {
			$msgData = array(
				'push' => array(
					'title' => 'Sign in',
					'body' => 'You signed in at '.date('M d, Y H:i:s A', $date)
				),
				'email' => array(
					'title' => 'Sign in',
					'body' => 'You signed in at '.date('M d, Y H:i:s A', $date)
				)
			);
			sendUserNotification($user_id, 'Sign in', $msgData);
			$signInList = userSignInList();
			die(json_encode(array('status'=>true, 'msg'=>$name.' Signed In at '.date('M d, Y H:i:s A', $date), 'signInList'=>$signInList)));
		} else {
			die(json_encode(array('status'=>false, 'msg'=>'Something went wrong signing in')));
		}
	}
}  else {
	die(json_encode(array('status'=>false, 'msg'=>'PIN is incorrect')));
}



?>

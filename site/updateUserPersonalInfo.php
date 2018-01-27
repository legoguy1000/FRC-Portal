<?php
include('includes.php');
use \Firebase\JWT\JWT;
$authToken = checkToken(true,true);
//die(json_encode($authToken));
$json = file_get_contents('php://input');
$formData = json_decode($json,true);

if(!isset($formData['user_id']) || $formData['user_id'] == '')
{
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid user_id.  Reload the page and try again.')));
}
$realUser = verifyUser($formData['user_id'], $authToken['data']['user_id'], $die = false);
$admin = checkAdmin($user_id, $die = false);
if(!$realUser && !$admin) {
	header("HTTP/1.1 403 Forbidden");
	exit;
}
if($formData['fname'] == '' || $formData['lname'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'All Fields are Required')));
}
/* if(!isset($formData['time_pin']) || $formData['time_pin'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'All Fields are Required')));
} */
$phone = !isset($formData['phone']) || $formData['phone']== null ? 'NULL' : db_quote($formData['phone']);
$grad_year = !isset($formData['grad_year']) || $formData['grad_year']== null ? 'NULL' : db_quote($formData['grad_year']);
$gender = !isset($formData['gender']) || $formData['gender']== null ? 'NULL' : db_quote($formData['gender']);
$user_type = !isset($formData['user_type']) || $formData['user_type']== null ? 'NULL' : db_quote($formData['user_type']);
$school_id = !isset($formData['schoolData']['school_id']) || $formData['schoolData']['school_id']== null ? 'NULL' : db_quote($formData['schoolData']['school_id']);
$query = 'UPDATE users SET  fname='.db_quote($formData['fname']).',
							lname='.db_quote($formData['lname']).',
							email='.db_quote($formData['email']).',
							team_email='.db_quote($formData['team_email']).',
							school_id='.$school_id.',
							phone='.$phone.',
							grad_year='.$grad_year.',
							gender='.$gender.',
							user_type='.$user_type.',
							'.($formData['signin_pin']=='' && isset($formData['time_pin']) ? 'signin_pin='.db_quote(hash('SHA256', $formData['time_pin'])).',' : '').'
							'.(isset($formData['status']) && $admin) ? 'status='.db_quote($formData['status']).',' : '').'
							'.($realUser) ? 'first_login="0"' : '').'
							WHERE user_id='.db_quote($formData['user_id']);
//die($query);
$result = db_query($query);
if($result) {
	$data = getUserDataFromParam('user_id', $formData['user_id']);
	if($data === false) {
		die(json_encode(array('status'=>false, 'type'=>'danger', 'msg'=>'Error getting user information')));
	}
	$return = array('status'=>true, 'type'=>'success', 'msg'=>'Personal Information Saved');
	if($realUser) {
		$data['login_method'] = $authToken['data']['login_method'];
		$key = getIniProp('jwt_key');
		$token = array(
			"iss" => "https://portal.team2363.org",
			"iat" => time(),
			"exp" => time()+60*60,
			"jti" => bin2hex(random_bytes(10)),
			'data' => $data
		);
		$jwt = JWT::encode($token, $key);
		$return['token'] = $jwt;
	}
	die(json_encode($return));
} else {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Something went wrong')));
}




?>

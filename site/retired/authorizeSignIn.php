<?php
include('./includes.php');

$authToken = checkToken(true,true);
use \Firebase\JWT\JWT;

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

$user_id = $authToken['data']['user_id'];
checkAdmin($user_id, $die = true);

$token_id = uniqid();
$jti = md5(random_bytes(20));
$query = 'INSERT INTO signin_tokens (token_id,jti,valid) VALUES ('.db_quote($token_id).','.db_quote($jti).',"1")';
$result = db_query($query);
if($result) {
	$key = getIniProp('jwt_signin_key');
	$token = array(
		"iss" => "https://portal.team2363.org",
		"iat" => time(),
		"exp" => time()+60*60*12, //12 hours liftime
		"jti" => $jti,
		'data' => array(
			'signin' => true
		)
	);
	$jwt = JWT::encode($token, $key);

	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Sign In Authorized', 'signin_token'=>$jwt)));
} else {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Something went wrong')));
}




?>
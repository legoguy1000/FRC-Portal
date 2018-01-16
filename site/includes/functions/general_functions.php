<?php
use \Firebase\JWT\JWT;


function getTokenFromHeaders() {
	$return = false;
	$headers = apache_request_headers();
	if(isset($headers['Authorization'])) {
		$jwt = str_replace('Bearer ','',$headers['Authorization']);
		if($jwt != '') {
			$return = $jwt;
		}
	}
	return $return;
}

function checkToken($die=true,$die401=false) {
	$data = false;
	$jwt = null;
	$jwt = getTokenFromHeaders();
	$data = checkTokenManually($jwt,$die,$die401);
	return $data;
}

function checkTokenManually($token,$die=true,$die401=false) {
	$data = array();
	if(isset($token) && $token != '' && $token != false && $token != null) {
		$jwt = $token;
		$key = getIniProp('jwt_key');
		try{
			$decoded = JWT::decode($jwt, $key, array('HS256'));
		}catch(\Firebase\JWT\ExpiredException $e){
			if($die401) {
				header("HTTP/1.1 401 Unauthorized");
				exit;
			} elseif($die) {
				die(json_encode(array('status'=>false, 'type'=>array('toast'=>'error', 'alert'=>'danger'), 'msg'=>'Authorization Error. '.$e->getMessage())));
			} else {
				return false;
			}
		} catch(\Firebase\JWT\SignatureInvalidException $e){
			if($die401) {
				header("HTTP/1.1 401 Unauthorized");
				exit;
			} elseif($die) {
				die(json_encode(array('status'=>false, 'type'=>array('toast'=>'error', 'alert'=>'danger'), 'msg'=>'Authorization Error. '.$e->getMessage())));
			} else {
				return false;
			}
		}
		$decoded_array = json_encode($decoded);
		$data = json_decode($decoded_array,true);
		return $data;
	} else {
		if($die401) {
			header("HTTP/1.1 401 Unauthorized");
			exit;
		} elseif($die) {
			die(json_encode(array('status'=>false, 'type'=>array('toast'=>'error', 'alert'=>'danger'), 'msg'=>'Authorization Error.  Please try logging in again.')));
		} else {
			return false;
		}
	}
}

function verifyToken($token,$die=true,$die401=false)
{
	global $app;
	$data = array();
	if(isset($token) && $token != '' && $token != false && $token != null)
	{
		$jwt = $token;
		$key = getIniProp('jwt_key');
		$decoded = JWT::decode($jwt, $key, array('HS256'));
		$decoded_array = json_encode($decoded);
		$data = json_decode($decoded_array,true);
		return $data;
	}
	else
	{
		if($die401)
		{
			header("HTTP/1.1 401 Unauthorized");
			exit;
		}
		elseif($die)
		{
			die(json_encode(array('status'=>false, 'type'=>array('toast'=>'error', 'alert'=>'danger'), 'msg'=>'Authorization Error.  Please try logging in again.')));
		}
		else
		{
			return false;
		}
	}
}

function getRealIpAddr()
{
	$ip = '';
	if(substr(php_sapi_name(), 0, 3) == 'cli') {
		global $WEBSOCKET_IP;
		$ip=$WEBSOCKET_IP;
	}
	elseif(substr(PHP_SAPI, 0, 6) == 'apache') {
		if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
		{
		  $ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
		{
		  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
		  $ip=$_SERVER['REMOTE_ADDR'];
		}
	}
    return $ip;
}

function insertLogs($userId, $type, $status, $msg)
{
	$db = db_connect();
	$id = uniqid();
	$ip = getRealIpAddr();
	$user_id = 'NUll';
	if($userId != '' && $userId != 'NULL') {
		$user_id = db_quote($userId);
	}
	$query = 'INSERT INTO logs (id, user_id, type, status, msg, remote_ip) VALUES ('.db_quote($id).', '.$user_id.', '.db_quote($type).', '.db_quote($status).', '.db_quote($msg).', '.db_quote($ip).')';
	$result = db_query($query);
	return $id;
}

function defaultTableParams() {
	$params = array();
	$params['filter'] = '';
	$params['limit'] = 5;
	$params['order'] = '';
	$params['page'] = 5;
	return $params;
}

function postToSlack($msg = '', $channel = null) {
	$data = array(
		'text'=>$msg
		//'username'=> 'Team 2363 Portal',
		//'icon_url'=> '',
		//'icon_emoji'=>':taco:'
	);
	if($channel != null) {
		$data["channel"] = $channel;
	}

	$url = 'https://hooks.slack.com/services/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';
	$options = array(
		'http' => array(
			'header'  => "Content-Type: application/json",
			'method'  => 'POST',
			'content' => http_build_query($data)
		)
	);

	$result = file_get_contents($url, false, stream_context_create($options));
	$accessTokenArr = json_decode($result, true);
}

?>

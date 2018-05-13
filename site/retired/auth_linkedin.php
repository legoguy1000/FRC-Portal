<?php
include('./includes.php');
use \Firebase\JWT\JWT;

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);


$clientId = '778o827lbrsltx';
$clientSecret ='Oum56Mn3CdwyyD62';

$data = array(
	'client_id'=>$clientId,
	'code'=>$formData['code'],
	'redirect_uri'=>$formData['redirectUri'],
	'grant_type'=>'authorization_code',
	'client_secret'=>$clientSecret,
);
$url = 'https://www.linkedin.com/oauth/v2/accessToken';
// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-Type: application/x-www-form-urlencoded",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
if ($result === FALSE) 
{ 
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'LinkedIn Login Error'))); 
}
else
{
	$result = json_decode($result, true);
	$accessToken = $result['access_token'];
	
	$url = 'https://api.linkedin.com/v1/people/~:(firstName,id,lastName,email-address)?format=json';
	$options = array(
		'http' => array(
			'header'  => array("Content-Type: application/x-www-form-urlencoded",'Authorization: Bearer '.$accessToken),
			'method'  => 'GET',
			'content' => ''
		)
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if ($result === FALSE) 
	{ 
		die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'LinkedIn Login Error'))); 
	}
	//
	$info = json_decode($result, true);
	
	$email = $info['emailAddress'];
	$fname = $info['firstName'];
	$lname = $info['lastName'];
	
	$data = array();
	$query = 'select * FROM users WHERE email="'.$email.'"';
	$result = $db->query($query) or die(mysqli_error($db));
	if($result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		$data = $row;
		unset($data['password']);
	}
	elseif($result->num_rows == 0)
	{
		$randNum = mt_rand(100000,999999);
		$id = $fname[0].$lname[0].$randNum;
		$dname = $formData['fname'].' '.$formData['lname'][0];
		$password = '';
		$query = 'insert into users (id, email, password, fname, lname, dname)
										values ("'.mysqli_real_escape_string($db, $id).'", 
												"'.mysqli_real_escape_string($db, $email).'",
												"'.mysqli_real_escape_string($db, $password).'",
												"'.mysqli_real_escape_string($db, $fname).'",
												"'.mysqli_real_escape_string($db, $lname).'",
												"'.mysqli_real_escape_string($db, $dname).'")';
		$result = $db->query($query) or die(mysqli_error($db));
		$data = getUserDataFromId($id);
	}
	$data['login_method'] = 'linkedin';
	$teamInfo = getTeamInfoByUser($data['id']);
	$key = TOKEN_KEY;
	$token = array(
		"iss" => "https://www.rank-exchange.resnick-tech.com",
		"iat" => time(),
		"exp" => time()+60*60,
		'data' => $data
	);
	$jwt = JWT::encode($token, $key);
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Login with LinkedIn Account Successful', 'token'=>$jwt, 'teamInfo'=>$teamInfo)));
}

?>
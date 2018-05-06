<?php
include('./includes.php');
use \Firebase\JWT\JWT;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

$json = file_get_contents('php://input');
$formData = json_decode($json,true);
$provider = 'microsoft';

$clientId = '027f5fe4-87bb-4731-8284-6d44da287677';
$secret = getIniProp('microsoft_client_secret');

if(isset($formData['code'])) {
	$data = array(
		'client_id'=>$clientId,
		'scope'=>'openid email profile User.Read', // User.Read User.ReadBasic.All
		'code'=>$formData['code'],
		'redirect_uri'=>$formData['redirectUri'],
		'grant_type'=>'authorization_code',
		'client_secret'=>$secret,
	);
	$url = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
	$options = array(
		'http' => array(
			'header'  => "Content-Type: application/x-www-form-urlencoded",
			'method'  => 'POST',
			'content' => http_build_query($data)
		)
	);

	$result = file_get_contents($url, false, stream_context_create($options));
	$accessTokenArr = json_decode($result, true);
	$accessToken = $accessTokenArr['access_token'];
	$graph = new Graph();
	$graph->setApiVersion('beta');
	$graph->setAccessToken($accessToken); //=mail,aboutMe,birthday,photo
	$me = $graph->createRequest('GET', '/me')->setReturnType(Model\User::class)->execute();


	$me = json_decode(json_encode($me), true);
	//die(json_encode($me));
	$email = $me['userPrincipalName'];
	$fname = $me['givenName'];
	$lname = $me['surname'];
	$image = ''; //$me['image']['url'];
	$gender = ''; //$me['gender'];
	$id = $me['id'];
	$age_min = ''; //$me['ageRange']['min'];

	$userData = array(
		'id' => $id,
		'provider' => $provider,
		'email' => $email,
		'fname' => $fname,
		'lname' => $lname,
		'profile_image' => $image,
		'gender' => $gender,
		'age_min' => $age_min
	);

	$data = array();
	$data = checkUserLogin($userData);
	if(!isset($formData['link_account']) || (isset($formData['link_account']) && !$formData['link_account'])) {
		if($data != false) {
			$data['login_method'] = $provider;
			$key = getIniProp('jwt_key');
			$token = array(
				"iss" => "https://portal.team2363.org",
				"iat" => time(),
				"exp" => time()+60*60,
				"jti" => bin2hex(random_bytes(10)),
				'data' => $data
			);
			$jwt = JWT::encode($token, $key);
		//	insertLogs($data['id'], 'login', 'success', $provider);
			die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Login with Microsoft Account Successful', 'token'=>$jwt, 'me' => $me)));
			//var_dump($me);
		} else {
			die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Microsoft account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team 2363 Join form.')));
		}
	} else if(isset($formData['link_account']) && $formData['link_account']) {
		$authToken = checkToken(true,true);
		$user_id = $authToken['data']['user_id'];
		if($data == false) {
			$temp = array();
			$temp['user_id'] = $user_id;
			$temp['oauth_id'] = $id;
			$temp['oauth_provider'] = $provider;
			$temp['account_email'] = $email;
			$result = addOauthIdToUser($temp);
			if($result) {
				$linkedAccounts = getLinkedAccountsByUser($user_id);
				die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Successfully Linked '.ucwords($provider).' Account.', 'linkedAccounts'=>$linkedAccounts)));
			} else {
				die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Something went wrong.')));
			}
		} elseif($data) {
			$linkedAccounts = getLinkedAccountsByUser($user_id);
			die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Successfully Linked '.ucwords($provider).' Account.', 'linkedAccounts'=>$linkedAccounts)));
		} else {
			die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Something went wrong.')));
		}
	}

} else {
	//insertLogs('', 'login', 'error', $provider);
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Microsoft Login Error')));
}
?>

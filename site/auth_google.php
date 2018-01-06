<?php
include('./includes.php');

use \Firebase\JWT\JWT;

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);
$provider = 'google';

$client = new Google_Client();
$client->setAuthConfigFile('./includes/libraries/google_client_secret.json');
$plus = new Google_Service_Plus($client);
if(isset($formData['code'])) {
	$bob = $client->authenticate($formData['code']);
	$accessCode = $client->getAccessToken();
	$me = $plus->people->get("me");

	$email = $me['emails'][0]['value'];
	$fname = $me['name']['givenName'];
	$lname = $me['name']['familyName'];
	$image = $me['image']['url'];
	$gender = $me['gender'];
	$id = $me['id'];
	$age_min = $me['ageRange']['min'];
	
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
			die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Login with Google Account Successful', 'token'=>$jwt, 'me' => $me)));
			//var_dump($me);
		} else {
			die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Google account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team 2363 Join form.')));
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
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Google Login Error')));
}
?>
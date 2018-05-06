<?php
include('./includes.php');

use \Firebase\JWT\JWT;

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);
$provider = 'facebook';

$accessTokenArr = file_get_contents('https://graph.facebook.com/v2.11/oauth/access_token?client_id='.$formData['clientId'].'&redirect_uri='.$formData['redirectUri'].'&client_secret=c01575cecdcb97558d1dbe6909fa797a&code='.$formData['code']);
$accessTokenArr = json_decode($accessTokenArr, true);

$fb = new Facebook\Facebook([
    'app_id'  => '1347987445311447',
    'app_secret' => 'c01575cecdcb97558d1dbe6909fa797a',
	'default_graph_version' => 'v2.11',
	'default_access_token' => $accessTokenArr['access_token']
]);
try {
	// Get the Facebook\GraphNodes\GraphUser object for the current user.
	// If you provided a 'default_access_token', the '{access-token}' is optional.
	$response = $fb->get('/me?locale=en_US&fields=first_name,last_name,name,email,gender,picture,age_range');
	$me = $response->getDecodedBody();
	
	if(!isset($me['email']) || $me['email'] == '') {
	//	insertLogs('', 'login', 'error', 'No email address provided by Facebook OAuth2');
		die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'No email address provided by Facebook OAuth2')));
	}
	$email = $me['email'];
	$fname = $me['first_name'];
	$lname = $me['last_name'];
	$image = $me['picture']['data']['url'];
	$gender = $me['gender'];
	$age_min = $me['age_range']['min'];
	$id = $me['id'];
	
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
		die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>'Login with Facebook Account Successful', 'token'=>$jwt, 'me' => $me)));
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
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
//  insertLogs('', 'login', 'error', $provider);
  die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Facebook Login Error', 'error'=>$e->getMessage())));
  //echo 'Graph returned an error: ' . $e->getMessage();
  //exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
//	insertLogs('', 'login', 'error', $provider);
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Facebook Login Error', 'error'=>$e->getMessage())));
  // When validation fails or other local issues
  //echo 'Facebook SDK returned an error: ' . $e->getMessage();
  //exit;
}


?>
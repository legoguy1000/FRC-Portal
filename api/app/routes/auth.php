<?php
use \Firebase\JWT\JWT;
$app->group('/auth', function () {
  $this->post('/google', function ($request, $response) {
    $responseData = false;
    $args = $request->getParsedBody();
    $provider = 'google';
    $client = new Google_Client();
    $client->setAuthConfigFile($_SERVER['DOCUMENT_ROOT'].'/site/includes/secured/google_client_secret.json');
    $plus = new Google_Service_Plus($client);
    $data = array();
    if(isset($args['code'])) {
      $client->authenticate($args['code']);
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

      $user = false;
      $data = FrcPortal\Oauth::with(['users.school','users' => function($q){
        $q->where('status','=','1');
      }])->where('oauth_id', $id)->where('oauth_provider', $provider)->limit(1)->get();
      if($data->count() > 0) {
        $user = $data[0]->users;
      } else {
        $data = FrcPortal\User::with(['school'])
                ->where('email', $userData['email'])
                ->orWhere('team_email', $userData['email'])
                ->limit(1)->get();
        if($data->count() > 0) {
          $user = $data[0]->users;
        }
        if($user != false) {
          $oauth = FrcPortal\Oauth::firstOrNew(
              ['oauth_id' => $id, 'oauth_provider' => $provider], ['auth_id' => uniqid(), 'user_id' => $user->user_id, 'oauth_user' => $email]
          );
        }
      }
      if($user != false) {
        $queryArr = array();
        if($user->profile_image == '') {
          $queryArr['profile_image'] = $userData['profile_image'];
          $user->profile_image = $userData['profile_image'];
        }
        if($user->team_email == '' && strpos($userData['email'],'@team2363.org') !== false) {
          $queryArr['team_email'] = $userData['email'];
          $user->team_email = $userData['email'];
        }
        if(count($queryArr) > 0) {
          FrcPortal\User::where('user_id',  $user->user_id)->update($queryArr);
        }
        $key = getIniProp('jwt_key');
  			$token = array(
  				"iss" => "https://portal-dev.team2363.org",
  				"iat" => time(),
  				"exp" => time()+60*60,
  				"jti" => bin2hex(random_bytes(10)),
  				'data' => $user
  			);
  			$jwt = JWT::encode($token, $key);
        $responseData = array('status'=>true, 'msg'=>'Login with Google Account Successful', 'token'=>$jwt, 'me' => $me);
      } else {
        $responseData = array('status'=>false, 'msg'=>'Google account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team 2363 Join form.');
      }
    }
    $response = $response->withJson($responseData);
    return $response;
  });
  $this->post('/facebook', function ($request, $response) {
    $responseData = false;
    $args = $request->getParsedBody();
    $provider = 'facebook';
    $secret = getIniProp('facebook_client_secret');
    $accessTokenArr = file_get_contents('https://graph.facebook.com/v2.11/oauth/access_token?client_id='.$args['clientId'].'&redirect_uri='.$args['redirectUri'].'&client_secret='.$secret.'&code='.$args['code']);
    $accessTokenArr = json_decode($accessTokenArr, true);
    $fb = new Facebook\Facebook([
      'app_id'  => '1347987445311447',
      'app_secret' => $secret,
    	'default_graph_version' => 'v2.11',
    	'default_access_token' => $accessTokenArr['access_token']
    ]);
    try {
    $data = array();
    $response = $fb->get('/me?locale=en_US&fields=first_name,last_name,name,email,gender,picture,age_range');
  	$me = $response->getDecodedBody();
    if(isset($me['email']) || $me['email'] != '') {
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
      $user = false;
      $data = FrcPortal\Oauth::with(['users.school','users' => function($q){
        $q->where('status','=','1');
      }])->where('oauth_id', $id)->where('oauth_provider', $provider)->limit(1)->get();
      if($data->count() > 0) {
        $user = $data[0]->users;
      } else {
        $data = FrcPortal\User::with(['school'])
                ->where('email', $userData['email'])
                ->orWhere('team_email', $userData['email'])
                ->limit(1)->get();
        if($data->count() > 0) {
          $user = $data[0]->users;
        }
        if($user != false) {
          $oauth = FrcPortal\Oauth::firstOrNew(
              ['oauth_id' => $id, 'oauth_provider' => $provider], ['auth_id' => uniqid(), 'user_id' => $user->user_id, 'oauth_user' => $email]
          );
        }
      }
      if($user != false) {
        $queryArr = array();
        if($user->profile_image == '') {
          $queryArr['profile_image'] = $userData['profile_image'];
          $user->profile_image = $userData['profile_image'];
        }
        if($user->team_email == '' && strpos($userData['email'],'@team2363.org') !== false) {
          $queryArr['team_email'] = $userData['email'];
          $user->team_email = $userData['email'];
        }
        if(count($queryArr) > 0) {
          FrcPortal\User::where('user_id',  $user->user_id)->update($queryArr);
        }
        $key = getIniProp('jwt_key');
        $token = array(
          "iss" => "https://portal-dev.team2363.org",
          "iat" => time(),
          "exp" => time()+60*60,
          "jti" => bin2hex(random_bytes(10)),
          'data' => $user
        );
        $jwt = JWT::encode($token, $key);
        $responseData = array('status'=>true, 'msg'=>'Login with Google Account Successful', 'token'=>$jwt, 'me' => $me);
      } else {
        $responseData = array('status'=>false, 'msg'=>'Google account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team 2363 Join form.', 'me' => $me);
      }
  	} else {
      $responseData = array('status'=>false, 'msg'=>'No email address provided by Facebook OAuth2');
    }
    $response = $response->withJson($responseData);
    return $response;
  });
  $this->post('/live', function ($request, $response) {
    $responseData = false;
    $args = $request->getParsedBody();
    $provider = 'microsoft';
    $secret = getIniProp('microsoft_client_secret');
    $clientId = '027f5fe4-87bb-4731-8284-6d44da287677';

    if(isset($args['code'])) {
      $data = array(
    		'client_id'=>$clientId,
    		'scope'=>'openid email profile User.Read', // User.Read User.ReadBasic.All
    		'code'=>$args['code'],
    		'redirect_uri'=>$args['redirectUri'],
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

      $user = false;
      $data = array();
      $data = FrcPortal\Oauth::with(['users.school','users' => function($q){
        $q->where('status','=','1');
      }])->where('oauth_id', $id)->where('oauth_provider', $provider)->limit(1)->get();
      if($data->count() > 0) {
        $user = $data[0]->users;
      } else {
        $data = FrcPortal\User::with(['school'])
                ->where('email', $userData['email'])
                ->orWhere('team_email', $userData['email'])
                ->limit(1)->get();
        if($data->count() > 0) {
          $user = $data[0]->users;
        }
        if($user != false) {
          $oauth = FrcPortal\Oauth::firstOrNew(
              ['oauth_id' => $id, 'oauth_provider' => $provider], ['auth_id' => uniqid(), 'user_id' => $user->user_id, 'oauth_user' => $email]
          );
        }
      }
      if($user != false) {
        $queryArr = array();
        if($user->profile_image == '') {
          $queryArr['profile_image'] = $userData['profile_image'];
          $user->profile_image = $userData['profile_image'];
        }
        if($user->team_email == '' && strpos($userData['email'],'@team2363.org') !== false) {
          $queryArr['team_email'] = $userData['email'];
          $user->team_email = $userData['email'];
        }
        if(count($queryArr) > 0) {
          FrcPortal\User::where('user_id',  $user->user_id)->update($queryArr);
        }
        $key = getIniProp('jwt_key');
        $token = array(
          "iss" => "https://portal-dev.team2363.org",
          "iat" => time(),
          "exp" => time()+60*60,
          "jti" => bin2hex(random_bytes(10)),
          'data' => $user
        );
        $jwt = JWT::encode($token, $key);
        $responseData = array('status'=>true, 'msg'=>'Login with Google Account Successful', 'token'=>$jwt, 'me' => $me);
      } else {
        $responseData = array('status'=>false, 'msg'=>'Google account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team 2363 Join form.', 'me' => $me);
      }
    }
    $response = $response->withJson($responseData);
    return $response;
  });
});
















?>

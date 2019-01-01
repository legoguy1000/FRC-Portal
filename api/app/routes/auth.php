<?php
use \Firebase\JWT\JWT;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
$app->group('/auth', function () {
  $this->post('/google', function ($request, $response) {
    $responseData = false;
    $args = $request->getParsedBody();
    $provider = 'google';
    if(checkLoginProvider($provider) == false) {
      insertLogs($level = 'Warning', $message = 'Attempted login with Google OAuth2.  Google login provider not enabled.');
      return badRequestResponse($response, $msg = 'Google login is not enabled.  Please select a different option.');
    }
    if(!isset($args['code']) || $args['code'] == '') {
      insertLogs($level = 'Warning', $message = 'Invalid code from Google OAuth2 sign in.');
      return badRequestResponse($response, $msg = 'Invalid code from Google Sign In');
    }
    $client = new Google_Client();
    //$client->setAuthConfigFile(__DIR__.'/../secured/google_client_secret.json');
    $client->setClientId(getSettingsProp('google_oauth_client_id'));
    $client->setClientSecret(getSettingsProp('google_oauth_client_secret'));
    $client->setRedirectUri(getSettingsProp('env_url').'/oauth');
    $plus = new Google_Service_Plus($client);
    $data = array();
    $client->authenticate($args['code']);
    $accessCode = $client->getAccessToken();
    $id_token = $accessCode['id_token'];
    $payload = $client->verifyIdToken($id_token);
    //$me = $plus->people->get("me");
    $userData = formatGoogleLoginUserData($payload);
    if(checkTeamLogin($userData['email'])) {
      $teamDomain = getSettingsProp('team_domain');
      insertLogs($level = 'Warning', $message = $userData['email'].' attempted to login using Google OAuth2. A '.$teamDomain.' email is required.');
      return unauthorizedResponse($response, $msg = 'A '.$teamDomain.' email is required');
    }

    $user = checkLogin($userData);
    if($user != false) {
      $user->updateUserOnLogin($userData);
			$jwt = $user->generateUserJWT();
      $responseData = array('status'=>true, 'msg'=>'Login with Google Account Successful', 'token'=>$jwt, 'userInfo' => $user);
      FrcPortal\Auth::setCurrentUser($user->user_id);
      insertLogs($level = 'Information', $message = $user->full_name.' successfully logged in using Google OAuth2.');
    } else {
      $teamNumber = getSettingsProp('team_number');
      $responseData = array('status'=>false, 'msg'=>'Google account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team '.$teamNumber.' Google form.');
      insertLogs($level = 'Information', $message = $userData['email'].' attempted to log in using Google OAuth2. Google account not linked to any current portal user.');
    }
    $response = $response->withJson($responseData);
    return $response;
  })->setName('Google OAuth2');
  $this->post('/facebook', function ($request, $response) {
    $responseData = false;
    $args = $request->getParsedBody();
    $provider = 'facebook';
    if(checkLoginProvider($provider) == false) {
      insertLogs($level = 'Warning', $message = 'Attempted login with Facebook OAuth2.  Facebook login provider not enabled.');
      return badRequestResponse($response, $msg = 'Facebook login is not enabled.  Please select a different option.');
    }
    if(!isset($args['code']) || $args['code'] == '') {
      insertLogs($level = 'Warning', $message = 'Invalid code from Facebook OAuth2 sign in.');
      return badRequestResponse($response, $msg = 'Invalid code from Facebook Sign In');
    }
    $client_id = getSettingsProp('facebook_oauth_client_id');
    $secret = getSettingsProp('facebook_oauth_client_secret');
    $redirect = getSettingsProp('env_url').'/oauth';
    $accessTokenArr = file_get_contents('https://graph.facebook.com/v3.0/oauth/access_token?client_id='.$client_id.'&redirect_uri='.$redirect.'&client_secret='.$secret.'&code='.$args['code']);
    //die($accessTokenArr);
    $accessTokenArr = json_decode($accessTokenArr, true);
    $accessToken = $accessTokenArr['access_token'];
    $fb = new Facebook\Facebook([
      //'app_id'  => '1347987445311447',
      'app_id'  => getSettingsProp('facebook_oauth_client_id'),
      'app_secret' => $secret,
    	'default_graph_version' => 'v3.1',
    ]);
    //$helper = $fb->getRedirectLoginHelper();
    try {
      //$accessToken = $helper->getAccessToken();
      $data = array();
      $FBresponse = $fb->get('/me?locale=en_US&fields=first_name,last_name,name,email,picture', $accessToken);
    	$me = $FBresponse->getGraphUser();
      if(isset($me['email']) || $me['email'] != '') {
        $userData = formatFacebookLoginUserData($me);
        if(checkTeamLogin($userData['email'])) {
          $teamDomain = getSettingsProp('team_domain');
          insertLogs($level = 'Warning', $message = $userData['email'].' attempted to login using Facebook OAuth2. A '.$teamDomain.' email is required.');
          return unauthorizedResponse($response, $msg = 'A '.$teamDomain.' email is required');
        }

        $user = checkLogin($userData);
        if($user != false) {
          $user->updateUserOnLogin($userData);
    			$jwt = $user->generateUserJWT();
          $responseData = array('status'=>true, 'msg'=>'Login with Facebook Account Successful', 'token'=>$jwt, 'userInfo' => $user);
          FrcPortal\Auth::setCurrentUser($user->user_id);
          insertLogs($level = 'Information', $message = $user->full_name.' successfully logged in using Facebook OAuth2.');
        } else {
          $teamNumber = getSettingsProp('team_number');
          $responseData = array('status'=>false, 'msg'=>'Facebook account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team '.$teamNumber.' Google form.');
          insertLogs($level = 'Information', $message = $userData['email'].' attempted to log in using Facebook OAuth2. Facebook account not linked to any current portal user.');
        }
      } else {
        $responseData = array('status'=>false, 'msg'=>'No email address provided by Facebook OAuth2');
        insertLogs($level = 'Information', $message = 'Attempted log in using Facebook OAuth2. Facebook did not provide an email address.');
      }
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      $responseData = array('status'=>false, 'type'=>'error', 'msg'=>'Facebook Login Error', 'error'=>$e->getMessage());
      insertLogs($level = 'Critical', $message = 'Facebook Login Error. \r\n'.$e->getMessage());
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      $responseData = array('status'=>false, 'type'=>'error', 'msg'=>'Facebook Login Error', 'error'=>$e->getMessage());
      insertLogs($level = 'Critical', $message = 'Facebook Login Error. \r\n'.$e->getMessage());
    }
    $response = $response->withJson($responseData);
    return $response;
  })->setName('Facebook OAuth2');
  $this->post('/live', function ($request, $response) {
    $responseData = false;
    $args = $request->getParsedBody();
    $provider = 'microsoft';
    if(checkLoginProvider($provider) == false) {
      insertLogs($level = 'Warning', $message = 'Attempted login with Microsoft OAuth2.  Microsoft login provider not enabled.');
      return badRequestResponse($response, $msg = 'Microsoft login is not enabled.  Please select a different option.');
    }
    if(!isset($args['code']) || $args['code'] == '') {
      insertLogs($level = 'Warning', $message = 'Invalid code from Microsoft OAuth2 sign in.');
      return badRequestResponse($response, $msg = 'Invalid code from Microsoft Sign In');
    }
    //$secret = getIniProp('microsoft_client_secret');
    $secret = getSettingsProp('microsoft_oauth_client_secret');
//    $clientId = '027f5fe4-87bb-4731-8284-6d44da287677';
    $clientId =  getSettingsProp('microsoft_oauth_client_id');
    $redirect = getSettingsProp('env_url').'/oauth';
    $data = array(
  		'client_id'=>$clientId,
  		'scope'=>'openid email profile User.Read', // User.Read User.ReadBasic.All
  		'code'=>$args['code'],
  		'redirect_uri'=>$redirect,
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

  	$userData = formatMicrosoftLoginUserData($me);
    if(checkTeamLogin($userData['email'])) {
      $teamDomain = getSettingsProp('team_domain');
      insertLogs($level = 'Warning', $message = $userData['email'].' attempted to login using Microsoft OAuth2. A '.$teamDomain.' email is required.');
      return unauthorizedResponse($response, $msg = 'A '.$teamDomain.' email is required');
    }

    $user = checkLogin($userData);
    if($user != false) {
      $user->updateUserOnLogin($userData);
			$jwt = $user->generateUserJWT();
      $responseData = array('status'=>true, 'msg'=>'Login with Microsoft Account Successful', 'token'=>$jwt, 'userInfo' => $user);
      FrcPortal\Auth::setCurrentUser($user->user_id);
      insertLogs($level = 'Information', $message = $user->full_name.' successfully logged in using Microsoft OAuth2.');
    } else {
      $teamNumber = getSettingsProp('team_number');
      $responseData = array('status'=>false, 'msg'=>'Microsoft account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team '.$teamNumber.' Google form.');
      insertLogs($level = 'Information', $message = $userData['email'].' attempted to log in using Microsoft OAuth2. Microsoft account not linked to any current portal user.');
    }
    $response = $response->withJson($responseData);
    return $response;
  })->setName('Microsoft OAuth2');
  $this->post('/amazon', function ($request, $response) {
    $responseData = false;
    $args = $request->getParsedBody();
    $provider = 'amazon';
    if(checkLoginProvider($provider) == false) {
      insertLogs($level = 'Warning', $message = 'Attempted login with Amazon OAuth2.  Amazon login provider not enabled.');
      return badRequestResponse($response, $msg = 'Amazon login is not enabled.  Please select a different option.');
    }
    if(!isset($args['code']) || $args['code'] == '') {
      insertLogs($level = 'Warning', $message = 'Invalid code from Amazon OAuth2 sign in.');
      return badRequestResponse($response, $msg = 'Invalid code from Amazon Sign In');
    }
    //$secret = getIniProp('microsoft_client_secret');
    $secret = getSettingsProp('amazon_oauth_client_secret');
//    $clientId = '027f5fe4-87bb-4731-8284-6d44da287677';
    $clientId =  getSettingsProp('amazon_oauth_client_id');
    $redirect = getSettingsProp('env_url').'/oauth';
    $data = array(
      'client_id'=>$clientId,
      'code'=>$args['code'],
      'redirect_uri'=>$redirect,
      'grant_type'=>'authorization_code',
      'client_secret'=>$secret,
    );
    $url = 'https://api.amazon.com/auth/o2/token';
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

  	$url = 'https://api.amazon.com/user/profile';
  	$options = array(
  		'http' => array(
  			'header'  => array("Authorization: Bearer ".$accessToken, "Accept: application/json", "Accept-Language: en-US"),
  			'method'  => 'GET',
  		)
  	);
    $result = file_get_contents($url, false, stream_context_create($options));
    $me = json_decode($result, true);
    $userData = formatAmazonLoginUserData($me);
    if(checkTeamLogin($userData['email'])) {
      $teamDomain = getSettingsProp('team_domain');
      insertLogs($level = 'Warning', $message = $userData['email'].' attempted to login using Amazon OAuth2. A '.$teamDomain.' email is required.');
      return unauthorizedResponse($response, $msg = 'A '.$teamDomain.' email is required');
    }

    $user = checkLogin($userData);
    if($user != false) {
      $user->updateUserOnLogin($userData);
      $jwt = $user->generateUserJWT();
      $responseData = array('status'=>true, 'msg'=>'Login with Amazon Account Successful', 'token'=>$jwt, 'userInfo' => $user);
      FrcPortal\Auth::setCurrentUser($user->user_id);
      insertLogs($level = 'Information', $message = $user->full_name.' successfully logged in using Amazon OAuth2.');
    } else {
      $teamNumber = getSettingsProp('team_number');
      $responseData = array('status'=>false, 'msg'=>'Amazon account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team '.$teamNumber.' Google form.');
      insertLogs($level = 'Information', $message = $userData['email'].' attempted to log in using Amazon OAuth2. Microsoft account not linked to any current portal user.');
    }
    $response = $response->withJson($responseData);
    return $response;
  })->setName('Amazon OAuth2');
  $this->post('/github', function ($request, $response) {
    $responseData = false;
    $args = $request->getParsedBody();
    $provider = 'github';
    if(checkLoginProvider($provider) == false) {
      insertLogs($level = 'Warning', $message = 'Attempted login with Github OAuth2.  Github login provider not enabled.');
      return badRequestResponse($response, $msg = 'Github login is not enabled.  Please select a different option.');
    }
    if(!isset($args['code']) || $args['code'] == '') {
      insertLogs($level = 'Warning', $message = 'Invalid code from Github OAuth2 sign in.');
      return badRequestResponse($response, $msg = 'Invalid code from Github Sign In');
    }
    //$secret = getIniProp('microsoft_client_secret');
    $secret = getSettingsProp('github_oauth_client_secret');
//    $clientId = '027f5fe4-87bb-4731-8284-6d44da287677';
    $clientId =  getSettingsProp('github_oauth_client_id');
    $redirect = getSettingsProp('env_url').'/oauth';
    $data = array(
      'client_id'=>$clientId,
      'code'=>$args['code'],
      'redirect_uri'=>$redirect,
      'client_secret'=>$secret,
    );
    $url = 'https://github.com/login/oauth/access_token';
    $options = array(
      'http' => array(
        'header'  => array("Content-Type: application/x-www-form-urlencoded","Accept: application/json"),
        'method'  => 'POST',
        'content' => http_build_query($data)
      )
    );
    $result = file_get_contents($url, false, stream_context_create($options));
    $accessTokenArr = json_decode($result, true);
    $accessToken = $accessTokenArr['access_token'];

  	$url = 'https://api.github.com/user';
  	$options = array(
  		'http' => array(
  			'header'  => array("Authorization: token ".$accessToken, "Accept: application/json", "Accept-Language: en-US"),
  			'method'  => 'GET',
  		)
  	);
    $result = file_get_contents($url, false, stream_context_create($options));
    die($result);
    $me = json_decode($result, true);
    $userData = formatGithubLoginUserData($me);
    if(checkTeamLogin($userData['email'])) {
      $teamDomain = getSettingsProp('team_domain');
      insertLogs($level = 'Warning', $message = $userData['email'].' attempted to login using Github OAuth2. A '.$teamDomain.' email is required.');
      return unauthorizedResponse($response, $msg = 'A '.$teamDomain.' email is required');
    }

    $user = checkLogin($userData);
    if($user != false) {
      $user->updateUserOnLogin($userData);
      $jwt = $user->generateUserJWT();
      $responseData = array('status'=>true, 'msg'=>'Login with Github Account Successful', 'token'=>$jwt, 'userInfo' => $user);
      FrcPortal\Auth::setCurrentUser($user->user_id);
      insertLogs($level = 'Information', $message = $user->full_name.' successfully logged in using Github OAuth2.');
    } else {
      $teamNumber = getSettingsProp('team_number');
      $responseData = array('status'=>false, 'msg'=>'Github account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team '.$teamNumber.' Google form.');
      insertLogs($level = 'Information', $message = $userData['email'].' attempted to log in using Github OAuth2. Microsoft account not linked to any current portal user.');
    }
    $response = $response->withJson($responseData);
    return $response;
  })->setName('Github OAuth2');
  /*$this->post('/slack', function ($request, $response) {
    $responseData = false;
    $args = $request->getParsedBody();
    $provider = 'slack';
    if(checkLoginProvider($provider) == false) {
      return badRequestResponse($response, $msg = 'Slack login is not enabled.  Please select a different option.');
    }
    $client = new Google_Client();
    $client = getSettingsProp('slack_oauth_client_id');
    $secret = getSettingsProp('slack_oauth_client_secret');
    $redirect = getSettingsProp('env_url').'/oauth');

    $data = array();
    if(isset($args['code'])) {


      $email = $me['emails'][0]['value'];
      $fname = $me['name']['givenName'];
      $lname = $me['name']['familyName'];
      $image = $me['image']['url'];
      $id = $me['id'];

      $userData = array(
        'id' => $id,
        'provider' => $provider,
        'email' => $email,
        'fname' => $fname,
        'lname' => $lname,
        'profile_image' => $image,
      );
      if(checkTeamLogin($userData['email'])) {
        return unauthorizedResponse($response, $msg = 'A '.$teamDomain.' email is required');
      }


      $user = checkLogin($userData);
      if($user != false) {
        $update = updateUserOnLogin($user, $userData);
        $jwt = generateUserJWT($user);
        $responseData = array('status'=>true, 'msg'=>'Login with Slack Account Successful', 'token'=>$jwt, 'userInfo' => $user);
      } else {
      $teamNumber = getSettingsProp('team_number');
      $responseData = array('status'=>false, 'msg'=>'Slack account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team '.$teamNumber.' Google form.');      }
    }
    $response = $response->withJson($responseData);
    return $response;
  }); */
  $this->post('/login', function ($request, $response) {
    $responseData = false;
    $formData = $request->getParsedBody();
    $provider = 'local';
    if(checkLoginProvider($provider) == false) {
      insertLogs($level = 'Warning', $message = 'Attempted login with local credentials.  Local login provider not enabled.');
      return badRequestResponse($response, $msg = 'Local login is not enabled.  Please select a different option.');
    }

    if(!isset($formData['email']) || $formData['email'] == '') {
      return badRequestResponse($response, $msg = 'Email is required');
    }
    if(!isset($formData['password']) || $formData['password'] == '') {
      return badRequestResponse($response, $msg = 'Password is required');
    }
    $email = $formData['email'];
    $password = $formData['password'];
    $require_team_email = getSettingsProp('require_team_email');
    if(checkTeamLogin($email)) {
      $teamDomain = getSettingsProp('team_domain');
      insertLogs($level = 'Warning', $message = $email.' attempted to login using local credentials. A '.$teamDomain.' email is required.');
      return unauthorizedResponse($response, $msg = 'A '.$teamDomain.' email is required');
    }

    $user = null;
    $user = FrcPortal\User::with(['school']) //,'user_categories'
            ->where(function ($query) use ($email) {
              $query->where('email', $email)
                    ->orWhere('team_email', $email);
            })
            ->where('password', hash('sha512',$password))
            ->whereNotNull('password')
            ->where('status',true)
            ->first();
    if($user != null) {
      $jwt = $user->generateUserJWT();
      $responseData = array('status'=>true, 'msg'=>'Login Successful', 'token'=>$jwt, 'userInfo' => $user);
      FrcPortal\Auth::setCurrentUser($user->user_id);
      insertLogs($level = 'Information', $message = $user->full_name.' successfully logged in using local credentials.');
    } else {
      $responseData = array('status'=>false, 'msg'=>'Username or Password not correct. Please try again.');
      insertLogs($level = 'Information', $message = $email.' attempted to login using local credentials. Username or Password not correct.');
    }
    $response = $response->withJson($responseData);
    return $response;
  })->setName('Local Login');
});
















?>

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
    $client->setClientSecret(decryptItems(getSettingsProp('google_oauth_client_secret')));
    $client->setRedirectUri(getSettingsProp('env_url').'/oauth/google');
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
    if(FrcPortal\Auth::isAuthenticated()) {
      $auth_user = FrcPortal\Auth::user();
      if($user != false && $user->user_id != $auth_user->user_id) {
        $responseData = array('status'=>false, 'msg'=>'Google account is already linked to another user');
        insertLogs($level = 'Information', $message = $auth_user->full_name.' attempted to link Google account '.$userData['email'].' to their profile.  Account is linked to another user.');
      } else {
        $provider = $userData['provider'];
      	$id = $userData['id'];
      	$email = $userData['email'];
        $oauth = FrcPortal\Oauth::updateOrCreate(['oauth_id' => $id, 'oauth_provider' => strtolower($provider)], ['user_id' => $auth_user->user_id, 'oauth_user' => $email]);
          $responseData = array('status'=>false, 'msg'=>'Google account linked');
          insertLogs($level = 'Information', $message = $auth_user->full_name.' linked Google account '.$userData['email'].' to their profile.');
      }
    } else {
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
    $clientId = getSettingsProp('facebook_oauth_client_id');
    $secret = decryptItems(getSettingsProp('facebook_oauth_client_secret'));
    $redirect = getSettingsProp('env_url').'/oauth/facebook';
    $fb = new Facebook\Facebook([
      'app_id'  => getSettingsProp('facebook_oauth_client_id'),
      'app_secret' => $secret,
    	'default_graph_version' => 'v3.2',
    ]);
    $fbc = $fb->getOAuth2Client();
    $access = $fbc->getAccessTokenFromCode($args['code'],$redirect);
    $accessToken = $access->getValue();
    try {
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
        if(FrcPortal\Auth::isAuthenticated()) {
          $auth_user = FrcPortal\Auth::user();
          if($user != false && $user->user_id != $auth_user->user_id) {
            $responseData = array('status'=>false, 'msg'=>'Facebook account is already linked to another user');
            insertLogs($level = 'Information', $message = $auth_user->full_name.' attempted to link Facebook account '.$userData['email'].' to their profile.  Account is linked to another user.');
          } else {
            $provider = $userData['provider'];
          	$id = $userData['id'];
          	$email = $userData['email'];
            $oauth = FrcPortal\Oauth::updateOrCreate(['oauth_id' => $id, 'oauth_provider' => strtolower($provider)], ['user_id' => $auth_user->user_id, 'oauth_user' => $email]);
              $responseData = array('status'=>false, 'msg'=>'Facebook account linked');
              insertLogs($level = 'Information', $message = $auth_user->full_name.' linked Facebook account '.$userData['email'].' to their profile.');
          }
        } else {
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
  $this->post('/microsoft', function ($request, $response) {
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
    $secret = decryptItems(getSettingsProp('microsoft_oauth_client_secret'));
    $clientId =  getSettingsProp('microsoft_oauth_client_id');
    $redirect = getSettingsProp('env_url').'/oauth/microsoft';
    $client = new GuzzleHttp\Client(['base_uri' => 'https://login.microsoftonline.com/common/oauth2/v2.0/']);
    $params = array(
  		'client_id'=>$clientId,
  		'scope'=>'openid email profile User.Read', // User.Read User.ReadBasic.All
  		'code'=>$args['code'],
  		'redirect_uri'=>$redirect,
  		'grant_type'=>'authorization_code',
  		'client_secret'=>$secret,
  	);
    $result = $client->request('POST', 'token', array(
      'form_params' => $params,
      'headers' => array("Content-Type: application/x-www-form-urlencoded","Accept: application/json")
    ));
    $code = $result->getStatusCode(); // 200
    $reason = $result->getReasonPhrase(); // OK
    $body = $result->getBody();
    $accessTokenArr = (array) json_decode($body, true);
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
    if(FrcPortal\Auth::isAuthenticated()) {
      $auth_user = FrcPortal\Auth::user();
      if($user != false && $user->user_id != $auth_user->user_id) {
        $responseData = array('status'=>false, 'msg'=>'Microsoft account is already linked to another user');
        insertLogs($level = 'Information', $message = $auth_user->full_name.' attempted to link Microsoft account '.$userData['email'].' to their profile.  Account is linked to another user.');
      } else {
        $provider = $userData['provider'];
        $id = $userData['id'];
        $email = $userData['email'];
        $oauth = FrcPortal\Oauth::updateOrCreate(['oauth_id' => $id, 'oauth_provider' => strtolower($provider)], ['user_id' => $auth_user->user_id, 'oauth_user' => $email]);
          $responseData = array('status'=>false, 'msg'=>'Microsoft account linked');
          insertLogs($level = 'Information', $message = $auth_user->full_name.' linked Microsoft account '.$userData['email'].' to their profile.');
      }
    } else {
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
    $secret = decryptItems(getSettingsProp('amazon_oauth_client_secret'));
    $clientId =  getSettingsProp('amazon_oauth_client_id');
    $redirect = getSettingsProp('env_url').'/oauth/amazon';
    $client = new GuzzleHttp\Client(['base_uri' => 'https://api.amazon.com/']);
    $params = array(
      'client_id'=>$clientId,
      'code'=>$args['code'],
      'redirect_uri'=>$redirect,
      'grant_type'=>'authorization_code',
      'client_secret'=>$secret,
    );
  	$result = $client->request('POST', 'auth/o2/token', array(
  		'form_params' => $params,
      'headers' => array('Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8')
  	));
  	$code = $result->getStatusCode(); // 200
  	$reason = $result->getReasonPhrase(); // OK
  	$body = $result->getBody();
    $accessTokenArr = (array) json_decode($body, true);
    $accessToken = $accessTokenArr['access_token'];

    $headers = array(
      'Authorization' => 'Bearer '.$accessToken,
      'Accept' => 'application/json',
      'Accept-Language' => 'en-US'
    );
    $result = $client->request('GET', 'user/profile', array('headers' => $headers));
    $code = $result->getStatusCode(); // 200
    $reason = $result->getReasonPhrase(); // OK
    $body = $result->getBody();
    $me = (array) json_decode($body, true);
    $userData = formatAmazonLoginUserData($me);
    if(checkTeamLogin($userData['email'])) {
      $teamDomain = getSettingsProp('team_domain');
      insertLogs($level = 'Warning', $message = $userData['email'].' attempted to login using Amazon OAuth2. A '.$teamDomain.' email is required.');
      return unauthorizedResponse($response, $msg = 'A '.$teamDomain.' email is required');
    }

    $user = checkLogin($userData);
    if(FrcPortal\Auth::isAuthenticated()) {
      $auth_user = FrcPortal\Auth::user();
      if($user != false && $user->user_id != $auth_user->user_id) {
        $responseData = array('status'=>false, 'msg'=>'Amazon account is already linked to another user');
        insertLogs($level = 'Information', $message = $auth_user->full_name.' attempted to link Amazon account '.$userData['email'].' to their profile.  Account is linked to another user.');
      } else {
        $provider = $userData['provider'];
        $id = $userData['id'];
        $email = $userData['email'];
        $oauth = FrcPortal\Oauth::updateOrCreate(['oauth_id' => $id, 'oauth_provider' => strtolower($provider)], ['user_id' => $auth_user->user_id, 'oauth_user' => $email]);
          $responseData = array('status'=>false, 'msg'=>'Amazon account linked');
          insertLogs($level = 'Information', $message = $auth_user->full_name.' linked Amazon account '.$userData['email'].' to their profile.');
      }
    } else {
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
    $secret = decryptItems(getSettingsProp('github_oauth_client_secret'));
    $clientId =  getSettingsProp('github_oauth_client_id');
    $redirect = getSettingsProp('env_url').'/oauth/github';

    $client = new GuzzleHttp\Client(['base_uri' => 'https://github.com/login/oauth/']);
    $params = array(
      'client_id'=>$clientId,
      'code'=>$args['code'],
      'redirect_uri'=>$redirect,
      'client_secret'=>$secret,
    );
    $result = $client->request('POST', 'access_token', array(
      'form_params' => $params,
      'headers' => array("Content-Type"=>"application/x-www-form-urlencoded","Accept"=>"application/json")
    ));
    $code = $result->getStatusCode(); // 200
    $reason = $result->getReasonPhrase(); // OK
    $body = $result->getBody();
    $accessTokenArr = (array) json_decode($body, true);
    $accessToken = $accessTokenArr['access_token'];

    //$params = array('access_token'=>$accessToken);
  	$client = new GuzzleHttp\Client(['base_uri' => 'https://api.github.com/','headers' => array('Authorization'=>'token '.$accessToken)]);
  	$result = $client->request('GET', 'user', array(
  		//'query' => $params
  	));
  	$code = $result->getStatusCode(); // 200
  	$reason = $result->getReasonPhrase(); // OK
  	$body = $result->getBody();
    $me = (array) json_decode($body, true);
    $userData = formatGithubLoginUserData($me);
    if(checkTeamLogin($userData['email'])) {
      $teamDomain = getSettingsProp('team_domain');
      insertLogs($level = 'Warning', $message = $userData['email'].' attempted to login using Github OAuth2. A '.$teamDomain.' email is required.');
      return unauthorizedResponse($response, $msg = 'A '.$teamDomain.' email is required');
    }

    $user = checkLogin($userData);
    if(FrcPortal\Auth::isAuthenticated()) {
      $auth_user = FrcPortal\Auth::user();
      if($user != false && $user->user_id != $auth_user->user_id) {
        $responseData = array('status'=>false, 'msg'=>'Github account is already linked to another user');
        insertLogs($level = 'Information', $message = $auth_user->full_name.' attempted to link Github account '.$userData['email'].' to their profile.  Account is linked to another user.');
      } else {
        $provider = $userData['provider'];
        $id = $userData['id'];
        $email = is_null($userData['email']) ? $me['login']:$userData['email'];
        $oauth = FrcPortal\Oauth::updateOrCreate(['oauth_id' => $id, 'oauth_provider' => strtolower($provider)], ['user_id' => $auth_user->user_id, 'oauth_user' => $email]);
          $responseData = array('status'=>false, 'msg'=>'Github account linked');
          insertLogs($level = 'Information', $message = $auth_user->full_name.' linked Github account '.$userData['email'].' to their profile.');
      }
    } else {
      if($user == false && is_null($userData['email'])) {
        $result = $client->request('GET', 'user/emails', array(
          //'query' => $params
        ));
        $code = $result->getStatusCode(); // 200
        $reason = $result->getReasonPhrase(); // OK
        $body = $result->getBody();
        $emails = (array) json_decode($body, true);
        $userData2 = $userData;
        foreach($emails as $email) {
          if($email['verified']) {
            $userData2['email'] = $email['email'];
            $user = checkLogin($userData2);
            if($user != false) {
              break;
            }
          }
        }
      }
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
    }
    $response = $response->withJson($responseData);
    return $response;
  })->setName('Github OAuth2');
  $this->post('/discord', function ($request, $response) {
    $responseData = false;
    $args = $request->getParsedBody();
    $provider = 'discord';
    if(checkLoginProvider($provider) == false) {
      insertLogs($level = 'Warning', $message = 'Attempted login with Discord OAuth2.  Github login provider not enabled.');
      return badRequestResponse($response, $msg = 'Discord login is not enabled.  Please select a different option.');
    }
    if(!isset($args['code']) || $args['code'] == '') {
      insertLogs($level = 'Warning', $message = 'Invalid code from Discord OAuth2 sign in.');
      return badRequestResponse($response, $msg = 'Invalid code from Discord Sign In');
    }
    $secret = decryptItems(getSettingsProp('discord_oauth_client_secret'));
    $clientId =  getSettingsProp('discord_oauth_client_id');
    $redirect = getSettingsProp('env_url').'/oauth/discord';

    $client = new GuzzleHttp\Client(['base_uri' => 'https://discordapp.com/api/']);
    $params = array(
      'client_id'=>$clientId,
      'code'=>$args['code'],
      'redirect_uri'=>$redirect,
      'client_secret'=>$secret,
      'grant_type'=>'authorization_code',
  		'scope'=>'idetify email',
    );
    $result = $client->request('POST', 'oauth2/token', array(
      'form_params' => $params,
      'headers' => array("Content-Type"=>"application/x-www-form-urlencoded","Accept"=>"application/json")
    ));
    $code = $result->getStatusCode(); // 200
    $reason = $result->getReasonPhrase(); // OK
    $body = $result->getBody();
    $accessTokenArr = (array) json_decode($body, true);
    $accessToken = $accessTokenArr['access_token'];
    $headers = array(
      'Authorization' => 'Bearer '.$accessToken,
      'Accept' => 'application/json',
      'Accept-Language' => 'en-US'
    );
    $result = $client->request('GET', 'users/@me', array('headers' => $headers));
    $code = $result->getStatusCode(); // 200
    $reason = $result->getReasonPhrase(); // OK
    $body = $result->getBody();
    $me = (array) json_decode($body, true);
    $userData = formatDiscordLoginUserData($me);
    if(checkTeamLogin($userData['email'])) {
      $teamDomain = getSettingsProp('team_domain');
      insertLogs($level = 'Warning', $message = $userData['email'].' attempted to login using Discord OAuth2. A '.$teamDomain.' email is required.');
      return unauthorizedResponse($response, $msg = 'A '.$teamDomain.' email is required');
    }

    $user = checkLogin($userData);
    if(FrcPortal\Auth::isAuthenticated()) {
      $auth_user = FrcPortal\Auth::user();
      if($user != false && $user->user_id != $auth_user->user_id) {
        $responseData = array('status'=>false, 'msg'=>'Discord account is already linked to another user');
        insertLogs($level = 'Information', $message = $auth_user->full_name.' attempted to link Discord account '.$userData['email'].' to their profile.  Account is linked to another user.');
      } else {
        $provider = $userData['provider'];
        $id = $userData['id'];
        $email = $userData['email'];
        $oauth = FrcPortal\Oauth::updateOrCreate(['oauth_id' => $id, 'oauth_provider' => strtolower($provider)], ['user_id' => $auth_user->user_id, 'oauth_user' => $email]);
          $responseData = array('status'=>false, 'msg'=>'Discord account linked');
          insertLogs($level = 'Information', $message = $auth_user->full_name.' linked Discord account '.$userData['email'].' to their profile.');
      }
    } else {
      if($user != false) {
        $user->updateUserOnLogin($userData);
        $jwt = $user->generateUserJWT();
        $responseData = array('status'=>true, 'msg'=>'Login with Discord Account Successful', 'token'=>$jwt, 'userInfo' => $user);
        FrcPortal\Auth::setCurrentUser($user->user_id);
        insertLogs($level = 'Information', $message = $user->full_name.' successfully logged in using Discord OAuth2.');
      } else {
        $teamNumber = getSettingsProp('team_number');
        $responseData = array('status'=>false, 'msg'=>'Amazon account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team '.$teamNumber.' Google form.');
        insertLogs($level = 'Information', $message = $userData['email'].' attempted to log in using Discord OAuth2. Microsoft account not linked to any current portal user.');
      }
    }

    $response = $response->withJson($responseData);
    return $response;
  })->setName('Discord OAuth2');
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
  $this->post('/admin', function ($request, $response) {
    $responseData = false;
    $formData = $request->getParsedBody();
    $provider = 'local_admin';

    if(!isset($formData['user']) || $formData['user'] == '') {
      return badRequestResponse($response, $msg = 'Username is required');
    }
    if(!isset($formData['password']) || $formData['password'] == '') {
      return badRequestResponse($response, $msg = 'Password is required');
    }
    $username = $formData['user'];
    $password = $formData['password'];
    if($username == getIniProp('admin_user') && hash('sha512',$password) == getIniProp('admin_pass')) {
      $user = localAdminModel();
      $jwt = $user->generateUserJWT();
      $responseData = array('status'=>true, 'msg'=>'Login Successful', 'token'=>$jwt, 'userInfo' => $user);
      FrcPortal\Auth::setCurrentUser($user->user_id);
      insertLogs($level = 'Information', $message = $user->full_name.' successfully logged in using local credentials.');
    } else {
      //$responseData = $formData;
      $responseData = array('status'=>false, 'msg'=>'Username or Password not correct. Please try again.', );
      insertLogs($level = 'Information', $message = $username.' attempted to login using local credentials. Username or Password not correct.');
    }
    $response = $response->withJson($responseData);
    return $response;
  })->setName('Local Login');
});
use MadWizard\WebAuthn\Server\UserIdentity;
use MadWizard\WebAuthn\Dom\AuthenticatorSelectionCriteria;
use MadWizard\WebAuthn\Server\Registration\RegistrationOptions;
use MadWizard\WebAuthn\Server\Registration\RegistrationContext;
use MadWizard\WebAuthn\Format\ByteBuffer;
use MadWizard\WebAuthn\Credential\UserHandle;
use MadWizard\WebAuthn\Config\WebAuthnConfiguration;
use MadWizard\WebAuthn\Server\WebAuthnServer;
use MadWizard\WebAuthn\Server\Authentication\AuthenticationOptions;
use MadWizard\WebAuthn\Server\Authentication\AuthenticationContext;
use MadWizard\WebAuthn\Credential\CredentialId;

$app->group('/webauthn', function () {
  $this->get('/register', function ($request, $response) {
    $responseData = false;
    $user = FrcPortal\Auth::user();
    $formData = $request->getParsedBody();
    $provider = 'webauthn';
    // Get user identity. Note that the userHandle should be a unique identifier for each user
    // (max 64 bytes). The WebAuthn specs recommend generating a random byte sequence for each
    // user. The code below is just for testing purposes!
    $userId = new UserIdentity(UserHandle::fromBuffer(new ByteBuffer($user->user_id)), $user->user_id, $user->full_name);
    // Setup options
    $options = new RegistrationOptions($userId);
    $options->setAttestation('none');
    $options->setExcludeExistingCredentials(true);
    $criteria = new AuthenticatorSelectionCriteria();
    $criteria->setAuthenticatorAttachment('platform');
    $criteria->setUserVerification('preferred');
    $options->setAuthenticatorSelection($criteria);
    $config = new WebAuthnConfiguration();
    $rpId = getSettingsProp('env_url');
    $config->setRelyingPartyId(preg_replace('#^https?://#', '', rtrim($rpId,'/')));
    $config->setRelyingPartyName('FRC Portal');
    $config->setRelyingPartyOrigin($rpId);
    $credentialStore = new FrcPortal\CredentialStore();
    $server = new WebAuthnServer($config,$credentialStore);
    // Get array with configuration for webauthn client
    $clientOptions = $server->startRegistration($options);
    $opts = $clientOptions->getClientOptionsJson();
    if($user->user_id != getIniProp('admin_user')) {
      $user1 = FrcPortal\User::find($user->user_id);
      if(!is_null($user1)) {
        $user1->webauthn_challenge = $opts['challenge'];
        $user1->save();
      }
    }
    $response = $response->withJson($opts);
    return $response;
  })->setName('Webauthn Register');
  $this->post('/register', function ($request, $response) {
    $responseData = false;
    $user = FrcPortal\Auth::user();
    $formData = $request->getParsedBody();
    $provider = 'webauthn';
    $userId = new UserIdentity(UserHandle::fromBuffer(new ByteBuffer($user->user_id)), $user->user_id, $user->full_name);
    // Setup options
    $options = new RegistrationOptions($userId);
    $options->setAttestation('none');
    $options->setExcludeExistingCredentials(true);
    $criteria = new AuthenticatorSelectionCriteria();
    $criteria->setAuthenticatorAttachment('platform');
    $criteria->setUserVerification('preferred');
    $options->setAuthenticatorSelection($criteria);
    $config = new WebAuthnConfiguration();
    $rpId = getSettingsProp('env_url');
    $config->setRelyingPartyId(preg_replace('#^https?://#', '', rtrim($rpId,'/')));
    $config->setRelyingPartyName('FRC Portal');
    $config->setRelyingPartyOrigin($rpId);
    $credentialStore = new FrcPortal\CredentialStore();
    $server = new WebAuthnServer($config,$credentialStore);
    if($user->user_id != getIniProp('admin_user')) {
      $user1 = FrcPortal\User::find($user->user_id);
      if(is_null($user1) || is_null($user1->webauthn_challenge) || $user1->webauthn_challenge == '') {
        //TODO: Error out
      }
    }
    $context = new RegistrationContext(new ByteBuffer($user1->webauthn_challenge), $config->getRelyingPartyOrigin(), $config->getRelyingPartyId(), UserHandle::fromBuffer(new ByteBuffer($user->user_id)));
    $result = $server->finishRegistration(json_encode($formData), $context);
    $credential = FrcPortal\UserCredential::where('credential_id',$formData['id'])->first();
    $credential->name = isset($formData['name']) ? $formData['name'] : null;
    $credential->platform = isset($formData['platform']) ? $formData['platform'] : null;
    $credential->save();
    $responseArr = array(
      'status' => true,
      'msg' => 'Registration complete',
      'data' => array(
        'credential_id' => $formData['id'],
        'type' => 'public-key',
        'user' => $user->user_id,
      )
    );
    $response = $response->withJson($responseArr);
    return $response;
  })->setName('Webauthn Register');
  $this->get('/authenticate/{user_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $responseData = false;
    $user = FrcPortal\Auth::user();
    $formData = $request->getParsedBody();
    $user_id = $args['user_id'];
    $provider = 'webauthn';
    // Setup options
    $options = new AuthenticationOptions();
    $options->setUserVerification('preferred');
    $credentialStore = new FrcPortal\CredentialStore();
    $credentials = $credentialStore->getUserCredentialIds(UserHandle::fromBuffer(new ByteBuffer($user_id)));
    foreach($credentials as $cred) {
      //$options->addAllowCredential($cred);
    }
    $config = new WebAuthnConfiguration();
    $rpId = getSettingsProp('env_url');
    $config->setRelyingPartyId(preg_replace('#^https?://#', '', rtrim($rpId,'/')));
    $config->setRelyingPartyName('FRC Portal');
    $config->setRelyingPartyOrigin($rpId);
    $server = new WebAuthnServer($config,$credentialStore);
    // Get array with configuration for webauthn client
    $clientOptions = $server->startAuthentication($options);
    $opts = $clientOptions->getClientOptionsJson();
    if($user_id != getIniProp('admin_user')) {
      $user1 = FrcPortal\User::find($user_id);
      if(!is_null($user1)) {
        $user1->webauthn_challenge = $opts['challenge'];
        $user1->save();
      }
    }
    $response = $response->withJson($opts);
    return $response;
  })->setName('Webauthn Register');
  $this->post('/authenticate', function ($request, $response) {
    $responseData = false;
    $user = FrcPortal\Auth::user();
    $formData = $request->getParsedBody();
    $provider = 'webauthn';
    // Setup options
    $options = new AuthenticationOptions();
    $options->setUserVerification('preferred');
    $config = new WebAuthnConfiguration();
    $rpId = getSettingsProp('env_url');
    $config->setRelyingPartyId(preg_replace('#^https?://#', '', rtrim($rpId,'/')));
    $config->setRelyingPartyName('FRC Portal');
    $config->setRelyingPartyOrigin($rpId);
    $credentialStore = new FrcPortal\CredentialStore();
    $server = new WebAuthnServer($config,$credentialStore);
    // Get array with configuration for webauthn client
    $userId = $formData['response']['userHandle'] != '' ? base64_decode($formData['response']['userHandle']) : false;
    if($userId == false) {
      $credential = $credentialStore->findCredential(CredentialId::fromString($formData['id']));
      $userId = $credential->getUserHandle()->toBinary();
      unset($formData['response']['userHandle']);
    }
    if($userId != false && $userId != getIniProp('admin_user')) {
      $user = FrcPortal\User::find($userId);
      if(is_null($user) || is_null($user->webauthn_challenge) || $user->webauthn_challenge == '') {
        $responseData = array('status'=>false, 'msg'=>'Login with WebAuthn Failed. Use another login method.');
        $response = $response->withJson($responseData);
        return $response;
      }
    }
    $context = new AuthenticationContext(new ByteBuffer($user->webauthn_challenge), $config->getRelyingPartyOrigin(), $config->getRelyingPartyId(), UserHandle::fromBuffer(new ByteBuffer($userId)));
    try {
      $result = $server->finishAuthentication(json_encode($formData), $context);
    } catch (MadWizard\WebAuthn\Exception\VerificationException $e) {
      if($e->getMessage() == 'Account was not found') {
        $responseData = array('status'=>false, 'msg'=>'Login with WebAuthn Failed. Use another login method.', 'error' => $e->getMessage(), 'badCredential' => true);
        $response = $response->withJson($responseData);
        return $response;
      }
    }
    if($user != false) {
      $jwt = $user->generateUserJWT();
      $responseData = array('status'=>true, 'msg'=>'Login with WebAuthn Successful', 'token'=>$jwt, 'userInfo' => $user);
      FrcPortal\Auth::setCurrentUser($user->user_id);
      insertLogs($level = 'Information', $message = $user->full_name.' successfully logged in using WebAuthn.');
    }
    $response = $response->withJson($responseData);
    return $response;
  })->setName('Webauthn Register');
});















?>

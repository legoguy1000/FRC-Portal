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
      return badRequestResponse($response, $msg = 'Google login is not enabled.  Please select a different option.');
    }
    $client = new Google_Client();
    //$client->setAuthConfigFile(__DIR__.'/../secured/google_client_secret.json');
    $client->setClientId(getSettingsProp('google_oauth_client_id'));
    $client->setClientSecret(getSettingsProp('google_oauth_client_secret'));
    $client->setRedirectUri(getSettingsProp('env_url').'/oauth');
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
        $responseData = array('status'=>true, 'msg'=>'Login with Google Account Successful', 'token'=>$jwt, 'userInfo' => $user);
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
    if(checkLoginProvider($provider) == false) {
      return badRequestResponse($response, $msg = 'Facebook login is not enabled.  Please select a different option.');
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
    	'default_graph_version' => 'v3.0',
    ]);
    //$helper = $fb->getRedirectLoginHelper();
    try {
      //$accessToken = $helper->getAccessToken();
      $data = array();
      $FBresponse = $fb->get('/me?locale=en_US&fields=first_name,last_name,name,email,picture', $accessToken);
    	$me = $FBresponse->getGraphUser();
      if(isset($me['email']) || $me['email'] != '') {
        $email = $me['email'];
      	$fname = $me['first_name'];
      	$lname = $me['last_name'];
      	$image = $me['picture']['data']['url'];
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
          $responseData = array('status'=>true, 'msg'=>'Login with Facebook Account Successful', 'token'=>$jwt, 'userInfo' => $me);
        } else {
          $responseData = array('status'=>false, 'msg'=>'Facebook account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team 2363 Join form.', 'me' => $me);
        }
    	} else {
        $responseData = array('status'=>false, 'msg'=>'No email address provided by Facebook OAuth2');
      }
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      $responseData = array('status'=>false, 'type'=>'error', 'msg'=>'Facebook Login Error', 'error'=>$e->getMessage());
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      $responseData = array('status'=>false, 'type'=>'error', 'msg'=>'Facebook Login Error', 'error'=>$e->getMessage());
    }
    $response = $response->withJson($responseData);
    return $response;
  });
  $this->post('/live', function ($request, $response) {
    $responseData = false;
    $args = $request->getParsedBody();
    $provider = 'microsoft';
    if(checkLoginProvider($provider) == false) {
      return badRequestResponse($response, $msg = 'Microsoft login is not enabled.  Please select a different option.');
    }
    //$secret = getIniProp('microsoft_client_secret');
    $secret = getSettingsProp('microsoft_oauth_client_secret');
//    $clientId = '027f5fe4-87bb-4731-8284-6d44da287677';
    $clientId =  getSettingsProp('microsoft_oauth_client_id');
    $redirect = getSettingsProp('env_url').'/oauth';
    if(isset($args['code'])) {
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

    	$email = $me['userPrincipalName'];
    	$fname = $me['givenName'];
    	$lname = $me['surname'];
    	$image = ''; //$me['image']['url'];\
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
        $responseData = array('status'=>true, 'msg'=>'Login with Microsoft Account Successful', 'token'=>$jwt, 'userInfo' => $user);
      } else {
        $responseData = array('status'=>false, 'msg'=>'Microsoft account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team 2363 Join form.', 'me' => $me);
      }
    }
    $response = $response->withJson($responseData);
    return $response;
  });
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
        $responseData = array('status'=>false, 'msg'=>'Slack account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team 2363 Join form.');
      }
    }
    $response = $response->withJson($responseData);
    return $response;
  }); */
  $this->post('/login', function ($request, $response) {
    $responseData = false;
    $formData = $request->getParsedBody();
    $provider = 'local';
    if(checkLoginProvider($provider) == false) {
      return badRequestResponse($response, $msg = 'Local login is not enabled.  Please select a different option.');
    }

    $email = $formData['email'];
    $password = $formData['password'];
    $require_team_email = getSettingsProp('require_team_email');
    if(checkTeamLogin($email)) {
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
			$jwt = generateUserJWT($user);
      $responseData = array('status'=>true, 'msg'=>'Login Successful', 'token'=>$jwt, 'userInfo' => $user);
    } else {
      $responseData = array('status'=>false, 'msg'=>'Username or Password not correct. Please try again.');
    }
    $response = $response->withJson($responseData);
    return $response;
  });
});
















?>

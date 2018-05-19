<?php
use \Firebase\JWT\JWT;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
$app->group('/auth', function () {
  $this->post('/google', function ($request, $response) {
    $responseData = false;
    $args = $request->getParsedBody();
    $provider = 'google';
    $loginEnabled = FrcPortal\Setting::where('section','login')->where('setting','google_login_enable')->first();
    if(is_null($loginEnabled) || ((boolean) $loginEnabled->value) == false) {
      $responseData = array('status'=>false, 'msg'=>'Google login is not enabled.  Please select a different option.');
      $response = $response->withJson($responseArr,400);
      return $response;
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

      $user = false;
      $data = FrcPortal\Oauth::with(['users.school','users' => function($q){
        $q->where('status',true);
      }])->where('oauth_id', $id)->where('oauth_provider', $provider)->first();
      if($data != null) {
        $user = $data->users;
      } else {
        $data = FrcPortal\User::with(['school'])
                ->where(function ($query) {
                  $query->where('email', $userData['email'])
                        ->orWhere('team_email', $userData['email']);
                })
                ->where('status',true)
                ->first();
        if($data != null) {
          $user = $data;
        }
        if($user != false) {
          $oauth = FrcPortal\Oauth::updateOrCreate(
              ['oauth_id' => $id, 'oauth_provider' => $provider], ['user_id' => $user->user_id, 'oauth_user' => $email]
          );
        }
      }
      if($user != false) {
        $update = false;
        if($user->profile_image == '') {
          $user->profile_image = $userData['profile_image'];
          $update = true;
        }
        $teamDomain = getSettingsProp('team_domain');
        if($user->team_email == '' && !is_null($teamDomain) && strpos($userData['email'],'@'.$teamDomain) !== false) {
          $user->team_email = $userData['email'];
          $update = true;
        }
        if($update == true) {
          $user = $user->save();
        }
        $key = getSettingsProp('jwt_key');
  			$token = array(
  				"iss" => getSettingsProp('env_url'),
  				"iat" => time(),
  				"exp" => time()+60*60,
  				"jti" => bin2hex(random_bytes(10)),
  				'data' => array(
            'user_id' => $user->user_id,
            'full_name' => $user->full_name,
            'admin' => $user->admin,
            'status' => $user->status,
            'user_type' => $user->user_type,
            'email' => $user->email,
          )
  			);
  			$jwt = JWT::encode($token, $key);
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
    $loginEnabled = FrcPortal\Setting::where('section','login')->where('setting','facebook_login_enable')->first();
    if(is_null($loginEnabled) || ((boolean) $loginEnabled->value) == false) {
      $responseData = array('status'=>false, 'msg'=>'Facebook login is not enabled.  Please select a different option.');
      $response = $response->withJson($responseArr,400);
      return $response;
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
        $user = false;
        $data = FrcPortal\Oauth::with(['users.school','users' => function($q){
          $q->where('status',true);
        }])->where('oauth_id', $id)->where('oauth_provider', $provider)->first();
        if($data != null) {
          $user = $data->users;
        } else {
          $data = FrcPortal\User::with(['school'])
                  ->where(function ($query) {
                    $query->where('email', $userData['email'])
                          ->orWhere('team_email', $userData['email']);
                  })
                  ->where('status',true)
                  ->first();
          if($data != null) {
            $user = $data;
          }
          if($user != false) {
            $oauth = FrcPortal\Oauth::updateOrCreate(
                ['oauth_id' => $id, 'oauth_provider' => $provider], ['user_id' => $user->user_id, 'oauth_user' => $email]
            );
          }
        }
        if($user != false) {
          $update = false;
          if($user->profile_image == '') {
            $user->profile_image = $userData['profile_image'];
            $update = true;
          }
          $teamDomain = getSettingsProp('team_domain');
          if($user->team_email == '' && !is_null($teamDomain) && strpos($userData['email'],'@'.$teamDomain) !== false) {
            $user->team_email = $userData['email'];
            $update = true;
          }
          if($update == true) {
            $user = $user->save();
          }
          $key = getSettingsProp('jwt_key');
    			$token = array(
    				"iss" => getSettingsProp('env_url'),
    				"iat" => time(),
    				"exp" => time()+60*60,
    				"jti" => bin2hex(random_bytes(10)),
    				'data' => array(
              'user_id' => $user->user_id,
              'full_name' => $user->full_name,
              'admin' => $user->admin,
              'status' => $user->status,
              'user_type' => $user->user_type,
              'email' => $user->email,
            )
    			);
    			$jwt = JWT::encode($token, $key);
          $responseData = array('status'=>true, 'msg'=>'Login with Facebook Account Successful', 'token'=>$jwt, 'userInfo' => $user);
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
    $loginEnabled = FrcPortal\Setting::where('section','login')->where('setting','microsoft_login_enable')->first();
    if(is_null($loginEnabled) || ((boolean) $loginEnabled->value) == false) {
      $responseData = array('status'=>false, 'msg'=>'Microsoft login is not enabled.  Please select a different option.');
      $response = $response->withJson($responseArr,400);
      return $response;
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

      $user = false;
      $data = array();
      $data = FrcPortal\Oauth::with(['users.school','users' => function($q){
        $q->where('status',true);
      }])->where('oauth_id', $id)->where('oauth_provider', $provider)->first();
      if($data != null) {
        $user = $data->users;
      } else {
        $data = FrcPortal\User::with(['school'])
                ->where(function ($query) {
                  $query->where('email', $userData['email'])
                        ->orWhere('team_email', $userData['email']);
                })
                ->where('status',true)
                ->first();
        if($data != null) {
          $user = $data;
        }
        if($user != false) {
          $oauth = FrcPortal\Oauth::updateOrCreate(
              ['oauth_id' => $id, 'oauth_provider' => $provider], ['user_id' => $user->user_id, 'oauth_user' => $email]
          );
        }
      }
      if($user != false) {
        $update = false;
        if($user->profile_image == '') {
          $user->profile_image = $userData['profile_image'];
          $update = true;
        }
        $teamDomain = getSettingsProp('team_domain');
        if($user->team_email == '' && !is_null($teamDomain) && strpos($userData['email'],'@'.$teamDomain) !== false) {
          $user->team_email = $userData['email'];
          $update = true;
        }
        if($update == true) {
          $user = $user->save();
        }
        $key = getSettingsProp('jwt_key');
  			$token = array(
  				"iss" => getSettingsProp('env_url'),
  				"iat" => time(),
  				"exp" => time()+60*60,
  				"jti" => bin2hex(random_bytes(10)),
  				'data' => array(
            'user_id' => $user->user_id,
            'full_name' => $user->full_name,
            'admin' => $user->admin,
            'status' => $user->status,
            'user_type' => $user->user_type,
            'email' => $user->email,
          )
  			);
  			$jwt = JWT::encode($token, $key);
        $responseData = array('status'=>true, 'msg'=>'Login with Google Account Successful', 'token'=>$jwt, 'userInfo' => $user);
      } else {
        $responseData = array('status'=>false, 'msg'=>'Microsoft account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team 2363 Join form.', 'me' => $me);
      }
    }
    $response = $response->withJson($responseData);
    return $response;
  });
  $this->post('/login', function ($request, $response) {
    $responseData = false;
    $formData = $request->getParsedBody();
    $provider = 'local';
    $loginEnabled = FrcPortal\Setting::where('section','login')->where('setting','local_login_enable')->first();
    if(is_null($loginEnabled) || ((boolean) $loginEnabled->value) == false) {
      $responseData = array('status'=>false, 'msg'=>'Local login is not enabled.  Please select a different option.');
      $response = $response->withJson($responseArr,400);
      return $response;
    }

    $email = $formData['email'];
    $password = $formData['password'];

    $user = null;
    $user = FrcPortal\User::with(['school'])
            ->where(function ($query) use ($email) {
              $query->where('email', $email)
                    ->orWhere('team_email', $email);
            })
            ->where('password', hash('sha512',$password))
            ->where('status',true)
            ->first();
    if($user != null) {
      $key = getSettingsProp('jwt_key');
			$token = array(
				"iss" => getSettingsProp('env_url'),
				"iat" => time(),
				"exp" => time()+60*60,
				"jti" => bin2hex(random_bytes(10)),
				'data' => array(
          'user_id' => $user->user_id,
          'full_name' => $user->full_name,
          'admin' => $user->admin,
          'status' => $user->status,
          'user_type' => $user->user_type,
          'email' => $user->email,
        )
			);
			$jwt = JWT::encode($token, $key);
      $responseData = array('status'=>true, 'msg'=>'Login Successful', 'token'=>$jwt, 'userInfo' => $user);
    } else {
      $responseData = array('status'=>false, 'msg'=>'Username or Password not correct. Please try again.');
    }
    $response = $response->withJson($responseData);
    return $response;
  });
});
















?>

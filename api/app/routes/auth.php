<?php
use \Firebase\JWT\JWT;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
$app->group('/auth', function () {
  $this->post('/google', function ($request, $response) {
    $responseData = false;
    $args = $request->getParsedBody();
    $provider = 'google';
    $client = new Google_Client();
    $client->setAuthConfigFile(__DIR__.'/../secured/google_client_secret.json');
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
        $q->where('status','=','1');
      }])->where('oauth_id', $id)->where('oauth_provider', $provider)->first();
      if($data != null) {
        $user = $data->users;
      } else {
        $data = FrcPortal\User::with(['school'])
                ->where('email', $userData['email'])
                ->orWhere('team_email', $userData['email'])
                ->first();
        if($data != null) {
          $user = $data->users;
        }
        if($user != false) {
          $oauth = FrcPortal\Oauth::firstOrNew(
              ['oauth_id' => $id, 'oauth_provider' => $provider], ['user_id' => $user->user_id, 'oauth_user' => $email]
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
  				"iss" => getIniProp('env_url'),
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
    $accessTokenArr = file_get_contents('https://graph.facebook.com/v3.0/oauth/access_token?client_id='.$args['clientId'].'&redirect_uri='.$args['redirectUri'].'&client_secret='.$secret.'&code='.$args['code']);
    //die($accessTokenArr);
    $accessTokenArr = json_decode($accessTokenArr, true);
    $accessToken = $accessTokenArr['access_token'];
    $fb = new Facebook\Facebook([
      'app_id'  => '1347987445311447',
      'app_secret' => $secret,
    	'default_graph_version' => 'v3.0',
    ]);
    //$helper = $fb->getRedirectLoginHelper();
    try {
      //$accessToken = $helper->getAccessToken();
      $data = array();
      $FBresponse = $fb->get('/me?locale=en_US&fields=first_name,last_name,name,email,picture', $accessToken);
    	$me = $FBresponse->getGraphUser();
      die(json_encode($me));
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
          $q->where('status','=','1');
        }])->where('oauth_id', $id)->where('oauth_provider', $provider)->first();
        if($data != null) {
          $user = $data->users;
        } else {
          $data = FrcPortal\User::with(['school'])
                  ->where('email', $userData['email'])
                  ->orWhere('team_email', $userData['email'])
                  ->first();
          if($data != null) {
            $user = $data->users;
          }
          if($user != false) {
            $oauth = FrcPortal\Oauth::firstOrNew(
                ['oauth_id' => $id, 'oauth_provider' => $provider], ['user_id' => $user->user_id, 'oauth_user' => $email]
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
            "iss" => getIniProp('env_url'),
            "iat" => time(),
            "exp" => time()+60*60,
            "jti" => bin2hex(random_bytes(10)),
            'data' => $user
          );
          $jwt = JWT::encode($token, $key);
          $responseData = array('status'=>true, 'msg'=>'Login with Facebook Account Successful', 'token'=>$jwt, 'me' => $me);
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
    //$secret = getIniProp('microsoft_client_secret');
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
        $q->where('status','=','1');
      }])->where('oauth_id', $id)->where('oauth_provider', $provider)->first();
      if($data != null) {
        $user = $data->users;
      } else {
        $data = FrcPortal\User::with(['school'])
                ->where('email', $userData['email'])
                ->orWhere('team_email', $userData['email'])
                ->first();
        if($data != null) {
          $user = $data->users;
        }
        if($user != false) {
          $oauth = FrcPortal\Oauth::firstOrNew(
              ['oauth_id' => $id, 'oauth_provider' => $provider], ['user_id' => $user->user_id, 'oauth_user' => $email]
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
          "iss" => getIniProp('env_url'),
          "iat" => time(),
          "exp" => time()+60*60,
          "jti" => bin2hex(random_bytes(10)),
          'data' => $user
        );
        $jwt = JWT::encode($token, $key);
        $responseData = array('status'=>true, 'msg'=>'Login with Microsoft Account Successful', 'token'=>$jwt, 'me' => $me);
      } else {
        $responseData = array('status'=>false, 'msg'=>'Microsoft account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team 2363 Join form.', 'me' => $me);
      }
    }
    $response = $response->withJson($responseData);
    return $response;
  });
});
















?>

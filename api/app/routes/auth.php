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
        die('asdf');
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
      }
      $responseData = array('status'=>false, 'msg'=>'Google account not linked to any current portal user.  If this is your first login, please use an account with the email you use to complete the Team 2363 Join form.', 'me' => $me);
    }
    $response = $response->withJson($responseData);
    return $response;
  });
});
















?>

<?php
use \Firebase\JWT\JWT;
$app->group('/auth', function () {
  $this->post('/google', function ($request, $response) {
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

      $data = FrcPortal\Oauth::with(['users.school','users' => function($q){
        $q->where('status','=','1');
      }])->where('oauth_id', $id)->where('oauth_provider', $provider)->limit(1)->get();
    }

    $response = $response->withJson($data);
    return $response;
  });
});
















?>

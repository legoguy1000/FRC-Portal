<?php
use \Firebase\JWT\JWT;
$app->group('/auth', function () {
  $this->post('/google', function ($request, $response, $args) {
    $provider = 'google';
    $client = new Google_Client();
    $client->setAuthConfigFile('./includes/secured/google_client_secret.json');
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

      $data = FrcPortal\User::with(['schools','oauth' => function($q){
        $q->where('oauth_id', $id)->where('oauth_provider', $provider);
      }])->where('status','=',true)->limit(1)->get();
    }

    $response->getBody()->write(json_encode($data));
    return $response;
  });
});
















?>
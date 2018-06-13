<?php
function checkAdmin($user_id) {
	$return = false;
	$user = FrcPortal\User::where('user_id',$user_id)->where('admin',true)->where('status',true)->first();
	if($user) {
		$return = true;
	}
	return $return;
}

function checkLogin($userData) {
	$provider = $userData['provider'];
	$id = $userData['id'];
	$email = $userData['email'];

	$user = false;
	$data = array();
	$data = FrcPortal\Oauth::with(['users.school', 'users' => function($q){ //,'users.user_categories'
		$q->where('status',true);
	}])->where('oauth_id', $id)->where('oauth_provider', $provider)->first();
	if($data != null) {
		$user = $data->users;
	} else {
		$data = FrcPortal\User::with(['school']) //'user_categories'
						->where(function ($query) {
							$query->where('email', $email)
										->orWhere('team_email', $email);
						})
						->where('status',true)
						->first();
		if($data != null) {
			$user = $data;
		}
		if($user != false) {
			$oauth = FrcPortal\Oauth::updateOrCreate(['oauth_id' => $id, 'oauth_provider' => $provider], ['user_id' => $user->user_id, 'oauth_user' => $email]);
		}
	}
	return $user;
}
?>

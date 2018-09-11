<?php
use Illuminate\Database\Capsule\Manager as DB;
function checkAdmin($user) {
	$return = false;
	if($user instanceof FrcPortal\User) {
		$return = $user->status && $user->admin;
	} else {
		$temp = FrcPortal\User::where('user_id',$user)->where('admin',true)->where('status',true)->first();
		$return = !is_null($temp);
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

function getUsersAnnualRequirements($season_id) {
	$season = false;
	if(!is_null($season_id)) {
		$season = FrcPortal\User::with(['annual_requirements' => function ($query) use ($season_id) {
											$query->where('season_id','=',$season_id);
										}])->whereExists(function ($query) use ($season_id) {
											$query->select(DB::raw(1))
														->from('annual_requirements')
														->whereRaw('annual_requirements.user_id = users.user_id AND annual_requirements.season_id = ?',[$season_id]);
										})
										->orWhere('status',true)
										->get();
	}
	return $season;
}

function getGenderByFirstName($name) {
	$return = false;
	if(!is_null($name) && $name != '') {
		$base = 'https://api.genderize.io/';
		$url = $base.'?name='.$name;
		$contents = json_decode(file_get_contents($url),true);
		if(isset($contents['gender']) && !is_null($contents['gender']) && $contents['gender'] != '') {
			$return = $contents['gender'];
		}
	}
	return $return;
}
?>

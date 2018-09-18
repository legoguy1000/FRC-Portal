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

function generateUserJWT(FrcPortal\User $user) {
	/* if(!$user instanceof FrcPortal\User) {
		return false;
	} */
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
	return $jwt;
}

function checkTeamLogin($userEmail = '') {
	$require_team_email = getSettingsProp('require_team_email');
	if($require_team_email) {
		$teamDomain = getSettingsProp('team_domain');
		if(!is_null($teamDomain) && strpos($userEmail,'@'.$teamDomain) === false || $userEmail == '') {
			return true; //Not valid email
		}
	}
	return false;
}

function updateUserOnLogin(FrcPortal\User $user, $userData) {
	/* if(!$user instanceof FrcPortal\User) {
		return false;
	} */
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
	return true;
}

function checkLoginProvider($provider) {
	$loginEnabled = FrcPortal\Setting::where('section','login')->where('setting',$provider.'_login_enable')->first();
	if(is_null($loginEnabled) || ((boolean) $loginEnabled->value) == false) {
		return false;
	}
	return true;
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

function getUsersEventRequirements($event_id) {
	$event = false;
	if(!is_null($event_id)) {
		$event = FrcPortal\User::with(['event_requirements' => function ($query) use ($event_id) {
			  $query->where('event_id','=',$event_id);
			},'event_requirements.event_rooms','event_requirements.event_cars'])
			->whereExists(function ($query) use ($event_id) {
			  $query->select(DB::raw(1))
				->from('event_requirements')
				->whereRaw('event_requirements.user_id = users.user_id AND event_requirements.event_id = ?',[$event_id]);
			})
			->orWhere('status',true)
			->get();
	}
	return $event;
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

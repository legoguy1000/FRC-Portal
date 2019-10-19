<?php
use Illuminate\Database\Capsule\Manager as DB;
use \Firebase\JWT\JWT;
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
	$data = FrcPortal\Oauth::with(['users' => function($q){ //,'users.user_categories'
		$q->where('status',true);
	}])->where('oauth_id', $id)->where('oauth_provider', $provider)->first();
	if($data != null) {
		$data->touch();
		$user = $data->users;
		$user->school();
	} else {
		$data = FrcPortal\User::where(function ($query) use ($email) {
							$query->where('email', $email)
										->orWhere('team_email', $email);
						})
						->where('status',true)
						->first();
		if($data != null) {
			$user = $data;
			$user->school();
		}
		if($user != false) {
			$oauth = FrcPortal\Oauth::updateOrCreate(['oauth_id' => $id, 'oauth_provider' => strtolower($provider)], ['user_id' => $user->user_id, 'oauth_user' => $email]);
		}
	}
	return $user;
}

function checkTeamLogin($userEmail = '') {
	$require_team_email = getSettingsProp('require_team_email');
	if($require_team_email) {
		if(!checkTeamEmail($userEmail) || $userEmail == '') {
			return true; //Not valid email
		}
	}
	return false;
}

function checkTeamEmail($email = '',$teamDomain = null) {
	if($teamDomain == null) {
		$teamDomain = getSettingsProp('team_domain');
	}
	if(!is_null($teamDomain) && preg_match('/[a-z0-9._%+-]+@'.$teamDomain.'$/i', $email) != false) {
		return true;
	}
	return false;
}

function checkLoginProvider($provider) {
	$loginEnabled = FrcPortal\Setting::where('section','login')->where('setting',$provider.'_login_enable')->first();
	if(is_null($loginEnabled) || ((boolean) $loginEnabled->value) == false) {
		return false;
	}
	return true;
}

function getUsersAnnualRequirements($season_id) {
	$users = false;
	if(!is_null($season_id)) {
		$users = FrcPortal\User::with(['annual_requirements' => function ($query) use ($season_id) {
											$query->where('season_id','=',$season_id);
										}])->whereExists(function ($query) use ($season_id) {
											$query->select(DB::raw(1))
														->from('annual_requirements')
														->whereRaw('annual_requirements.user_id = users.user_id')
														->where('annual_requirements.join_team',true)
														->where('annual_requirements.season_id',$season_id);
										})
										->orWhere('status',true)
										->get();
		foreach($users as $user) {
			$user->annual_requirements()->first()->getWeeklyBuildSeasonHoursAttribute();
		}
	}
	return $users;
}

/*
function getUsersEventRequirements($event_id) {
	$event = false;
	if(!is_null($event_id)) {
		$event = FrcPortal\User::with(['event_requirements' => function ($query) use ($event_id) {
			  $query->where('event_id','=',$event_id);
			},'event_requirements.event_rooms','event_requirements.event_cars'])
			->whereExists(function ($query) use ($event_id) {
			  $query->select(DB::raw(1))
				->from('event_requirements')
				->whereRaw('event_requirements.user_id = users.user_id')
				->where('event_requirements.registration',true)
				->where('event_requirements.event_id',$event_id);
			})
			->orWhere('status',true)
			->get();
	}
	return $event;
} */

function localAdminModel() {
	$user = new FrcPortal\User();
	$user->user_id = getIniProp('admin_user');
	$user->email = '';
	$user->fname = 'Local';
	$user->lname = 'Admin';
	$user->full_name = $user->fname.' '.$user->lname;
	$user->admin = true;
	$user->status = true;
	$user->localadmin = true;
	return $user;
}
?>

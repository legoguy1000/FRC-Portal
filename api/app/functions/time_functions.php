<?php
use \Firebase\JWT\JWT;
use Illuminate\Database\Capsule\Manager as DB;
function getSignInList($year = null) {
	if(is_null($year)) {
		$year = date('Y');
	}
	$users = FrcPortal\User::with(['annual_requirements' => function ($query) use ($year)  {
		//$query->leftJoin('seasons', 'annual_requirements.season_id', '=', 'seasons.season_id')->where('seasons.year', $year);
		$query->whereHas('seasons', function ($query) use ($year)  {
			$query->where('year', $year);
		});
	}, 'last_sign_in'])->where(function ($query) {
      $query->where('user_type','Student')->orWhere('user_type','Mentor');
  })->where('status',true)->get();
	/* $data = $users->filter(function ($user) {
		return $user->student || $user->mentor;
	})->all(); */
	return $users;
}

function generateSignInToken($ts = null, $te = null) {
	if(is_null($ts)) {
		$ts = time();
	}
	if(is_null($te)) {
		$te = time()+60*60*12; //12 hours liftime
	}
	$jti = md5(random_bytes(20));
	$key = getSettingsProp('jwt_signin_key');
	$token = array(
		"iss" => getSettingsProp('env_url'),
		"iat" => time(),
		"nbf" => $ts,
		"exp" => $te,
		"jti" => $jti,
		'data' => array(
			'signin' => true
		)
	);
	$jwt = JWT::encode($token, $key);
	//$qr_code = file_get_contents('https://chart.googleapis.com/chart?cht=qr&chl='.$jwt.'&chs=360x360&choe=UTF-8&chld=L|1');
	return array(
		'token' => $jwt,
		'qr_code' => '', //base64_encode($qr_code),
	);
}
 ?>

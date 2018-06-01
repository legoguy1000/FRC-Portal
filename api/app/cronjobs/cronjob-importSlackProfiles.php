<?php
include(__DIR__ . '/../includes.php');


$users = array();
$slack_token = getSettingsProp('slack_api_token');
$url = 'https://slack.com/api/users.list?token='.$slack_token;
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$result = curl_exec($ch);
curl_close($ch);
if($result) {
	$data = (array) json_decode($result, true);
	if(isset($data['members']) && is_array($data['members']) && count($data['members']) > 0) {
		$users = $data['members'];
		foreach($users as $user) {
			$slack_id = $user['id'];
			$profile = $user['profile'];
			$whereArr = array();
			$where = '';
			$validdUser = false;
			if(isset($profile['email']) && $profile['email'] != '') {
				$email = $profile['email'];
				$user = FrcPortal\User::where('email',$email)->orWhere('team_email',$email)->first();
				if(!is_null($user)) {
					$validdUser = true;
					$user->slack_id = $slack_id;
					$user->save();
				}
			}
			if($validdUser == false && isset($profile['first_name']) && $profile['first_name'] != '' && isset($profile['last_name']) && $profile['last_name'] != '') {
				$first_name = $profile['first_name'];
				$last_name = $profile['last_name'];
				$user = FrcPortal\User::where('fname',$first_name)->where('lname',$last_name)->first();
				if(!is_null($user)) {
					$user->slack_id = $slack_id;
					$user->save();
				}
			}
		}
	}
}
?>

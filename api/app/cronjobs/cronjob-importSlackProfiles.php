<?php
include(__DIR__ . '/../includes.php');


$users = array();
$slack_token = getIniProp('slack_api_token');
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
			if(isset($profile['email']) && $profile['email'] != '') {
				$email = $profile['email'];
				$user = FrcPortal\User::where('email',$email)->where('team_email',$email)->update(['slack_id' => $slack_id]);
			}
			if(isset($profile['first_name']) && $profile['first_name'] != '' && isset($profile['last_name']) && $profile['last_name'] != '') {
				$first_name = $profile['first_name'];
				$last_name = $profile['last_name'];
				$user = FrcPortal\User::where('fname',$first_name)->where('lname',$last_name)->update(['slack_id' => $slack_id]);
			}
		}
	}
}
?>

<?php
include('includes.php');

$users = array();
$slack_token = getIniProp('slack_token');
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
			$user_id = $user['id'];
			$profile = $user['profile'];
			if(isset($profile['email']) && $profile['email'] != '') {
				$email = $profile['email'];
				$query = 'UPDATE users SET slack_id = '.db_quote($user_id).' WHERE email='.db_quote($email).' OR team_email='.db_quote($email);
				$result = db_query($query);
			}
		}
	}
}
?>

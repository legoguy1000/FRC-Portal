<?php
require_once(__DIR__ . '/../includes.php');


$users = array();
$slack_enable = getSettingsProp('slack_enable');
if(!is_null($slack_enable) && $slack_enable == true) {
	$result = slackGetAPI($endpoint = 'users.list');
	while(true) {
		if($result) {
			$data = (array) json_decode($result, true);
			if(isset($data['members']) && is_array($data['members']) && count($data['members']) > 0 && $data['ok'] == true) {
				$users = $data['members'];
				foreach($users as $user) {
					$slack_id = $user['id'];
					$profile = $user['profile'];
					$validUser = false;
					if(isset($profile['email']) && $profile['email'] != '') {
						$email = $profile['email'];
						$user = FrcPortal\User::where('email',$email)->orWhere('team_email',$email)->first();
						if(!is_null($user)) {
							$validUser = true;
							$user->slack_id = $slack_id;
							$user->save();
						}
					}
					if($validUser == false && isset($profile['first_name']) && $profile['first_name'] != '' && isset($profile['last_name']) && $profile['last_name'] != '') {
						$first_name = $profile['first_name'];
						$last_name = $profile['last_name'];
						$user = FrcPortal\User::where('fname',$first_name)->where('lname',$last_name)->first();
						if(!is_null($user)) {
							$user->slack_id = $slack_id;
							$user->save();
						}
					}
				}
				if(isset($data['response_metadata']['next_cursor']) && $data['response_metadata']['next_cursor']!='') {
					$params = array(
						'cursor' => $data['response_metadata']['next_cursor']
					);
					$result = slackGetAPI($endpoint = 'users.list', $params);
				} else {
					break;
				}
			}
		} else {
			break;
		}
	}
}
?>

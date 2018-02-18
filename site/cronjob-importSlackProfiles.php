<?php
include('includes.php');

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
			if(isset($profile['email']) && $profile['email'] != '') {
				$email = $profile['email'];
				$whereArr[] = '(email='.db_quote($email).' OR team_email='.db_quote($email).')';
			}
			if(isset($profile['first_name']) && $profile['first_name'] != '' && isset($profile['last_name']) && $profile['last_name'] != '') {
				$first_name = $profile['first_name'];
				$last_name = $profile['last_name'];
				$whereArr[] = '(fname='.db_quote($first_name).' AND lname='.db_quote($last_name).')';
			}
			$where = count($whereArr) > 0 ? 'WHERE '.implode(' OR ', $whereArr) : '';
			$q = userQuery($sel='',$joins='', $where, $order = '');
			$result = db_select_single($q);
			if(!is_null($result)) {
				$user_id = $result['user_id'];
				$query = 'UPDATE users SET slack_id = '.db_quote($slack_id).' WHERE user_id='.db_quote($user_id);
				$result = db_query($query);
			}
		}
	}
}
?>

<?php
include('includes.php');

$authToken = checkToken();
$user_id = $authToken['data']['user_id'];

$admin = checkAdmin($user_id, $die = false);
$data = array();
if(isset($_GET['user_id']) && $_GET['user_id'] != '' && $_GET['user_id'] != 'undefined' && $admin) {
	$user_id = $_GET['user_id'];
	$data = getUserDataFromParam('user_id', $user_id);
}


$seasonInfo = array(
	'past' => array(),
	'upcomming' => array(),
	'all' => array()
);
$hoursArr = array(
	'past' => array(),
	'current' => array(),
	'all' => array()
);

$season_info = userSeasonInfo($user_id, $year = null);
foreach($season_info as $req) {
	$seasonInfo['all'][] = $req;
	if(time() <= strtotime($req['end_date'])) {
		$seasonInfo['upcomming'] = $req;
	} elseif(time() > strtotime($req['end_date'])) {
		$seasonInfo['past'][] = $req;
	}
}
$eventInfo = userEventInfo($user_id, $year = date('Y'), $event = null);

$endpoints = getNotifiationEndpointsByUser($user_id);
$linkedAccounts = getLinkedAccountsByUser($user_id);
$notificationPreferences = getNotificationPreferencesByUser($user_id);


$data['endpoints'] = $endpoints;
$data['seasonInfo'] = $seasonInfo;
$data['eventInfo'] = $eventInfo;
$data['linkedAccounts'] = $linkedAccounts;
$data['notificationPreferences'] = $notificationPreferences;
/* $data = array(
	'endpoints' => $endpoints,
	'season_info' => $seasonInfo,
	'linked_accounts' => $linkedAccounts,
	'user_info' => $userInfo
); */

if(!empty($data)) {
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$data)));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
}



?>

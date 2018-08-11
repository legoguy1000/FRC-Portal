<?php
include(__DIR__ . '/../includes.php');

//Change User Status after graduation
$changeUserStatus = getSettingsProp('enable_cronjob-changeUserStatus');
if($changeUserStatus) {
  $users = array();
  $month = getSettingsProp('school_month_end');
  if(!is_null($month) && date('F') == $month && date('Y-m-t') = date('Y-m-d')) {
  	$users = FrcPortal\User::where('user_type','Student')->whereNotNull('grad_year')->where('grad_year',date('Y'))->update(['status' => false, 'admin' => false, 'former_student' => true]);
  }
}

//Import Slack usernames
$importSlackProfiles = getSettingsProp('enable_cronjob-importSlackProfiles');
if($importSlackProfiles) {
  $users = array();
  $slack_enable = getSettingsProp('slack_enable');
  $slack_token = getSettingsProp('slack_api_token');
  if(!is_null($slack_enable) && $slack_enable == true && !is_null($slack_token)) {
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
  }
}

//Update events from the Google Calendar
$updateEventsFromGoogle = getSettingsProp('enable_cronjob-updateEventsFromGoogle');
if($updateEventsFromGoogle) {
  $events = FrcPortal\Event::havingRaw('date(event_start) >= CURDATE()-interval 3 month')->get();
  if(count($events) > 0) {
  	foreach($events as $event) {
  		if(isset($event->google_cal_id) && $event->google_cal_id != '') {
  			syncGoogleCalendarEvent($event->event_id);
  		}
  	}
  }
}

//Poll membership form for new members
$importSlackProfiles = getSettingsProp('enable_cronjob-pollMembershipForm');
if($updateEventsFromGoogle) {
  $season_id = null;
  $spreadsheetId = null;
  $season = FrcPortal\Season::where('bag_day','>=',date('Y-m-d'))->first();
  if(!is_null($season)) {
  	$season_id = $season->season_id;
  	$spreadsheetId = $season->join_spreadsheet != '' ? $season->join_spreadsheet:null;
  	if(is_null($spreadsheetId)) {
  		$result = getSeasonMembershipForm($season->year);
  		if($result['status'] == true) {
  			$spreadsheetId = $result['data']['join_spreadsheet'];
  			$season->join_spreadsheet = $spreadsheetId;
  			$season->save();
  		}
  	}
  } else {
  	$year = date('Y')+1;
  	$result = getSeasonMembershipForm($year);
  	if($result['status'] == true) {
  		$spreadsheetId = $result['data']['join_spreadsheet'];
  		$season = FrcPortal\Season::where('year',$year)->first();
  		if(!is_null($season)) {
  			$season_id = $season['season_id'];
  			$season->join_spreadsheet = $spreadsheetId;
  			$season->save();
  		}
  	}
  }
  $data = pollMembershipForm($spreadsheetId);
  if($data != false && !empty($data)) {
  	$return = itterateMembershipFormData($data, $season_id);
  }
}

?>

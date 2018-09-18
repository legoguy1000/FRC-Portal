<?php
include(__DIR__ . '/../includes.php');

//Change User Status after graduation
$changeUserStatus = getSettingsProp('enable_cronjob-changeUserStatus');
if($changeUserStatus) {
  $users = array();
  $month = getSettingsProp('school_month_end');
  if(!is_null($month) && $month != '' && date('F') == $month && date('Y-m-t') == date('Y-m-d')) {
  	$users = FrcPortal\User::where('user_type','Student')->whereNotNull('grad_year')->where('grad_year',date('Y'))->update(['status' => false, 'admin' => false, 'former_student' => true]);
  }
}

//Import Slack Profiles
$importSlackProfiles = getSettingsProp('enable_cronjob-importSlackProfiles');
if($importSlackProfiles) {
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
}

//Poll Membership Form
$pollMembershipFormResponses = getSettingsProp('enable_cronjob-pollMembershipFormResponses');
if($pollMembershipFormResponses) {
  $season_id = null;
  $spreadsheetId = null;
  $season = FrcPortal\Season::where('bag_day','>=',date('Y-m-d'))->orderBy('start_date', 'ASC')->first();
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
  $data = pollMembershipForm($spreadsheetId, $season);
  if($data != false && !empty($data)) {
  	$return = itterateMembershipFormData($data, $season);
  }
}

//Update Google Events
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

//Remove Sign Ins that haven't signed out
$tooLong = getSettingsProp('enable_cronjob-tooLong');
if($tooLong) {
  $hids = array();
  $date= date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")." -12 hours"));
  $result = FrcPortal\MeetingHour::whereNull('time_out')->where('time_in','<=',$date)->delete();
}
//End of Day to Slack
$endOfDayHoursToSlack = getSettingsProp('enable_cronjob-endOfDayHoursToSlack');
if($endOfDayHoursToSlack && date('H') == 21) {
  endOfDayHoursToSlack($date = null);
}
?>

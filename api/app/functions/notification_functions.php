<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Database\Capsule\Manager as DB;

function sendMassNotifications($type, $msgData) {
	$notifications = FrcPortal\NotificationPreference::with('user')->where('type',$type)->get();
	foreach($notifications as $note) {
		if($note['method'] == 'email') {
			$msg = $msgData['email'];
			$subject = $msg['subject'];
			$content = $msg['content'];
			//$userData = FrcPortal\User::find($user->user_id);
			$attachments = !empty($msg['attachments']) && is_array($msg['attachments']) ? $msg['attachments'] : false;
			$note->user->emailUser($subject,$content,$attachments);
		}
		elseif($note['method'] == 'slack') {
			$msg = $msgData['slack'];
			$title = $msg['title'];
			$body = $msg['body'];
			$tag = '';
			$note_id = uniqid();
			$note->user->slackMessage($body);
		}
	}
}

function postToSlack($msg = '', $channel = null) {
	$slack_enable = getSettingsProp('slack_enable');
	if(!$slack_enable) {
		return false;
	}

	$data = array(
		'text'=>$msg
		//'username'=> '',
		//'icon_url'=> '',
		//'icon_emoji'=>':taco:'
	);
	if($channel != null) {
		$data["channel"] = $channel;
	}
	$result = SlackApiPost($data);
	return $result;
}

function SlackApiPost($data = null) {
	$result = false;
	if(empty($data) || !is_array($data)) {
		return $result;
	}
	$result = slackPostAPI($endpoint = 'chat.postMessage', $data);
	return $result;
}

function endOfDayHoursToSlack($date = null) {
	if(empty($date)) {
		$date = date('Y-m-d');
	}
	$msg = 'Congratulations on another hard day of work.#new_line#';
	$result = DB::table('meeting_hours')
						->whereRaw('DATE(meeting_hours.time_in) = "'.$date.'"')
						->whereRaw('DATE(meeting_hours.time_out)=DATE(meeting_hours.time_in)')
						->select(DB::raw('IFNULL(SUM(time_to_sec(timediff(meeting_hours.time_out, meeting_hours.time_in)) / 3600),0) as hours'))->groupBy(DB::raw('DATE(meeting_hours.time_in)'))->first();

	//$query = 'SELECT IFNULL(SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600),0) as hours FROM meeting_hours a WHERE DATE(a.time_in)='.db_quote($date).' AND DATE(a.time_out)=DATE(a.time_in) GROUP BY DATE(a.time_in)';
	//$result = db_select_single($query);
	if(!empty($result)) {
		$hours = $result->hours;
		$result = DB::table('meeting_hours')
							->whereRaw('year(meeting_hours.time_in) = "'.date('Y',strtotime($date)).'"')
							->whereRaw('DATE(meeting_hours.time_out)=DATE(meeting_hours.time_in)')
							->select(DB::raw('IFNULL(SUM(time_to_sec(timediff(meeting_hours.time_out, meeting_hours.time_in)) / 3600),0) as hours'))->groupBy(DB::raw('year(meeting_hours.time_in)'))->first();
		//$query = 'SELECT IFNULL(SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600),0) as hours FROM meeting_hours a WHERE year(a.time_in)='.db_quote(date('Y',strtotime($date))).' GROUP BY year(a.time_in)';
		//$result = db_select_single($query);
		$total = !empty($result) ? $result->hours : 0;
		$teamName = getSettingsProp('team_name');
		$msg .= $teamName.' completed another '.round($hours,1).' hours of work for an annual total of '.round($total,1).'.#new_line#Keep up the amazing work!!';
		postToSlack($msg, $channel = null);
	}
}

function emailSignInOut($user_id,$emailData) {
	$year = date('Y');
	$date = date('Y-m-d');

	$signInTime = $emailData['signin_time'] ? $emailData['signin_time']:'';
	$signInOut= $emailData['signin_out'] ? $emailData['signin_out']:'';

	$season = FrcPortal\Season::with(['annual_requirements' => function ($query) use ($user_id) {
						$query->where('user_id','=',$user_id); // fields from comments table,
					}])->where('year','=',$year)->first();
	$userSeasonInfo = $season['annual_requirements'];

	$season_start = $season['start_date'];
	$season_end = $season['end_date'];
	$msg = '';
	if($season->season_period['build_season']) {
		$msg = ' You have accumulated '.$userSeasonInfo['build_season_hours'].' build season hours.';
	} elseif($season->season_period['competition_season']) {
		$msg = ' You have accumulated '.$userSeasonInfo['competition_season_hours'].' competition season hours.';
	} elseif($season->season_period['off_season']) {
		$msg = ' You have accumulated '.$userSeasonInfo['build_season_hours'].' offseason hours.';
	}

	$io = '';
	if($signInOut == 'sign_in') {
		$io = 'in';
	} else if($signInOut == 'sign_out') {
		$io = 'out';
	}
	$subject = 'You signed '.$io.' at '.$signInTime;
	$teamNumber = getSettingsProp('team_number');
	$content = '<p>You signed '.$io.' using the Team '.$teamNumber.' Portal at '.$signInTime.'.</p>';
	$content .= '<p> '.$msg.' You have accumulated '.$userSeasonInfo['total_hours'].' total annual hours. Do not forget to sign out or your hours will not be recorded.</p>';

	return array(
		'subject' => $subject,
		'content' => $content
	);
	//emailUser($userData,$subject,$content,$attachments = false);
}

?>

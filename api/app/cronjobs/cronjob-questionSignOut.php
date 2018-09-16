<?php
include(__DIR__ . '/../includes.php');
//

if((date('N') <= 5 && date('H') == 21) || (date('N') > 5 && date('H') == 18)) {
	$date= date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")." -12 hours"));
/*	$sel = 'meeting_hours.time_in, meeting_hours.time_out, meeting_hours.hours_id';
	$joins = ' RIGHT JOIN meeting_hours USING (user_id)';
	$where = ' WHERE time_out IS NULL AND time_in > '.db_quote($date);
	$query = userQuery($sel, $joins, $where, $order='');
	$result = db_select_user($query); */
	$result = FrcPortal\MeetingHour::with('users')->whereNull('time_out')->where('time_in','>',$date)->get();
	if(count($result) > 0) {
		foreach($result as $hour) {
			$user = $hour->users;
			if($user->slack_id != '') {
				$msg = 'Hey '.$user->fname.',#new_line##new_line#';
				$msg .= 'I noticed that you signed in on '.date('F j, Y',strtotime($user->time_in)).' at '.date('g:i A',strtotime($user->time_in)).' and have not signed out yet.  Most weekday meetings end at 9pm and weekend meetings at 6pm.  If you forgot to sign out please use the button below, if not please do not forget to sign out before leaving the shop.  This is your only reminder, if you do not sign out your hours will not count.';
				$attachments = array(
					array(
						'fallback' => 'You are unable to sign out',
						'callback_id' => 'sign_out',
						'color' => '#662E91',
						'attachment_type' => 'default',
						'actions' => array(
							array(
								'name' => 'sign_out',
								'text' => 'Sign Out Now',
								'type' => 'button',
								'value' => $user->user_id.'-'.$hour->hours_id,
								'style' => 'primary'
							)
						)
					)
				);
				$data = array(
					'channel' => $user->slack_id,
					'text'=>$msg,
					'attachments'=>$attachments
				);
				$result = SlackApiPost($data);
			}
		}
	}
}//


?>

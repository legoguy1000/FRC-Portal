<?php
include(__DIR__ . '/../includes.php');
//

if((date('N') <= 5 && date('H') == 21) || (date('N') > 5 && date('H') == 18)) {
	$date= date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")." -12 hours"));
	$sel = 'meeting_hours.time_in, meeting_hours.time_out';
	$joins = ' RIGHT JOIN meeting_hours USING (user_id)';
	$where = ' WHERE time_out IS NULL AND time_in > '.db_quote($date);
	$query = userQuery($sel, $joins, $where, $order='');
	$result = db_select_user($query);
	if(count($result) > 0) {
		foreach($result as $user) {
			$msg = 'Hey '.$user['fname'].',#new_line##new_line#';
			$msg .= 'I noticed that you signed in on '.date('F j, Y',strtotime($user['time_in'])).' at '.date('g:i A',strtotime($user['time_in'])).' and have not signed out yet.  Most weeekday meetings end at 9pm and weeekend meetings at 6pm.  If you forgot to sign out please use the button below, if not please do not forget to sign out before leaving the shop.  This is your only reminder, if you do not sign out your hours will not count.';
			$attachments = array(
				'fallback' => 'You are unable to sign out',
				'callback_id' => 'sign_out',
				'color' => '#662E91',
				'attachment_type' => 'default',
				'actions' => array(
					array(
						'name' => 'sign_out',
						'text' => 'Sign Out Now',
						'type' => 'button',
						'value' => 'true',
						'style' => 'primary'
					)
				)
			);
			$data = array(
				'text'=>$msg,
				'attachments'=>$attachments
			);
			if($channel != null) {
				$data["channel"] = $channel;
			}
			$content = str_replace('#new_line#','\n',json_encode($data));
			$slack_webhook_url = getIniProp('slack_webhook_url');
			//$url = 'https://hooks.slack.com/services/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';
			$ch = curl_init();
			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $slack_webhook_url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type: application/json',
			    'Content-Length: ' . strlen($content))
			);
			$result = curl_exec($ch);
			//close connection
			curl_close($ch);
		}
	}
}


?>

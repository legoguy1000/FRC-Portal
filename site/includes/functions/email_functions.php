<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getNotificationOptions() {
	$default = array(
		'sign_in_out' => false,
		'new_season' => false,
		'new_event' => false,
		'join_team' => false,
		'dues' => false,
		'stims' => false,
	);
	$data = array(
		'slack' => $default,
		'email' => $default,
	);
	return $data;
}

function getNotificationPreferencesByUser($user_id) {
	$data = getNotificationOptions();
	$query = 'SELECT np.* FROM notification_preferences np WHERE user_id='.db_quote($user_id);
	$result = db_select($query);
	if(count($result) > 0) {
		foreach($result as $re) {
			$m = $re['method'];
			$t = $re['type'];
			$data[$m][$t] = true;
		}
	}
	return $data;
}

function setDefaultNotifications($user_id) {
	$data = getNotificationOptions();
	$queryArr = array();
	$queryStr	 = '';
	foreach($data as $meth=>$types) {
		foreach($types as $type) {
			$pref_id = uniqid();
			$queryArr[] = '('.db_quote($pref_id).', '.db_quote($user_id).', '.db_quote($meth).', '.db_quote($type).')';
		}
	}
	if(!empty($queryArr)) {
		$queryStr = implode(',',$queryArr);
	}
	$query = 'INSERT INTO notification_preferences (pref_id, user_id, method, type) VALUES '.$queryStr;
	$result = db_query($query);
}

/*
use Minishlink\WebPush\WebPush;
function sendPushNotificationByUser($user, $title='', $body='', $tag='') {
	$db = db_connect();

	$ti = '';
	$tagInit = uniqid();
	if(isset($title) && $title!='') {
		$ti = ' | '.$title;
	}
	if(isset($tag) && $tag!='') {
		$tagInit = $tag;
	}
	$apiKeys = array(
		'GCM' => getIniProp('fcm_key'),
	);
	$webPush = new WebPush($apiKeys);

	$endpoints = getNotifiationEndpointsByUser($user);
	$payload = array(
		'title'=>'Team 2363 Portal'.$ti,
		'body'=>$body,
		'tag'=>$tagInit,
	);
	foreach($endpoints as $ep) {
		$notification = array(
			'endpoint' => $ep['endpoint'],
			'userPublicKey' => $ep['public_key'],
			'userAuthToken' => $ep['auth_secret'],
			'payload' => json_encode($payload)
		);
		$webPush->sendNotification(
			$notification['endpoint'],
			$notification['payload'], // optional (defaults null)
			$notification['userPublicKey'], // optional (defaults null)
			$notification['userAuthToken'], // optional (defaults null)
			true,
			$options = array(
				'TTL' => 60
			)
		);
	}
} */

function sendUserNotification($user_id, $type, $msgData)
{
	global $db;

	$preferences = getNotificationPreferencesByUser($user_id);

	//$preferences = array('push' => true, 'email' => false);
	if($preferences['email'][$type] == true) {
		$msg = $msgData['email'];
		$subject = $msg['subject'];
		$content = $msg['content'];
		$userData = $msg['userData'];
		$attachments = isset($msg['attachments']) && is_array($msg['attachments']) ? $msg['attachments'] : false;
		emailUser($userData,$subject,$content,$attachments);
	}
	if($preferences['slack'][$type] == true) {
		$msg = $msgData['slack'];
		$title = $msg['title'];
		$body = $msg['body'];
		$tag = '';
		$note_id = uniqid();
		$query = 'INSERT INTO notifications (note_id,user_id,message) VALUES ('.db_quote($note_id).','.db_quote($user_id).','.db_quote($body).')';
		$result = db_query($query);
		if($result) {
			slackMessageToUser($user_id, $body);
			//sendPushNotificationByUser($user_id, $title, $body, $note_id);
		}
	}
}

function emailUser($userData = array(),$subject = '',$content = '',$attachments = false)
{
	$root = '/home/team2363_portal/portal.team2363.org';
	$html = file_get_contents($root.'/site/includes/libraries/email_template.html');
	$css = file_get_contents($root.'/site/includes/libraries/email_css.css');
	$emogrifier = new \Pelago\Emogrifier($html, $css);
	$mergedHtml = $emogrifier->emogrify();

	$subjectLine = $subject;
	$emailContent = $content ;
	$email = str_replace('###SUBJECT###',$subjectLine,$mergedHtml);
	$email = str_replace('###FNAME###',$userData['fname'],$email);
	$email = str_replace('###CONTENT###',$emailContent,$email);
	$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
	try {
	    //Server settings
	    $mail->SMTPDebug = 2;                                 // Enable verbose debug output
	    /* $mail->isSMTP();                                      // Set mailer to use SMTP
	    $mail->Host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup SMTP servers
	    $mail->SMTPAuth = true;                               // Enable SMTP authentication
	    $mail->Username = 'user@example.com';                 // SMTP username
	    $mail->Password = 'secret';                           // SMTP password
	    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
	    $mail->Port = 587;                                    // TCP port to connect to */

	    //Recipients
	    $mail->setFrom('portal@team2363.org', 'Team 2363 Portal');
	    $mail->addAddress($userData['email'], $userData['full_name']);     // Add a recipient
	   /*  $mail->addAddress('ellen@example.com');               // Name is optional
	    $mail->addReplyTo('info@example.com', 'Information');
	    $mail->addCC('cc@example.com');
	    $mail->addBCC('bcc@example.com'); */

	    //Attachments
			if($attachments != false && is_array($attachments)) {
				foreach($attachments as $file) {
					if(is_array($file) && file_exists($file['path'])) {
						$mail->addAttachment($file['path'], $file['name']);
					} elseif(file_exists($file)) {
						$mail->addAttachment($file);
					}
				}
			}
	    /* $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
	    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name */

	    //Content
	    $mail->isHTML(true);                                  // Set email format to HTML
	    $mail->Subject = $subject;
	    $mail->Body    = $email;
	    /* $mail->AltBody = 'This is the body in plain text for non-HTML mail clients'; */

	    $mail->send();
	//    echo 'Message has been sent';
	} catch (Exception $e) {
		return $mail->ErrorInfo;
	 //   echo 'Message could not be sent.';
	  //  echo 'Mailer Error: ' . $mail->ErrorInfo;
	}
}

function emailSignInOut($user_id,$emailData) {
	$year = date('Y');
	$date = date('Y-m-d');

	$signInTime = $emailData['signin_time'] ? $emailData['signin_time']:'';
	$signInOut= $emailData['signin_out'] ? $emailData['signin_out']:'';

	$seasonInfo = userSeasonInfo($user_id, $year);
	$userSeasonInfo = $seasonInfo[0];

	$season = getSeasonByYear($year, $reqs = false);
	$season_start = $season['start_date'];
	$season_end = $season['end_date'];
	$msg = '';
	if($date >= $season_start && $date <= $season_end) {
		$msg = ' You have accumulated '.$userSeasonInfo['season_hours_exempt'].' non-exempt season hours.';
	} else {
		$msg = ' You have accumulated '.$userSeasonInfo['off_season_hours'].' offseason hours.';
	}

	$io = '';
	if($signInOut == 'sign_in') {
		$io = 'in';
	} else if($signInOut == 'sign_out') {
		$io = 'out';
	}
	$subject = 'You signed '.$io.' at '.$signInTime;
	$content = '<p>You signed '.$io.' using the Team 2363 Portal at '.$signInTime.'.</p><p> '.$msg.' You have accumulated '.$userSeasonInfo['total'].' total annual hours. Do not forget to sign out or your hours will not be recorded.</p>';

	return array(
		'subject' => $subject,
		'content' => $content
	);
	//emailUser($userData,$subject,$content,$attachments = false);
}
























/* OLD */

function errorHandle($error, $query = '')
{
	$db = db_connect();
	$userId = '';
	$token = checkToken($die=false,$die401=false);
	if($token != false)
	{
		$userId = $token['data']['id'];
	}
	$errorId = insertLogs($userId, 'mysql', 'error', $query.'<br/><br/>'.$error);
	$smtpCreds = smtpCredentials();
	$mail = new PHPMailer;
	$mail->setFrom('frcscout_mysql_error@resnick-tech.com', 'FRC Scout MySQL Error');
	$mail->addAddress('adr8292@gmail.com', 'Alex resnick');     // Add a recipient
	$mail->isHTML(true);                                  // Set email format to HTML
	$mail->Subject = 'FRC Scout | MySQL Error';
	//$mail->SMTPDebug = 1;
	$mail->IsSMTP();
	$mail->Mailer = "smtp";
	$mail->Host = $smtpCreds['host'];
	$mail->Port = $smtpCreds['port'];
	$mail->SMTPAuth = $smtpCreds['auth'];
	$mail->SMTPSecure = $smtpCreds['ssl'];
	$mail->Username = $smtpCreds['user'];
	$mail->Password = $smtpCreds['pasword'];
	$mail->Body  = $error.'<br/><br/>'.$query;
	$email = array();
	if(!$mail->send()) {
		$email['msg'] = 'Mailer Error: ' . $mail->ErrorInfo;
		$email['status'] = false;
	} else {
		$email['msg'] = 'Message has been sent';
		$email['status'] = true;
	}
	//header("HTTP/1.1 500 Internal Server Error");
	//echo json_encode(array('status'=>false, 'type'=>'danger', 'msg'=>$error, 'query'=>$query, 'email'=>$email, 'error_id'=>$errorId));
	//return array('status'=>false, 'type'=>'danger', 'msg'=>$error, 'query'=>$query, 'email'=>$email);
}

function webHookEmailNotification($data)
{
	//Email me the info
	$smtpCreds = smtpCredentials();
	$mail = new PHPMailer;
	$mail->setFrom('blue_alliance_webhook@resnick-tech.com', 'Blue Alliance Webhook');
	$mail->addAddress('adr8292@gmail.com', 'Alex resnick');     // Add a recipient
	$mail->isHTML(true);                                  // Set email format to HTML
	$mail->Subject = 'Blue Alliance | '.ucwords(str_replace('_',' ',$data['message_type']));
	//$mail->SMTPDebug = 1;
	$mail->IsSMTP();
	$mail->Mailer = "smtp";
	$mail->Host = $smtpCreds['host'];
	$mail->Port = $smtpCreds['port'];
	$mail->SMTPAuth = $smtpCreds['auth'];
	$mail->SMTPSecure = $smtpCreds['ssl'];
	$mail->Username = $smtpCreds['user'];
	$mail->Password = $smtpCreds['pasword'];
	$message = 'No Data';
	if(!empty($data))
	{
		if($data['message_type'] == 'match_score')
		{
			$eventName = $data['message_data']['event_name'];
			$match = $data['message_data']['match'];
			$matchNum = $match['match_number'];
			$redAlliance = $match['alliances']['red']['teams'];
			$blueAlliance = $match['alliances']['blue']['teams'];
			$redScore = $match['alliances']['red']['score'];
			$blueScore = $match['alliances']['blue']['score'];
			$message = '<table class="tg" style="undefined: ;table-layout: fixed;width: 292px;border-collapse: collapse;border-spacing: 0;">
						<colgroup>
						<col style="width: 100px">
						<col style="width: 100px">
						<col style="width: 100px">
						<col style="width: 100px">
						<col style="width: 100px">
						</colgroup>
						  <tr>
							<th class="tg-9hbo" colspan="5" style="font-family: Arial, sans-serif;font-size: 18px;font-weight: bold;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;vertical-align: top;">'.$eventName.'<br/>Match: '.$matchNum.'</th>
						  </tr>
						  <tr>
							<td class="tg-yw4l" style="font-family: Arial, sans-serif;font-size: 18px;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;vertical-align: top;"></td>
							<td class="tg-amwm" colspan="3" style="font-family: Arial, sans-serif;font-size: 18px;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;font-weight: bold;text-align: center;vertical-align: top;">Alliance Members</td>
							<td class="tg-9hbo" style="font-family: Arial, sans-serif;font-size: 18px;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;font-weight: bold;vertical-align: top;">Score</td>
						  </tr>
						  <tr>
							<td class="tg-9hbo" style="font-family: Arial, sans-serif;font-size: 18px;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;font-weight: bold;vertical-align: top;">Red</td>
							<td class="tg-0fb1" style="font-family: Arial, sans-serif;font-size: 18px;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;background-color: #fe0000;color: #ffffff;text-align: center;vertical-align: top;">'.$redAlliance[0].'</td>
							<td class="tg-0fb1" style="font-family: Arial, sans-serif;font-size: 18px;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;background-color: #fe0000;color: #ffffff;text-align: center;vertical-align: top;">'.$redAlliance[1].'</td>
							<td class="tg-0fb1" style="font-family: Arial, sans-serif;font-size: 18px;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;background-color: #fe0000;color: #ffffff;text-align: center;vertical-align: top;">'.$redAlliance[2].'</td>
							<td class="tg-cgy6" style="font-family: Arial, sans-serif;font-size: 18px;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;font-weight: bold;background-color: #fe0000;color: #000000;text-align: center;vertical-align: top;">'.$redScore.'</td>
						  </tr>
						  <tr>
							<td class="tg-9hbo" style="font-family: Arial, sans-serif;font-size: 18px;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;font-weight: bold;vertical-align: top;">Blue</td>
							<td class="tg-ohc4" style="font-family: Arial, sans-serif;font-size: 18px;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;background-color: #3166ff;color: #ffffff;text-align: center;vertical-align: top;">'.$blueAlliance[0].'</td>
							<td class="tg-ohc4" style="font-family: Arial, sans-serif;font-size: 18px;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;background-color: #3166ff;color: #ffffff;text-align: center;vertical-align: top;">'.$blueAlliance[1].'</td>
							<td class="tg-ohc4" style="font-family: Arial, sans-serif;font-size: 18px;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;background-color: #3166ff;color: #ffffff;text-align: center;vertical-align: top;">'.$blueAlliance[2].'</td>
							<td class="tg-cfoz" style="font-family: Arial, sans-serif;font-size: 18px;padding: 10px 5px;border-style: solid;border-width: 1px;overflow: hidden;word-break: normal;font-weight: bold;background-color: #3166ff;color: #000000;text-align: center;vertical-align: top;">'.$blueScore.'</td>
						  </tr>
						</table>';
		}
		else
		{
			$message = '';
		}
	}
	$message .= '<br/><br/><br/>'.json_encode($data).'<br/><br/><br/>';
	$mail->Body  = $message;
	if(!$mail->send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;
	} else {
		echo 'Message has been sent';
	//	echo '<br/><br/>';
	//	echo json_encode($data);
	}
}

function contactEmail($msgData)
{
	$smtpCreds = smtpCredentials();
	$mail = new PHPMailer;
	$mail->setFrom('frcscout_mysql_error@resnick-tech.com', 'FRC Scout MySQL Error');
	$mail->addAddress($$msgData['email'], $msgData['name']);     // Add a recipient
	$mail->isHTML(true);                                  // Set email format to HTML
	$mail->Subject = 'FRC Scout Contact';
	$mail->SMTPDebug = 1;
	$mail->IsSMTP();
	$mail->Mailer = "smtp";
	$mail->Host = $smtpCreds['host'];
	$mail->Port = $smtpCreds['port'];
	$mail->SMTPAuth = $smtpCreds['auth'];
	$mail->SMTPSecure = $smtpCreds['ssl'];
	$mail->Username = $smtpCreds['user'];
	$mail->Password = $smtpCreds['pasword'];
	$mail->Body  = $error.'<br/><br/>'.$query;
	$email = array();
	if(!$mail->send()) {
		$email['msg'] = 'Mailer Error: ' . $mail->ErrorInfo;
		$email['status'] = false;
	} else {
		$email['msg'] = 'Message has been sent';
		$email['status'] = true;
	}
	return array('status'=>false, 'type'=>'danger', 'msg'=>$error, 'query'=>$query, 'email'=>$email);
}











?>

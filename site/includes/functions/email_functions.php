<?php

function smtpCredentials()
{
	$data = array(
		'host' => 'ssrs.reachmail.net',
		'port' => '587',
		'user' => 'RESNICKT\alex',
		'pasword' => 'Legoguy0923',
		'auth' => true,
		'ssl' => 'tls'
	);
	return $data;
}

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

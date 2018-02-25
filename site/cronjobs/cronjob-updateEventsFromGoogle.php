<?php
include(__DIR__ . '/../includes.php');
//
$calendar = getIniProp('google_calendar_id');
$api_key = getIniProp('google_api_key');
$query = 'SELECT * FROM events WHERE date(event_start) >= CURDATE()-interval 3 month'
$result = db_select($query);
if(count($result) > 0) {
	$client = new Google_Client();
	$client->setDeveloperKey($api_key);
	$service = new Google_Service_Calendar($client);
	foreach($result as $event) {
		if(isset($event['google_cal_id']) && $event['google_cal_id'] != '') {
			$gevent = $service->events->get($calendar, $event['google_cal_id']);
			$name = $gevent->summary;
			$event_start = $gevent->start->dateTime;
			$event_end = $gevent->end->dateTime;
			if(empty($start)) {
				$event_start = $gevent->start->date.' 00:00:00';
				$event_end = $gevent->end->date.' 23:59:59';
			}
			$location = $gevent->location;
		}
	}
}


?>

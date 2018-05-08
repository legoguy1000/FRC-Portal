<?php
function syncGoogleCalendarEvent($event_id) {
	$calendar = getIniProp('google_calendar_id');
	$api_key = getIniProp('google_api_key');
	$result = array(
		'status' => false,
		'msg' => '',
		'data' => null
	);
	$event = FrcPortal\Event::find($event_id);
	if(!is_null($event)) {
		$cal_id = $event->google_cal_id;
		if(isset($google_cal_id) && $google_cal_id != '') {
			try {
				$client = new Google_Client();
				$client->setDeveloperKey($api_key);
				$service = new Google_Service_Calendar($client);
				$gevent = $service->events->get($calendar, $google_cal_id);

				$event->name = $gevent->summary;
				$event->details = $gevent->description;
				$event->location = $gevent->location;
				if(empty($gevent->start->dateTime)) {
					$event->event_start = $gevent->start->date.' 00:00:00';
					$event->event_end = $gevent->end->date.' 23:59:59';
				} else {
					$event->event_start = date('Y-m-d H:i:s', strtotime($gevent->start->dateTime));
					$event->event_end =date('Y-m-d H:i:s', strtotime($gevent->end->dateTime));
				}
				if($event->save()) {
					$result['status'] = true;
					$result['msg'] = $event->name.' synced with Google Calendar';
					$result['data'] = $event;
				} else {
					$result['msg'] = 'Something went wrong updating the event';
				}

			} catch (Exception $e) {
					$error = json_decode($e->getMessage(), true);
	        if($error['error']['code'] == 404) {
						$result['msg'] = 'Google Calendar event not found';
					} else {
						$result['msg'] = 'Something went wrong searching Google Calendar';
					}
	    }
		} else {
			$result['msg'] = 'Google Calendar Event ID not found';
		}
	} else {
		$result['msg'] = 'Event ID not found';
	}
	return $result;
}
?>

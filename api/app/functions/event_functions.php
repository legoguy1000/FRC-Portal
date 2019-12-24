<?php
function getGoogleCalendarEvent($google_cal_id) {
	$calendar = getSettingsProp('google_calendar_id');
	$api_key = getSettingsProp('google_api_key');
	if(empty($google_cal_id)) {
		throw new Exception('Google Calendar Event ID cannot be blank', 400);
	}
	try {
		$client = new Google_Client();
		$client->setDeveloperKey($api_key);
		$service = new Google_Service_Calendar($client);
		$gevent = $service->events->get($calendar, $google_cal_id);
		return formatGoogleCalendarEventData($gevent);
	} catch (Exception $e) {
		throw $e;
	}
}

function getEventTimeSlotList($event_id) {
	$timeSlots = array();
	if(empty($event_id)) {
		throw new Exception('Event ID cannot be blank', 400);
	}
	return FrcPortal\EventTimeSlot::with('registrations.user')->where('event_id',$event_id)->orderBy('time_start', 'ASC')->get();
}

function formatGoogleCalendarEventData($event) {
	$temp = array(
		'google_event' => $event,
		'name' => $event->summary,
		'location' => $event->location,
		'google_cal_id' => $event->id,
		'start' => null,
		'end' => null,
		'allDay' => false,
		'event_start' => null,
		'event_end' => null,
		'single_day' => null,
		'single_month' => null,
/*				'event_start_unix' => null,
		'event_end_unix' => null,
		'event_end_formatted' => null,
		'event_start_iso' => null,
		'event_end_iso' => null,
		'event_end_formatted' => null, */
		'details' => $event->description,
	);
	if(empty($event->start->dateTime)) {
		$temp['allDay'] = true;
		$temp['event_start'] = $event->start->date.' 00:00:00';
		$ed = new DateTime($event->end->date);
		$ed->modify("-1 day");
		$temp['event_end'] = $ed->format("Y-m-d").' 23:59:59';
	} else {
		$temp['event_start'] = date('Y-m-d H:i:s', strtotime($event->start->dateTime));
		$temp['event_end'] = date('Y-m-d H:i:s', strtotime($event->end->dateTime));
	}
	$temp['start'] = formatDateArrays($temp['event_start']);
	$temp['end'] = formatDateArrays($temp['event_end']);
	$temp['single_day'] = (bool) ($temp['start']['date_raw'] == $temp['end']['date_raw']);
	$temp['single_month'] = (bool) ($temp['start']['date_ym'] == $temp['end']['date_ym']);
	return $temp;
}

function checkTimeSlotOverlap($timeSlot) {
	$data = FrcPortal\EventTimeSlot::where('event_id',$timeSlot->event_id)
	->where(function($query) use ($timeSlot){
		//Old encompass new
		$query->where(function($query) use ($timeSlot){
			$query->where('time_start','<=',$timeSlot->time_start);
			$query->where('time_end','>=',$timeSlot->time_end);
		})
		//Old straddle new start
		->orWhere(function($query) use ($timeSlot){
		 	$query->where('time_start', '<=', $timeSlot->time_start);
		 	$query->where('time_end', '>', $timeSlot->time_start);
		})
		//Old straddle new end
		->orWhere(function($query) use ($timeSlot){
		 	$query->where('time_start', '<', $timeSlot->time_end);
		 	$query->where('time_end', '>=', $timeSlot->time_end);
		})
		//New encompass old
		->orWhere(function($query) use ($timeSlot){
		 	$query->where('time_start', '>=', $timeSlot->time_start);
		 	$query->where('time_end', '<=', $timeSlot->time_end);
		});
	});
	if(!empty($timeSlot->time_slot_id)) {
		$data->where('time_slot_id','<>',$timeSlot->time_slot_id);
	}
	return $data->exists();
}
function formatTimeSlot($timeSlot, $formData) {
    $timeSlot->name = $formData['name'];
    $timeSlot->description = !empty($formData['description']) ? $formData['description']:'';
    $ts = new DateTime($formData['time_start']);
    $te = new DateTime($formData['time_end']);
    $timeSlot->time_start = $ts->format('Y-m-d H:i:s');
    $timeSlot->time_end = $te->format('Y-m-d H:i:s');
		if(checkTimeSlotOverlap($timeSlot)) {
			throw new Exception('Time Slot cannot overlap an existing slot', 400);
		}
	  return $timeSlot;
}

function updateTimeSlot($event_id, $time_slot_id, $formData) {
	if(empty($event_id)) {
		throw new Exception('Event ID is invalid', 400);
	}
	if(empty($time_slot_id)) {
		throw new Exception('Time Slot ID is invalid', 400);
	}
	if(empty($formData)) {
		throw new Exception('Invalid Time Slot data', 400);
	}
	$timeSlot = FrcPortal\EventTimeSlot::where('event_id',$event_id)->where('time_slot_id',$time_slot_id)->first();
	if(empty($timeSlot)) {
		throw new Exception('Event Time Slot not found', 404);
	}
	try {
		$timeSlot = formatTimeSlot($timeSlot, $formData);
	  if(!$timeSlot->save()) {
			throw new Exception('Time Slot could not be saved', 500);
		}
		return true;
	} catch (Exception $e) {
		throw $e;
	}
}

function addTimeSlot($event_id, $formData) {
	if(empty($event_id)) {
		throw new Exception('Event ID is invalid', 400);
	}
	if(empty($formData)) {
		throw new Exception('Invalid Time Slot data', 400);
	}
	$timeSlot = new FrcPortal\EventTimeSlot();
  $timeSlot->event_id = $event_id;
	try {
		$timeSlot = formatTimeSlot($timeSlot, $formData);
		if(!$timeSlot->save()) {
			throw new Exception('Time Slot could not be saved', 500);
		}
		return true;
	} catch (Exception $e) {
		throw $e;
	}

}
?>

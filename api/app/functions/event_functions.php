<?php
	function syncGoogleCalendarEvent($event_id) {
	$calendar = getSettingsProp('google_calendar_id');
	$api_key = getSettingsProp('google_api_key');
	$event = FrcPortal\Event::with('poc')->find($event_id); //, 'event_rooms.users', 'event_cars', 'event_time_slots.registrations.user'
	if(is_null($event)) {
		throw new Exception('Event ID not found');
	}
	$google_cal_id = $event->google_cal_id;
	if(!isset($google_cal_id) || $google_cal_id == '') {
		throw new Exception('Google Calendar Event ID cannot be blank', 400);
	}
	$ge = getGoogleCalendarEvent($google_cal_id);
	$event->name = $ge['name'];
	$event->details = $ge['details'];
	$event->location = $ge['location'];
	$event->event_start = $ge['event_start'];
	$event->event_end = $ge['event_end'];
	if(!is_null($event->registration_deadline_gcalid) && $event->registration_deadline_gcalid != '') {
		try {
			$ged = getGoogleCalendarEvent($event->registration_deadline_gcalid);
			$event->registration_deadline = $ged['event_end'];
		} catch (Exception $e) {}
	}
	if(!$event->save()) {
		throw new Exception('Something went wrong updating the event', 500);
	}
	return $event;
}

function getGoogleCalendarEvent($google_cal_id) {
	$calendar = getSettingsProp('google_calendar_id');
	$api_key = getSettingsProp('google_api_key');
	if(!isset($google_cal_id) || $google_cal_id == '') {
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

function getEventCarList($event_id) {
	$cars = array();
	$carInfo = array();
	if(!isset($event_id) || $event_id == '') {
		throw new Exception('Event ID cannot be blank', 400);
	}
	$carInfo = FrcPortal\EventCar::with(['driver','passengers'])->where('event_id',$event_id)->get();
	if(count($carInfo) > 0) {
		foreach($carInfo as $car) {
			$car_id = $car->car_id;
			$users = FrcPortal\EventRequirement::with(['user'])->where('event_id',$event_id)->where('car_id','=',$car_id)->get();
			$cars[$car_id] = $users;
		}
	}
	//no user yet users
	$users = FrcPortal\EventRequirement::with(['user'])->where('event_id',$event_id)->where('registration',true)->whereNull('car_id')->get();
	$cars['non_select'] = $users;
	return array('cars'=>$carInfo, 'total'=>count($carInfo), 'car_selection'=>$cars);

}

function getEventRoomList($event_id) {
	$result = array(
		'status' => false,
		'msg' => '',
		'data' => null
	);
	$rooms = array();
	$roomInfo = array();
	if(!isset($event_id) || $event_id == '') {
		throw new Exception('Event ID cannot be blank', 400);
	}
	$roomInfo = FrcPortal\EventRoom::where('event_id',$event_id)->get();
	if(count($roomInfo) > 0) {
		foreach($roomInfo as $room) {
			$room_id = $room->room_id;
			$users = FrcPortal\EventRequirement::with(['user'])->where('event_id',$event_id)->where('room_id','=',$room_id)->get();
			$rooms[$room_id] = $users;
		}
	}
	//no user yet users
	$users = FrcPortal\EventRequirement::with(['user'])->where('event_id',$event_id)->where('registration',true)->whereNull('room_id')->get();
	$rooms['non_select'] = $users;
	return array('rooms'=>$roomInfo, 'total'=>count($roomInfo), 'room_selection'=>$rooms);
}

function deleteEventRoom($event_id, $room_id) {
	if(!isset($event_id) || $event_id == '') {
		throw new Exception('Event ID cannot be blank', 400);
	}
	if(!isset($room_id) || $room_id == '') {
		throw new Exception('Room ID cannot be blank', 400);
	}
	$eventRoom = FrcPortal\EventRoom::where('event_id',$event_id)->where('room_id',$room_id)->first();
	if(is_null($eventRoom)) {
		throw new Exception('Event Room not found', 404);
	}
	if(!$eventRoom->delete()) {
		throw new Exception('Something went wrong', 500);
	}
	return true;
}

function getEventTimeSlotList($event_id) {
	$timeSlots = array();
	if(!isset($event_id) || $event_id == '') {
		throw new Exception('Event ID cannot be blank', 400);
	}
	return FrcPortal\EventTimeSlot::with('registrations.user')->where('event_id',$event_id)->get();
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
	return $temp;
}

function checkTimeSlotOverlap($timeSlot) {
	$data = FrcPortal\EventTimeSlot::where('event_id',$timeSlot->event_id)->where(function($query) use ($timeSlot){
									$query->where('time_start','>',$timeSlot->time_start)
									->where('time_start','<',$timeSlot->time_end)
									->orWhere(function($query) use ($timeSlot){
										 $query->where('time_end', '<', $timeSlot->time_start);
										 $query->where('time_end', '>', $timeSlot->time_end);
									 })
									->orWhere(function($query) use ($timeSlot){
										 $query->where('time_start', '>=', $timeSlot->time_start);
										 $query->where('time_end', '<=', $timeSlot->time_end);
									 });
								 });
	if(!is_null($timeSlot->time_slot_id)) {
		$data->where('time_slot_id','<>',$timeSlot->time_slot_id);
	}
	return $data->exists();
}
function formatTimeSlot($timeSlot, $formData) {
    $timeSlot->name = $formData['name'];
    $timeSlot->description = isset($formData['description']) ? $formData['description']:'';
    $ts = new DateTime($formData['time_start']);
    $te = new DateTime($formData['time_end']);
    $timeSlot->time_start = $ts->format('Y-m-d H:i:s');
    $timeSlot->time_end = $te->format('Y-m-d H:i:s');
		if(checkTimeSlotOverlap($timeSlot)) {
			throw new Exception('Time Slote cannot overlap an existing slot', 400);
		}
	  return $timeSlot;
}

function updateTimeSlot($event_id, $time_slot_id, $formData) {
	if(!isset($event_id) || $event_id == '') {
		throw new Exception('Event ID is invalid', 400);
	}
	if(!isset($time_slot_id) || $time_slot_id == '') {
		throw new Exception('Time Slot ID is invalid', 400);
	}
	if(!isset($formData) || empty($formData)) {
		throw new Exception('Invalid Time Slot data', 400);
	}
	$timeSlot = FrcPortal\EventTimeSlot::where('event_id',$event_id)->where('time_slot_id',$time_slot_id)->first();
	if(is_null($timeSlot)) {
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
	if(!isset($event_id) || $event_id == '') {
		throw new Exception('Event ID is invalid', 400);
	}
	if(!isset($formData) || empty($formData)) {
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

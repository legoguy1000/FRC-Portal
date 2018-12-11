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
		throw new Exception('Google Calendar Event ID not found');
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
		throw new Exception('Something went wrong updating the event');
	}
	return $event;
}

function getGoogleCalendarEvent($google_cal_id) {
	$calendar = getSettingsProp('google_calendar_id');
	$api_key = getSettingsProp('google_api_key');
	if(!isset($google_cal_id) || $google_cal_id == '') {
		throw new Exception('Google Calendar Event ID not found');
	}
	$client = new Google_Client();
	$client->setDeveloperKey($api_key);
	$service = new Google_Service_Calendar($client);
	$gevent = $service->events->get($calendar, $google_cal_id);
	return formatGoogleCalendarEventData($gevent);
}

function getEventCarList($event_id) {
	$result = array(
		'status' => false,
		'msg' => '',
		'data' => null
	);
	$cars = array();
	$carInfo = array();
	if(isset($event_id) && $event_id != '') {
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
		$result['status'] = true;
		$result['data'] = array('cars'=>$carInfo, 'total'=>count($carInfo), 'car_selection'=>$cars);
	} else {
		$result['msg'] = 'Event ID cannot be blank';
	}
	return $result;
}

function getEventRoomList($event_id) {
	$result = array(
		'status' => false,
		'msg' => '',
		'data' => null
	);
	$rooms = array();
	$roomInfo = array();
	if(isset($event_id) && $event_id != '') {
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
		$result['status'] = true;
		$result['data'] = array('rooms'=>$roomInfo, 'total'=>count($roomInfo), 'room_selection'=>$rooms);
	} else {
		$result['msg'] = 'Event ID cannot be blank';
	}
	return $result;
}

function getEventTimeSlotList($event_id) {
	$result = array(
		'status' => false,
		'msg' => '',
		'data' => null
	);
	$timeSlots = array();
	if(isset($event_id) && $event_id != '') {
		$timeSlots = FrcPortal\EventTimeSlot::with('registrations.user')->where('event_id',$event_id)->get();
		$result['status'] = true;
		$result['data'] = $timeSlots;
	} else {
		$result['msg'] = 'Event ID cannot be blank';
	}
	return $result;
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

function updateTimeSlot($timeSlot, $formData) {
	  $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    $timeSlot->name = $formData['name'];
    $timeSlot->description = isset($formData['description']) ? $formData['description']:'';
    $ts = new DateTime($formData['time_start']);
    $te = new DateTime($formData['time_end']);
    $timeSlot->time_start = $ts->format('Y-m-d H:i:s');
    $timeSlot->time_end = $te->format('Y-m-d H:i:s');
    if($timeSlot->save()) {
      $slots = getEventTimeSlotList($timeSlot->event_id);
	    if($slots['status']) {
	       $responseArr = standardResponse($status = true, $msg = 'Time Slot Updated', $data = $slots['data']);
	    } else {
				$responseArr = $slots;
	    }
	  }
	  return $responseArr;
}

function AddTimeSlot($event_id, $formData) {
	$responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
	$timeSlot = new FrcPortal\EventTimeSlot();
  $timeSlot->event_id = $event_id;
	$update = updateTimeSlot($timeSlot, $formData);
	if($update['status']) {
	  $update['msg'] = 'Time Slot Created';
	}
	return $update;
}
?>

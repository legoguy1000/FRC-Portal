<?php

function eventQuery($sel='',$joins='', $where = '', $order = '') {
	$selStr = isset($sel) && $sel !='' ? ', '.$sel : '';
	$joinStr = isset($joins) && $joins !='' ? ' '.$joins : '';
	$orderStr = isset($order) && $order !='' ? ' '.$order : '';
	$whereStr = isset($where) && $where !='' ? ' '.$where : '';
	$query = 'SELECT events.*, UNIX_TIMESTAMP(events.event_start) AS event_start_unix, UNIX_TIMESTAMP(events.event_end) AS event_end_unix, if(events.registration_date_unix IS NULL,0,UNIX_TIMESTAMP(events.registration_date_unix)) AS registration_date_unix, YEAR(events.event_start) AS year, datediff(events.event_end,events.event_start)+1 as num_days, seasons.game_name, seasons.game_logo '.$selStr.'
			  FROM events
			  LEFT JOIN seasons ON (events.event_start >= seasons.start_date AND events.event_end <= seasons.end_date)
			  '.$joinStr.' '.$whereStr.' '.$orderStr;
	return $query;
}

function formatEventData($event) {
	$data = array();
	if(isset($event) && is_array($event)) {
		$data = $event;
		$data['drivers_required'] = (bool) $data['drivers_required'];
		$data['food_required'] = (bool) $data['food_required'];
		$data['payment_required'] = (bool) $data['payment_required'];
		$data['permission_slip_required'] = (bool) $data['permission_slip_required'];
		$data['room_required'] = (bool) $data['room_required'];
		if(isset($data['car_space'])) {
			$data['car_space'] = (integer) $data['car_space'];
		}
		$data['single_day'] = (bool) (date('Y-m-d',$data['event_start_unix']) == date('Y-m-d',$data['event_end_unix']));
		$data['pocInfo'] = !is_null($data['poc']) ? getUserDataFromParam('user_id',$data['poc']) : json_decode($data['poc_other']);
	}
	return $data;
}

function getAllEvents() {
	$db = db_connect();
	$data = array();
	$query = eventQuery($sel='',$joins='', $where = '', $order = '');
	$result = db_select($query);
	foreach($result as $event) {
		$data[] = formatEventData($event);
	}
	return $data;
}

function getUpcommingEvents() {
	$events = array();
	$where = 'WHERE events.event_start >= '.db_quote(date('Y-m-d')).' AND events.event_start <= '.db_quote(date('Y-m-d'), strtotime('+6 months'));
	$order = 'ORDER BY events.event_start ASC';
	$query = eventQuery($sel='',$joins='', $where, $order);
	$result = db_select($query);
	if(count($result) > 0) {
		foreach($result as $event) {
			$events[] = formatEventData($event);
		}
	}
	return $events;
}

function getEvent($event_id = null, $reqs = false) {
	$data = array();

	if(isset($event_id) && $event_id != '') {
		$where = 'WHERE events.event_id = '.db_quote($event_id);
		$query = eventQuery($sel='',$joins='', $where, $order = '');
		$result = db_select_single($query);
		if($result) {
			$data = formatEventData($result);
			$data['requirements'] = array();
			if($reqs) {
				$reqs = userEventInfo($user_id = null, $year = null, $event_id);
				$data['requirements'] = array('data'=>$reqs, 'total'=>count($reqs));
				//$data['room_list'] = $data['room'] ? getEventRoomList($event_id): array();
			}
		}
	} else {
		return false;
	}
	return $data;
}

function getEventRoomList($event_id) {
	$data = array();
	$rooms = array();
	$roomInfo = array();
	if(isset($event_id) && $event_id != '') {
		$query = 'SELECT * FROM event_rooms WHERE event_id='.db_quote($event_id);
		$result = db_select($query);
		$roomTypeCount = array();
		foreach($result as $room) {
			$temp = $room;
			$roomType = $room['user_type'] == 'Student' ? $room['user_type'].'.'.$room['gender'] : $room['user_type'];
			$roomTypeCount[] = $roomType;
			$c = array_count_values($roomTypeCount);
			$temp['roomType'] = array($roomType);
			$temp['room_title'] = $room['user_type'] == 'Student' ? str_replace('Male',"Boys",str_replace('Female',"Girls",$room['gender'])).' '.$c[$roomType] : $room['user_type'].' '.$c[$roomType];
			$roomInfo[] = $temp;
			$room_id = $room['room_id'];
			$joins = ' RIGHT JOIN event_requirements USING (user_id)';
			$where = ' WHERE event_requirements.room_id = '.db_quote($room_id);
			$uq = userQuery($sel='', $joins, $where, $order='');
			$uqr = db_select_user($uq);
			$rooms[$room_id] = $uqr;
		}
		//no user yet users
		$joins = ' RIGHT JOIN event_requirements USING (user_id)';
		$where = ' WHERE event_requirements.room_id IS NULL AND event_id='.db_quote($event_id);
		$uq = userQuery($sel='', $joins, $where, $order='');
		$uqr = db_select_user($uq);
		$rooms['non_select'] = $uqr;
		$data = array('rooms'=>$roomInfo, 'total'=>count($result), 'room_selection'=>$rooms);
	} else {
		return false;
	}
	return $data;
}

function getEventCarList($event_id) {
	$data = array();
	$rooms = array();
	$roomInfo = array();
	if(isset($event_id) && $event_id != '') {
		$query = 'SELECT event_cars.*, users.fname, users.lname FROM event_cars RIGHT JOIN users USING (user_id) WHERE event_id='.db_quote($event_id);
		$result = db_select($query);
		$roomTypeCount = array();
		foreach($result as $car) {
			$temp = $car;
			$temp['car_space'] = (integer) $temp['car_space'];
			$temp['car_title'] = $temp['fname'].' '.$temp['lname'].' ('.$temp['car_space'].')';
			$carInfo[] = $temp;
			$car_id = $car['car_id'];
			$joins = ' RIGHT JOIN event_requirements USING (user_id)';
			$where = ' WHERE event_requirements.car_id = '.db_quote($car_id);
			$uq = userQuery($sel='', $joins, $where, $order='');
			$uqr = db_select_user($uq);
			$cars[$car_id] = $uqr;
		}
		//no user yet users
		$joins = ' RIGHT JOIN event_requirements USING (user_id)';
		$where = ' WHERE event_requirements.car_id IS NULL AND event_id='.db_quote($event_id);
		$uq = userQuery($sel='', $joins, $where, $order='');
		$uqr = db_select_user($uq);
		$cars['non_select'] = $uqr;
		$data = array('cars'=>$carInfo, 'total'=>count($result), 'car_selection'=>$cars);
	} else {
		return false;
	}
	return $data;
}

function getAllEventsFilter($filter = '', $limit = 10, $order = '-event_start', $page = 1) {

	/* if(isset($filter) && $filter != '') {
		$filter = $filter;
	}
	if(isset($limit) && $limit != '') {
		$limit = $limit;
	}
	if(isset($order) && $order != '') {
		$order = $order;
	}
	if(isset($page) && $page != '') {
		$page = $page;
	} */

	$events = array();
	$data = array();
//	$defaultParams = defaultTableParams();
	$queryArr = array();
	$queryStr = '';
	if($filter != '') {
		$queryArr[] = '(events.name LIKE '.db_quote('%'.$filter.'%').')';
		$queryArr[] = '(events.type LIKE '.db_quote('%'.$filter.'%').')';
		$queryArr[] = '(events.event_start LIKE '.db_quote('%'.$filter.'%').')';
		$queryArr[] = '(events.event_end LIKE '.db_quote('%'.$filter.'%').')';
		$queryArr[] = '(seasons.game_name LIKE '.db_quote('%'.$filter.'%').')';
		$queryArr[] = '(seasons.year LIKE '.db_quote('%'.$filter.'%').')';
		//Date Filters
		$queryArr[] = '(MONTHNAME(events.event_start) LIKE '.db_quote('%'.$filter.'%').')';
		$queryArr[] = '(MONTHNAME(events.event_end) LIKE '.db_quote('%'.$filter.'%').')';
	}

	if(count($queryArr) > 0) {
		$queryStr = 'WHERE '.implode(' OR ',$queryArr);
	}

	$orderBy = '';
	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
	if(in_array($orderCol,array('game_name','year','event_start','event_end', 'name', 'type'))) {
		$orderBy = 'ASC';
		if($order[0] == '-') {
			$orderBy = 'DESC';
		}
	}
	$where = $queryStr;
	$query = eventQuery($sel='',$joins='', $where, $order = '');

	$result = db_select($query);
	$totalNum = count($result);

	$offset	= ($page - 1) * $limit;

	$order = ' ORDER BY '.$orderCol.' '.$orderBy.' LIMIT '.$offset.', '.$limit;
	$query = eventQuery($sel='',$joins='', $where, $order);
	//die($query);
	$result = db_select($query);
	if(count($result) > 0) {
		foreach($result as $event) {
			$events[] = formatEventData($event);
		}
	}
	$data['data'] = $events;
	$data['query'] = $query;
	$data['total'] = $totalNum;
	$data['maxPage'] = ceil($totalNum/$limit);

	return $data;
}

function syncGoogleCalendarEvent($google_cal_id, $event_id) {
	$calendar = getIniProp('google_calendar_id');
	$api_key = getIniProp('google_api_key');
	$result = false;
	if(isset($google_cal_id) && $google_cal_id != '' && isset($event_id) && $event_id != '') {
		$client = new Google_Client();
		$client->setDeveloperKey($api_key);
		$service = new Google_Service_Calendar($client);
		$gevent = $service->events->get($calendar, $google_cal_id);
		$name = $gevent->summary;
		$details = $gevent->description;
		if(empty($gevent->start->dateTime)) {
			$event_start = $gevent->start->date.' 00:00:00';
			$event_end = $gevent->end->date.' 23:59:59';
		} else {
			$event_start = date('Y-m-d H:i:s', strtotime($gevent->start->dateTime));
			$event_end =date('Y-m-d H:i:s', strtotime($gevent->end->dateTime));
		}
		$location = $gevent->location;
		//echo json_encode($event);
		$query = 'UPDATE events SET name='.db_quote($name).', details='.db_quote($details).', event_start='.db_quote($event_start).', event_end='.db_quote($event_end).', location='.db_quote($location).' WHERE event_id='.db_quote($event_id);
		$result = db_query($query);
	}
	return $result;
}
?>

<?php

function eventQuery($sel='',$joins='', $where = '', $order = '') {
	$selStr = isset($sel) && $sel !='' ? ', '.$sel : '';
	$joinStr = isset($joins) && $joins !='' ? ' '.$joins : '';
	$orderStr = isset($order) && $order !='' ? ' '.$order : '';
	$whereStr = isset($where) && $where !='' ? ' '.$where : '';
	$query = 'SELECT events.*, UNIX_TIMESTAMP(events.event_start) AS event_start_unix, UNIX_TIMESTAMP(events.event_end) AS event_end_unix, YEAR(events.event_start) AS year, datediff(events.event_end,events.event_start)+1 as num_days, seasons.game_name, seasons.game_logo '.$selStr.'
			  FROM events
			  LEFT JOIN seasons ON (events.event_start >= seasons.start_date AND events.event_end <= seasons.end_date)
			  '.$joinStr.' '.$whereStr.' '.$orderStr;
	return $query;
}

function formatEventData($event) {
	$data = array();
	if(isset($event) && is_array($$eventuser)) {
		$data = $event;
		$data['drivers'] = (bool) $data['drivers'];
		$data['food'] = (bool) $data['food'];
		$data['payment'] = (bool) $data['payment'];
		$data['permission_slip'] = (bool) $data['permission_slip'];
		$data['room'] = (bool) $data['room'];
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
			}
		}
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
?>

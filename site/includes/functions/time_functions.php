<?php

function getAllSignInsFilter($filter = '', $limit = 10, $order = '-time_in', $page = 1) {

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

	$users = array();
	$data = array();
	$queryArr = array();
	$queryStr = '';
	if($filter != '') {
		$queryArr[] = '(full_name LIKE '.db_quote('%'.$filter.'%').')';
		$queryArr[] = '(users.email LIKE '.db_quote('%'.$filter.'%').')';
		$queryArr[] = '(hours LIKE '.db_quote('%'.$filter.'%').')';
	}

	if(count($queryArr) > 0) {
		$queryStr = ' HAVING '.implode(' OR ',$queryArr);
	}

	$orderBy = '';
	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
	if(in_array($orderCol,array('full_name','time_in','time_out','hours'))) {
		$orderBy = 'ASC';
		if($order[0] == '-') {
			$orderBy = 'DESC';
		}
	}
	$sel='mh.time_in, mh.time_out, UNIX_TIMESTAMP(mh.time_in) AS time_in_unix, UNIX_TIMESTAMP(mh.time_out) AS time_out_unix, (time_to_sec(IFNULL(timediff(mh.time_out, mh.time_in),0)) / 3600) as hours';
	$joins='RIGHT JOIN meeting_hours mh USING (user_id)';
	$where = $queryStr;
	$query = userQuery($sel,$joins, $where, $order = '');
	$result = db_select($query);
	$totalNum = count($result);

	$offset	= ($page - 1) * $limit;
	$order = 'ORDER BY '.$orderCol.' '.$orderBy.' LIMIT '.$offset.', '.$limit;
	$query = userQuery($sel,$joins, $where, $order);
	//die($query);
	$result = db_select($query);
	if(count($result) > 0) {
		foreach($result as $user) {
			$temp = formatUserData($user);
			$users[] = $temp;
		}
	}
	$data['data'] = $users;
	$data['query'] = $query;
	$data['total'] = $totalNum;
	$data['maxPage'] = ceil($totalNum/$limit);

	return $data;
}

function getAllMissingHoursRequestsFilter($filter = '', $limit = 10, $order = 'full_name', $page = 1) {

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

	$users = array();
	$data = array();
	$queryArr = array();
	$queryStr = '';
	if($filter != '') {
		$queryArr[] = '(full_name LIKE '.db_quote('%'.$filter.'%').')';
	}

	if(count($queryArr) > 0) {
		$queryStr = ' HAVING '.implode(' OR ',$queryArr);
	}

	$orderBy = '';
	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
	if(in_array($orderCol,array('full_name'))) {
		$orderBy = 'ASC';
		if($order[0] == '-') {
			$orderBy = 'DESC';
		}
	}
	$sel='mhr.*, UNIX_TIMESTAMP(mhr.time_in) AS time_in_unix, UNIX_TIMESTAMP(mhr.time_out) AS time_out_unix, (time_to_sec(IFNULL(timediff(mhr.time_out, mhr.time_in),0)) / 3600) as hours';
	$joins='RIGHT JOIN missing_hours_requests mhr USING (user_id)';
	$where = $queryStr;
	$query = userQuery($sel,$joins, $where, $order = '');
	$result = db_select($query);
	$totalNum = count($result);

	$offset	= ($page - 1) * $limit;
	$order = 'ORDER BY '.$orderCol.' '.$orderBy.' LIMIT '.$offset.', '.$limit;
	$query = userQuery($sel,$joins, $where, $order);
	//die($query);
	$result = db_select($query);
	if(count($result) > 0) {
		foreach($result as $user) {
			$temp = formatUserData($user);
			$temp['approved'] = (bool) $temp['approved'];
			$users[] = $temp;
		}
	}
	$data['data'] = $users;
	$data['query'] = $query;
	$data['total'] = $totalNum;
	$data['maxPage'] = ceil($totalNum/$limit);

	return $data;
}




 ?>

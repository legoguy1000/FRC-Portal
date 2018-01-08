<?php

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
	$userQuery = userQuery();
	$defaultParams = defaultTableParams();
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
	$where = $queryStr;
	$query = userQuery($sel='',$joins='', $where, $order = '');
	$result = db_select($query);
	$totalNum = count($result);

	$offset	= ($page - 1) * $limit;
	$order = 'ORDER BY '.$orderCol.' '.$orderBy.' LIMIT '.$offset.', '.$limit;
	$query = userQuery($sel='',$joins='', $where, $order);
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




 ?>

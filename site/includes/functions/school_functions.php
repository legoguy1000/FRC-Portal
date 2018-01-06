<?php

function schoolQuery($sel='',$joins='', $where = '', $order = '') {
	$selStr = isset($sel) && $sel !='' ? ', '.$sel : '';
	$joinStr = isset($joins) && $joins !='' ? ' '.$joins : '';
	$orderStr = isset($order) && $order !='' ? ' '.$order : '';
	$whereStr = isset($where) && $where !='' ? ' '.$where : '';
	$query = 'SELECT schools.*, IFNULL(sc.student_count,0) as student_count '.$selStr.'
			  FROM schools 
			  LEFT JOIN (SELECT school_id, COUNT(*) as student_count FROM users GROUP BY school_id) sc USING (school_id)
			  '.$joinStr.' '.$whereStr.' '.$orderStr;
	return $query;
}

function getAllSchools() {
	$db = db_connect();
	$data = array();
	$query = 'SELECT schools.* FROM schools';
	$schools = db_select($query);
	foreach($schools as $school) {
		$data[] = $school;
	}
	return $data;
}

function searchAllSchools($search) {
	$db = db_connect();
	$data = array();
	$query = 'SELECT schools.* FROM schools WHERE school_name LIKE '.db_quote('%'.$search.'%').' OR abv LIKE '.db_quote('%'.$search.'%');
	$schools = db_select($query);
	if(count($schools) > 0) {
		foreach($schools as $school) {
			$data[] = $school;
		}
	}
	return $data;
}
function getAllSchoolsFilter($filter = '', $limit = 10, $order = 'school_name', $page = 1) {
	
	if(!isset($filter) || $filter == '') {
		$filter = '';
	}
	if((!isset($limit) || $limit == '') && $limit != 0) {
		$limit = 10;
	}
	if(!isset($order) || $order == '') {
		$order = 'school_name';
	}
	if(!isset($page) || $page == '') {
		$page = 1;
	}

	$schools = array();
	$data = array();
	//$defaultParams = defaultTableParams();
	$queryArr = array();
	$orderStr = '';
	$whereStr = '';
	$limitStr = '';
	
	if($filter != '') {
		$queryArr[] = '(schools.game_name LIKE '.db_quote('%'.$filter.'%').')';
		$queryArr[] = '(schools.year LIKE '.db_quote('%'.$filter.'%').')';
	}
	
	if(count($queryArr) > 0) {
		$whereStr = ' WHERE '.implode(' OR ',$queryArr);
	}
	
	$orderBy = '';
	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
	if(in_array($orderCol,array('school_name','abv','student_count'))) {
		$orderBy = 'ASC';
		if($order[0] == '-') {
			$orderBy = 'DESC';
		}
	}

	$query = schoolQuery('','', $whereStr, '');
	
	$result = db_select($query);
	$totalNum = count($result);
	//die($totalNum);
	if($limit > 0) {
		$offset	= ($page - 1) * $limit;	
		$limitStr = 'LIMIT '.$offset.', '.$limit;
	}
	 
	$orderStr = ' ORDER BY '.$orderCol.' '.$orderBy.' '.$limitStr;
	$query = schoolQuery('','', $whereStr, $orderStr);
	//die($query);
	$result = db_select($query);	
	if(count($result) > 0) {
		foreach($result as $school) {
			$schools[] = $school;
		}
	}
	$data['data'] = $schools;
	$data['query'] = $query;
	$data['total'] = $totalNum;
	$data['maxPage'] = $limit > 0 ? ceil($totalNum/$limit) : 0;
	
	return $data;
}
?>

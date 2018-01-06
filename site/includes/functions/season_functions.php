<?php

function seasonQuery($sel='',$joins='', $where = '', $order = '') {
	$selStr = isset($sel) && $sel !='' ? ', '.$sel : '';
	$joinStr = isset($joins) && $joins !='' ? ' '.$joins : '';
	$orderStr = isset($order) && $order !='' ? ' '.$order : '';
	$whereStr = isset($where) && $where !='' ? ' '.$where : '';
	$query = 'SELECT seasons.* '.$selStr.'
			  FROM seasons 
			  '.$joinStr.' '.$whereStr.' '.$orderStr;
	return $query;
}

function getAllSeasons() {

	$data = array();
	$query = seasonQuery($sel='',$joins='', $where = '', $order = '');
	$seasons = db_select($query);
	foreach($seasons as $seas) {
		$data[] = $seas;
	}
	return $data;
}

function getSeason($season_id = null, $reqs = false) {
	$data = array();
	
	if(isset($season_id) && $season_id != '') {
		$where = 'WHERE seasons.season_id = '.db_quote($season_id);
		$query = seasonQuery($sel='',$joins='', $where, $order = '');
		$result = db_select_single($query);
		if($result) {
			$data = $result;
			$data['hour_requirement'] = (integer) $data['hour_requirement'];
			$data['requirements'] = array();
			if($reqs) {
				$reqs = userSeasonInfo($user_id = null, $year = $result['year']);
				$data['requirements'] = array('data'=>$reqs, 'total'=>count($reqs));
			}
			
			
			
		}
	} else {
		return false;
	}
	return $data;
}

function activeSeasonUsers($season = null) {
	
	
}


function getAllSeasonsFilter($filter = '', $limit = 10, $order = '-year', $page = 1) {
	
	if(!isset($filter) || $filter == '') {
		$filter = '';
	}
	if((!isset($limit) || $limit == '') && $limit != 0) {
		$limit = 10;
	}
	if(!isset($order) || $order == '') {
		$order = '-year';
	}
	if(!isset($page) || $page == '') {
		$page = 1;
	}

	$seasons = array();
	$data = array();
	$defaultParams = defaultTableParams();
	$queryArr = array();
	$orderStr = '';
	$whereStr = '';
	$limitStr = '';
	
	if($filter != '') {
		$queryArr[] = '(seasons.game_name LIKE '.db_quote('%'.$filter.'%').')';
		$queryArr[] = '(seasons.year LIKE '.db_quote('%'.$filter.'%').')';
	}
	
	if(count($queryArr) > 0) {
		$whereStr = ' WHERE '.implode(' OR ',$queryArr);
	}
	
	$orderBy = '';
	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
	if(in_array($orderCol,array('game_name','year','start_date','bag_day','end_date'))) {
		$orderBy = 'ASC';
		if($order[0] == '-') {
			$orderBy = 'DESC';
		}
	}

	$query = seasonQuery('','', $whereStr, '');
	
	$result = db_select($query);
	$totalNum = count($result);
	//die($totalNum);
	if($limit > 0) {
		$offset	= ($page - 1) * $limit;	
		$limitStr = 'LIMIT '.$offset.', '.$limit;
	}
	 
	$orderStr = ' ORDER BY '.$orderCol.' '.$orderBy.' '.$limitStr;
	$query = seasonQuery('','', $whereStr, $orderStr);

	$result = db_select($query);	
	if(count($result) > 0) {
		foreach($result as $seas) {
			$seasons[] = $seas;
		}
	}
	$data['data'] = $seasons;
	$data['query'] = $query;
	$data['total'] = $totalNum;
	$data['maxPage'] = $limit > 0 ? ceil($totalNum/$limit) : 0;
	
	return $data;
}
?>

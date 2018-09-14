<?php

function topHourUsers($year) {
	if(!isset($year) || $year == '') {
		$year = date('Y');
	}

	$data = array();
	$sel = 'a.*';
	$joins = ' LEFT JOIN (SELECT mh.*,  ROUND(SUM(time_to_sec(timediff(mh.time_out, mh.time_in)) / 3600),1) as hours, year(mh.time_in) as year FROM meeting_hours mh WHERE year(mh.time_in) = '.db_quote($year).' GROUP BY mh.user_id ) a USING (user_id) ';
	$where = 'WHERE a.hours > 0';
	$order = 'ORDER BY a.hours DESC,users.lname ASC LIMIT 5';
	$query = userQuery($sel,$joins, $where, $order);
	$result = db_select_user($query);
	if($result) {
	/*	foreach($result as $id=>$res) {
			$result[$id] = formatUserData($res);
		} */
		$data = $result;
	}

	return $data;
}

function checkReportInputs($request, $response, type = 'range') {
    if($type == 'range') {
	    if($request->getParam('start_date') == null|| $request->getParam('start_date') == '' || !is_numeric($request->getParam('start_date'))) {
		return badRequestResponse($response, $msg = 'Invalid Start Date');
	    }
	    if($request->getParam('end_date') == null || $request->getParam('end_date') == '' || !is_numeric($request->getParam('end_date'))) {
		return badRequestResponse($response, $msg = 'Invalid End Date');
	    }    
    } elseif($type == 'single') {
	    if($request->getParam('year') == null|| $request->getParam('year') == '' || !is_numeric($request->getParam('year'))) {
		return badRequestResponse($response, $msg = 'Invalid Year');
	    }
    }
    return true;
}

?>

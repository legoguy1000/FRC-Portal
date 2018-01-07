<?php

function topHourUsers($year) {
	if(!isset($year) || $year == '') {
		$year = date('Y');
	}

	$data = array();
	$sel = 'a.*';
	$joins = ' LEFT JOIN (SELECT mh.*, SUM(time_to_sec(timediff(mh.time_out, mh.time_in)) / 3600) as hours, year(mh.time_in) as year FROM meeting_hours mh WHERE year(mh.time_in) = '.db_quote($year).' GROUP BY mh.user_id ) a USING (user_id) ';
	$where = '';
	$order = 'ORDER BY a.hours DESC,a.time_in DESC LIMIT 5';
	$query = userQuery($sel,$joins, $where, $order);
	$result = db_select($query);
	$data = $result ? $result : array();
	return $data;
}


?>

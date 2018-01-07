<?php

function topHourUsers($year) {
	if(!isset($year) || $year == '') {
		$year = date('Y');
	}

	$data = array();
	$sel = 'a.*';
	$joins = ' LEFT JOIN (SELECT SUM(time_to_sec(timediff(mh.time_out, mh.time_in)) / 3600) as hours, year(mh.time_in) as year FROM meeting_hours mh) a USING (user_id) ';
	$where = 'WHERE year(a.time_in) = '.db_quote($year).' GROUP BY a.user_id ';
	$order = 'ORDER BY hours DESC,RAND() LIMIT 5';
	$query = userQuery($sel,$joins, $where, $order);
	$result = db_select($query);
	$data = $result ? $result : array();
	return $data;
}


?>

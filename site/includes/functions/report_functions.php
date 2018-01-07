<?php

function topHourUsers($year) {
	if(!isset($year) || $year == '') {
		$year = date('Y');
	}

	$data = array();
	$query = 'SELECT SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600) as hours, year(a.time_in) as year, u.* FROM meeting_hours a LEFT JOIN users u USING (user_id) WHERE year(a.time_in) = '.db_quote($year).' GROUP BY a.user_id ORDER BY hours DESC,RAND() LIMIT 5';
	$result = db_select($query);
	$data = $result ? $result : array();
	return $data;
}


?>

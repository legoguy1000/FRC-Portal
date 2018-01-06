<?php

function topHourUsers($year) {
	if(!isset($year) || $year == '') {
		$year = date('Y');
	}

	$sel = '';
	$joins = '';
	$where = '';
	$order = '';
	$query = 'SELECT SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600) as hours, year(a.time_in) as year, u.* FROM meeting_hours a LEFT JOIN users u USING (user_id) HAVING year = '.db_quote($year).' GROUP BY u.user_id,year ORDER BY year DESC, hours DESC LIMIT 5';
	$result = db_select($query);
	return $result;
}


?>

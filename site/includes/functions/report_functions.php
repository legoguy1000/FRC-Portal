<?php

function topHourUsers($year) {
	if(!isset($year) || $year == '') {
		$year = date('Y');
	}

	$sel = 'SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600) as hours, year(a.time_in) as year';
	$joins = 'LEFT JOIN meeting_hours a USING (user_id) GROUP BY user_id,year';
	$where = 'HAVING year = '.db_quote($year).'';
	$order = 'ORDER BY year DESC, hours DESC LIMIT 5';
	$query = userQuery($sel,$joins, $where, $order);

	/* $query = 'SELECT users.*, CONCAT(users.fname," ",users.lname) AS full_name, schools.school_name, SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600) as hours, year(a.time_in) as year
			  FROM users LEFT JOIN schools USING (school_id)
			  LEFT JOIN meeting_hours a USING (user_id)
			  GROUP BY user_id,year HAVING year = '.db_quote($year).' ORDER BY year DESC, hours DESC'; */
	$result = db_select($query);
	return $result;
}


?>

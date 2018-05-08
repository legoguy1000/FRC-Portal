<?php
function userQuery($sel='',$joins='', $where = '', $order = '') {
	$selStr = isset($sel) && $sel !='' ? ', '.$sel : '';
	$joinStr = isset($joins) && $joins !='' ? ' '.$joins : '';
	$orderStr = isset($order) && $order !='' ? ' '.$order : '';
	$whereStr = isset($where) && $where !='' ? ' '.$where : '';
	$query = 'SELECT users.*,
					 CONCAT(users.fname," ",users.lname) AS full_name,
					 schools.*,
					 CASE
						WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=0  THEN "Graduated"
						WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=12 THEN "Senior"
						WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=24 THEN "Junior"
						WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=36 THEN "Sophmore"
						WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=48 THEN "Freshman"
						WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) >48 THEN "Pre-Freshman"
						ELSE ""
					 END AS student_grade
					 '.$selStr.'
			  FROM users
			  LEFT JOIN schools USING (school_id)
			  '.$joinStr.' '.$whereStr.' '.$orderStr;
	return $query;
}

/* function annualRequirementsQueryArr($l = 'annual_requirements') {

	$sel = 'IFNULL('.$l.'.join_team,0) AS join_team, IFNULL('.$l.'.stims,0) AS stims, IFNULL('.$l.'.dues,0) AS dues';
	$joins = 'CROSS JOIN seasons b';
	$joins .= ' LEFT JOIN annual_requirements '.$l.' USING (user_id,season_id)';

	$data = array(
		'selects' => $sel,
		'joins' => $joins
	);
	return $data;
}

function userHoursQueryArr($l = 'off_season_hours', $q = 'on_season_hours') {

	$sel =  'IFNULL('.$l.'.off_season_hours,0) AS off_season_hours, IFNULL('.$q.'.season_hours,0) AS season_hours, (IFNULL('.$l.'.off_season_hours,0)+IFNULL('.$q.'.season_hours,0)) AS total';
	$joins = ' LEFT JOIN (SELECT meeting_hours.user_id,year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS off_season_hours, seasons.* FROM meeting_hours LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in) WHERE meeting_hours.time_in>seasons.end_date GROUP BY meeting_hours.user_id,seasons.year) '.$l.' USING (user_id,year)';
	$joins .= ' LEFT JOIN (SELECT meeting_hours.user_id,year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS season_hours, seasons.* FROM meeting_hours LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in) WHERE meeting_hours.time_in>=seasons.start_date AND meeting_hours.time_in<=seasons.end_date GROUP BY meeting_hours.user_id,seasons.year) '.$q.' USING (user_id,year)';

	$data = array(
		'selects' => $sel,
		'joins' => $joins
	);
	return $data;
} */

/*function userHoursAnnualRequirementsQueryArr($b = 'seasons', $l = 'annual_requirements', $c = 'off_season_hours', $d = 'on_season_hours') {

	$sel =  $b.'.*';
	$joins = 'CROSS JOIN seasons '.$b;
	if($l != false) {
		$sel .= ', IFNULL('.$l.'.join_team,0) AS join_team,
							 IFNULL('.$l.'.stims,0) AS stims,
							 IFNULL('.$l.'.dues,0) AS dues';
		$joins .= ' LEFT JOIN annual_requirements '.$l.' USING (user_id,season_id)';
	}
	if($c != false) {
		$sel .= ',  ROUND(IFNULL('.$c.'.off_season_hours,0),1) AS off_season_hours';
		$joins .= ' LEFT JOIN (SELECT meeting_hours.user_id,year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS off_season_hours, seasons.* FROM meeting_hours LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in) WHERE meeting_hours.time_in>seasons.end_date GROUP BY meeting_hours.user_id,seasons.year) '.$c.' USING (user_id,year)';
	}
	if($d != false) {
		$sel .= ',  ROUND(IFNULL('.$d.'.season_hours,0),1) AS season_hours,
								ROUND(IFNULL('.$d.'1.season_hours_exempt,0),1) AS season_hours_exempt,
								IFNULL('.$d.'1.season_hours_exempt >= '.$b.'.hour_requirement,0) AS min_hours';
		$joins .= ' LEFT JOIN (SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS season_hours, seasons.* FROM meeting_hours LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in) WHERE meeting_hours.time_in>=seasons.start_date AND meeting_hours.time_in<=seasons.end_date GROUP BY meeting_hours.user_id,seasons.year) '.$d.' USING (user_id,year)';
		$joins .= ' LEFT JOIN (SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS season_hours_exempt, seasons.*,exempt_hours.exempt_id FROM meeting_hours LEFT JOIN exempt_hours ON meeting_hours.time_in >= DATE_SUB(exempt_hours.time_start, INTERVAL 1 HOUR) AND meeting_hours.time_out < DATE_ADD(exempt_hours.time_end, INTERVAL 1 HOUR) LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in) WHERE meeting_hours.time_in>=seasons.start_date AND meeting_hours.time_in<=seasons.end_date  AND exempt_hours.exempt_id IS NULL GROUP BY meeting_hours.user_id,seasons.year) '.$d.'1 USING (user_id,year)';
	}
	if($c != false && $d != false) {
		$sel .= ',  ROUND((IFNULL('.$c.'.off_season_hours,0)+IFNULL('.$d.'.season_hours,0)),1) AS total';
	}
	$data = array(
		'selects' => $sel,
		'joins' => $joins
	);
	return $data;
} */

function userHoursAnnualRequirementsQueryArr($b = 'seasons', $l = 'annual_requirements', $c = 'hours') {

	$sel =  $b.'.*';
	$joins = 'CROSS JOIN seasons '.$b;
	if($l != false) {
		$sel .= ', IFNULL('.$l.'.join_team,0) AS join_team,
							 IFNULL('.$l.'.stims,0) AS stims,
							 IFNULL('.$l.'.dues,0) AS dues';
		$joins .= ' LEFT JOIN annual_requirements '.$l.' USING (user_id,season_id)';
	}
	if($c != false) {
		$sel .= ',  ROUND(IFNULL('.$c.'.off_season_hours,0),1) AS off_season_hours';
		$sel .= ',	ROUND(IFNULL('.$c.'1.build_season_hours,0),1) AS build_season_hours';
		$sel .= ',	ROUND(IFNULL('.$c.'2.competition_season_hours,0),1) AS competition_season_hours';
		//$sel .= ',	ROUND(IFNULL('.$c.'3.season_hours_exempt,0),1) AS season_hours_exempt';
		$sel .= ',	IFNULL('.$c.'1.build_season_hours >= '.$b.'.hour_requirement,0) AS min_hours';
		$sel .= ',	ROUND(IFNULL('.$c.'3.total_hours,0),1) AS total_hours';

		$joins .= ' LEFT JOIN (SELECT meeting_hours.user_id,year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS off_season_hours, seasons.* FROM meeting_hours LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in) WHERE meeting_hours.time_in>seasons.end_date GROUP BY meeting_hours.user_id,seasons.year) '.$c.' USING (user_id,year)';
		$joins .= ' LEFT JOIN (SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS build_season_hours, seasons.*,exempt_hours.exempt_id FROM meeting_hours LEFT JOIN exempt_hours ON meeting_hours.time_in >= DATE_SUB(exempt_hours.time_start, INTERVAL 1 HOUR) AND meeting_hours.time_out < DATE_ADD(exempt_hours.time_end, INTERVAL 1 HOUR) LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in) WHERE meeting_hours.time_in>=seasons.start_date AND meeting_hours.time_in<=seasons.bag_day  AND exempt_hours.exempt_id IS NULL GROUP BY meeting_hours.user_id,seasons.year) '.$c.'1 USING (user_id,year)';
		//$joins .= ' LEFT JOIN (SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS build_season_hours, seasons.* FROM meeting_hours LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in) WHERE meeting_hours.time_in>=seasons.start_date AND meeting_hours.time_in<=seasons.bag_day GROUP BY meeting_hours.user_id,seasons.year) '.$c.'1 USING (user_id,year)';
		$joins .= ' LEFT JOIN (SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS competition_season_hours, seasons.* FROM meeting_hours LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in) WHERE meeting_hours.time_in>seasons.bag_day AND meeting_hours.time_in<=seasons.end_date GROUP BY meeting_hours.user_id,seasons.year) '.$c.'2 USING (user_id,year)';
		$joins .= ' LEFT JOIN (SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS total_hours, seasons.* FROM meeting_hours LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in) GROUP BY meeting_hours.user_id,seasons.year) '.$c.'3 USING (user_id,year)';

	}
	$data = array(
		'selects' => $sel,
		'joins' => $joins
	);
	return $data;
}

function userEventRequirementsQueryArr($b = 'events', $l = 'event_requirements') {

	$sel =  $b.'.*, year('.$b.'.event_start) as year, UNIX_TIMESTAMP('.$b.'.event_start) AS event_start_unix, UNIX_TIMESTAMP('.$b.'.event_end) AS event_end_unix, datediff('.$b.'.event_end,'.$b.'.event_start)+1 as num_days';
	$joins = 'CROSS JOIN events '.$b;
	if($l != false) {
		$sel .= ', IFNULL('.$l.'.registration,0) AS registration, IFNULL('.$l.'.payment,0) AS payment, IFNULL('.$l.'.permission_slip,0) AS permission_slip, IFNULL('.$l.'.food,0) AS food, '.$l.'.room_id, IFNULL('.$l.'.can_drive,0) AS can_drive, '.$l.'.car_id, IF('.$l.'.car_id IS NULL,0,1) as car_bool, '.$l.'.comments';
		$joins .= ' LEFT JOIN event_requirements '.$l.' USING (user_id,event_id)';
		$sel .= ', '.$l.'_room.room_title, IF('.$l.'_room.room_title IS NULL,0,1) as room_bool';
		$joins .= ' LEFT JOIN event_rooms '.$l.'_room USING (event_id,room_id)';
		$sel .= ', '.$l.'_car.car_space';
		$joins .= ' LEFT JOIN event_cars '.$l.'_car USING (user_id)';
	}
	$data = array(
		'selects' => $sel,
		'joins' => $joins
	);
	return $data;
}

function userSignInList() {
	$data = array();

	$reqsQuery = userHoursAnnualRequirementsQueryArr($b = 'b', $l = false, $c = 'c');
	$sel = $reqsQuery['selects'];
	$joins = $reqsQuery['joins'];

	$sel .= ', a.time_in, a.time_out, UNIX_TIMESTAMP(a.time_in) AS time_in_unix, UNIX_TIMESTAMP(a.time_out) AS time_out_unix';
	$joins .= ' LEFT JOIN meeting_hours a ON a.hours_id = (SELECT hours_id from meeting_hours WHERE meeting_hours.user_id=users.user_id ORDER BY time_in DESC LIMIT 1)';

	$where = 'WHERE users.status = "1" AND b.year='.date('Y');
	$order = 'ORDER BY users.lname ASC';
	$query = userQuery($sel, $joins, $where, $order);
	$result = db_select_user($query);
	//die($query);
	if(count($result > 0)) {
		$data = $result;
	}
	return $data;
}

function userSeasonInfo($user_id = null, $year = null, $return=array()) {
	$data = array();
	//$reqsQuery = userHoursAnnualRequirementsQueryArr($b = 'b', $l = 'e', $c = 'c', $d = 'd');
	$reqsQuery = userHoursAnnualRequirementsQueryArr($b = 'b', $l = 'e', $c = 'c');

	$defaultFilterilterArr = array(
		'join_team',
		'stims',
		'dues',
		'min_hours',
		'user_type',
		'user_id',
		'full_name',
		'build_season_hours',
		'competition_season_hours',
		'off_season_hours',
		'total_hours',
		'min_hours',
		'year',
		'game_logo',
		'game_name'
	);
	$filterArr = array_unique(array_merge($defaultFilterilterArr, $return));
	$where = '';
	$whereArr = array('(users.status = "1" OR e.req_id IS NOT NULL)');
	$sel = $reqsQuery['selects'];
	$joins = $reqsQuery['joins'];
	if($user_id != null) {
		$whereArr[] = 'users.user_id = '.db_quote($user_id);
	}
	if($year != null) {
		$whereArr[] = 'b.year = '.db_quote($year);
	}
	if(!empty($whereArr)) {
		$where = 'WHERE '.implode(' AND ',$whereArr);
	}
	$order = 'ORDER BY users.lname ASC, b.year DESC';
	$query = userQuery($sel, $joins, $where, $order);

	$result = db_select($query);
	if(count($result > 0)) {
		foreach($result as $id=>$user) {
			$temp = filterArrayData ($user, $filterArr);
			$jt = (bool) $temp['join_team'];
			$stims = (bool) $temp['stims'];
			$dues = (bool) $temp['dues'];
			$mh = (integer) $temp['min_hours'];
			$stu = (bool) $temp['user_type'] == 'Student';
			$men = (bool) $temp['user_type'] == 'Mentor';
			$temp['reqs_complete'] = $jt && $stims && (($stu && $dues) || $men) && $mh;
			$data[] = formatUserData($temp);
		}
	}
	return $data;
}

function userEventInfo($user_id = null, $year = null, $event = null, $return=array()) {
	$data = array();
	$reqsQuery = userEventRequirementsQueryArr($b = 'b', $l = 'e');

	$defaultFilterilterArr = array(
		'user_id',
		'full_name',
		'user_type',
		'event_id',
		'registration',
		'payment',
		'permission_slip',
		'food',
		'room_id',
		'room_bool',
		'can_drive',
		'car_id',
		'car_bool',
		'comments',
		'car_space'
	);
	$filterArr = array_unique(array_merge($defaultFilterilterArr, $return));

	$where = '';
	$whereArr = array('(users.status = "1" OR e.ereq_id IS NOT NULL)');
	$sel = $reqsQuery['selects'];
	$joins = $reqsQuery['joins'];

	if($user_id != null) {
		$whereArr[] = 'users.user_id = '.db_quote($user_id);
	}
	if($event != null) {
		$whereArr[] = 'b.event_id = '.db_quote($event);
	} else if($year != null) {
		$whereArr[] = 'YEAR(b.event_start) = '.db_quote($year);
	}
	if(!empty($whereArr)) {
		$where = 'WHERE '.implode(' AND ',$whereArr);
	}
	$order = 'ORDER BY users.lname ASC, b.event_start DESC';
	$query = userQuery($sel, $joins, $where, $order);
	//die($query);
	$result = db_select_user($query);
	if(count($result) > 0) {
		foreach($result as $event) {
			$temp = formatEventData($event);
			$data[]= filterArrayData($temp, $filterArr);
		}
	}
	return $data;
}

function userAnnualRequirements($user_id = null, $year = null) {

	$data = array();
	$reqsQuery = userHoursAnnualRequirementsQueryArr($b = 'seasons', $l = 'annual_requirements', $c = false);
	$where = '';
	$whereArr = array();
	$sel = $reqsQuery['selects'];
	$joins = $reqsQuery['joins'];
	if($user_id != null) {
		$whereArr[] = 'users.user_id = '.db_quote($user_id);
	}
	if($year != null) {
		$whereArr[] = 'seasons.year = '.db_quote($year);
	}
	if(!empty($whereArr)) {
		$where = 'WHERE '.implode(' AND ',$whereArr);
	}
	$order = 'ORDER BY users.lname ASC, b.year DESC';
	$query = userQuery($sel, $joins, $where, $order);
	$result = db_select_user($query);
	if(count($result > 0)) {
		foreach($result as $id=>$temp) {
			$jt = $temp['join_team'];
			$stims = $temp['stims'];
			$dues = $temp['dues'];
			$mh = $temp['min_hours'];
			$stu = (bool) $temp['user_type'] == 'Student';
			$men = (bool) $temp['user_type'] == 'Mentor';
			$temp['reqs_complete'] = $jt && $stims && (($stu && $dues) || $men) && $mh;
			$data[] = $temp;
		}
		//$data = $result;
	}
	return $data;
}

function userHours($user_id = null, $year = null) {

	$data = array();
	$hoursQuery = userHoursQueryArr($l = 'c', $q = 'd');
	$where = '';
	$whereArr = array();
	$sel = 'b.*, '.$hoursQuery['selects'];
	$joins = 'CROSS JOIN seasons b';
	$joins .= ' LEFT JOIN (SELECT meeting_hours.user_id,year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS off_season_hours, seasons.* FROM meeting_hours LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in) WHERE meeting_hours.time_in>seasons.end_date GROUP BY meeting_hours.user_id,seasons.year) c USING (user_id,year)';
	$joins .= ' LEFT JOIN (SELECT meeting_hours.user_id,year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS season_hours, seasons.* FROM meeting_hours LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in) WHERE meeting_hours.time_in>=seasons.start_date AND meeting_hours.time_in<=seasons.end_date GROUP BY meeting_hours.user_id,seasons.year) d USING (user_id,year)';
	$joins .= ' LEFT JOIN (SELECT meeting_hours.user_id, SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) as season_hours_exempt from meeting_hours LEFT JOIN events ON meeting_hours.time_in >= events.event_start AND meeting_hours.time_out < DATE_ADD(events.event_end, INTERVAL 1 DAY) LEFT JOIN seasons ON seasons.year=YEAR(CURRENT_DATE()) WHERE meeting_hours.time_in>=seasons.start_date AND meeting_hours.time_in<=seasons.end_date AND (events.exempt_hours IS NULL OR events.exempt_hours = "0") GROUP BY meeting_hours.user_id) d1 ON (user_id,year)';
	if($user_id != null) {
		$whereArr[] = 'users.user_id = '.db_quote($user_id);
	}
	if($year != null) {
		$whereArr[] = 'b.year = '.db_quote($year);
	}
	if(!empty($whereArr)) {
		$where = 'WHERE '.implode(' AND ',$whereArr);
	}
	$order = 'ORDER BY users.lname ASC, b.year DESC';
	$query = userQuery($sel, $joins, $where, $order);

	$result = db_select($query);
	if(count($result > 0)) {
		$data = $result;
	}
	return $data;
}

function userHoursbyDate($user_id = null, $year = null) {

	$data = array();
	$where = '';
	$whereArr = array();

	if($user_id != null) {
		$whereArr[] = 'user_id = '.db_quote($user_id);
	}
	if($year != null) {
		$whereArr[] = 'year(a.time_in) = '.db_quote($year);
	}
	if(!empty($whereArr)) {
		$where = 'WHERE '.implode(' AND ',$whereArr);
	}
	$query = 'SELECT year(a.time_in) as year, DATE(a.time_in) as date,  ROUND(SUM(time_to_sec(IFNULL(timediff(a.time_out, a.time_in),0)) / 3600),1) as hours FROM `meeting_hours` a '.$where.' GROUP BY date ORDER BY date ASC';
	//die($query);
	$result = db_select($query);
	if(count($result > 0)) {
		$data = $result;
	}
	return $data;
}

function getUserDataFromParam($param, $value) {
	$data = array();
	$where = ' WHERE users.'.db_escape($param).'='.db_quote($value);
	$query = userQuery($sel='',$joins='', $where, $order = '');
	$user = db_select_single($query);
	if(!is_null($user)) {
		$data = formatUserData($user);
		//$data['notifiation_endpoints'] = getNotifiationEndpointsByUser($data['user_id']);
	} else {
		$data = false;
	}
	return $data;
}

function formatUserData($user) {
	$data = array();
	if(isset($user) && is_array($user)) {
		$data = $user;
		if(isset($data['admin'])) {
			$data['admin'] = (bool) $data['admin'];
		}
		if(isset($data['first_login'])) {
			$data['newUser'] = (bool) $data['first_login'];
			$data['first_login'] = (bool) $data['first_login'];
		}
		if(isset($data['former_student'])) {
			$data['former_student'] = (bool) $data['former_student'];
		}
		if(isset($data['status'])) {
			$data['status'] = (bool) $data['status'];
		}
		if(isset($data['status'])) {
			$data['status'] = (bool) $data['status'];
		}
		if(isset($data['slack_id'])) {
			$data['slackEnabled'] = (bool) isset($data['slack_id']) && $data['slack_id'] != '';
		}
		if(isset($data['user_type']) && isset($data['gender']) ) {
			$data['roomType'] = $data['user_type'] == 'Student' ? $data['user_type'].'.'.$data['gender'] : $data['user_type'];
		}
		//Annual Requirements
		if(isset($data['join_team'])) {
			$data['join_team'] = (bool) $data['join_team'];
		}
		if(isset($data['stims'])) {
			$data['stims'] = (bool) $data['stims'];
		}
		if(isset($data['dues'])) {
			$data['dues'] = (bool) $data['dues'];
		}
		if(isset($data['min_hours'])) {
			$data['min_hours'] = (bool) $data['min_hours'];
		}
		if(isset($data['start_date']) && isset($data['bag_day']) && isset($data['end_date'])) {
			$data['build_season'] = false;
			$data['competition_season'] = false;
			$data['off_season'] = false;
			if(date('Y-m-d') >= $data['start_date'] && date('Y-m-d') <= $data['bag_day']) {
				$data['build_season'] = true;
			} else if (date('Y-m-d') > $data['bag_day'] && date('Y-m-d') <= $data['end_date']) {
				$data['competition_season'] = true;
			} else if(date('Y-m-d') > $data['end_date']) {
				$data['off_season'] = true;
			}
		}
		//Event Requirements
		if(isset($data['registration'])) {
			$data['registration'] = (bool) $data['registration'];
		}
		if(isset($data['payment'])) {
			$data['payment'] = (bool) $data['payment'];
		}
		if(isset($data['permission_slip'])) {
			$data['permission_slip'] = (bool) $data['permission_slip'];
		}
		if(isset($data['food'])) {
			$data['food'] = (bool) $data['food'];
		}
		if(isset($data['room_bool'])) {
			$data['room_bool'] = (bool) $data['room_id'];
		}
		if(isset($data['can_drive'])) {
			$data['can_drive'] = (bool) $data['can_drive'];
		}
		if(isset($data['car_bool'])) {
			$data['car_bool'] = (bool) $data['car_bool'];
		}
		if(isset($data['car_space'])) {
			$data['car_space'] = (integer) $data['car_space'];
		}
		//Hours
		if(isset($data['hours'])) {
			$data['hours'] = (float) $data['hours'];
		}
		if(isset($data['total_hours'])) {
			$data['total_hours'] = (float) $data['total_hours'];
		}
		if(isset($data['season_hours'])) {
			$data['season_hours'] = (float) $data['season_hours'];
		}
		if(isset($data['build_season_hours'])) {
			$data['build_season_hours'] = (float) $data['build_season_hours'];
		}
		if(isset($data['competition_season_hours'])) {
			$data['competition_season_hours'] = (float) $data['competition_season_hours'];
		}
		if(isset($data['season_hours_exempt'])) {
			$data['season_hours_exempt'] = (float) $data['season_hours_exempt'];
		}
		if(isset($data['off_season_hours'])) {
			$data['off_season_hours'] = (float) $data['off_season_hours'];
		}
		if(isset($data['total'])) {
			$data['total'] = (float) $data['total'];
		}
		if(isset($data['hours'])) {
			$data['hours'] = (float) $data['hours'];
		}

	}
	return $data;
}
function checkUserLogin($userData) {
	$data = false;
	$user = false;
	$te = false;
	$sel = 'oauth_ids.*';
	$joins = 'LEFT JOIN oauth_ids USING (user_id)';
	$where = 'WHERE oauth_ids.oauth_id='.db_quote($userData['id']).' AND oauth_ids.oauth_provider='.db_quote($userData['provider']).' AND status="1"';
	$query = userQuery($sel,$joins, $where, $order = '');
	$user = db_select_single($query);
	if(!is_null($user)) {
		$data = formatUserData($user);
	} else {
		$user = getUserDataFromParam('email', $userData['email']);
		if($user == false) {
			$user = getUserDataFromParam('team_email', $userData['email']);
			if($user == false) {
				if(strpos($userData['email'],'@team2363.org') !== false) {
					$emailArr = explode('@',$userData['email']);
					$em = $emailArr[0];
					$fname = substr($em,0,strlen($em)-1);
					$li = substr($em,strlen($em)-1,strlen($em)-1);
					$where = 'WHERE users.fname='.db_quote($fname).' AND users.lname LIKE "'.$li.'%" AND status="1"';;
					$query = userQuery($sel='',$joins='', $where, $order = '');
					$user = db_select_single($query);
					if(!is_null($user)) {
						$te = true;
					}
				}
			}
		}
		if($user != false) {
			$data = formatUserData($user);
			$data['oauth_id'] = $userData['id'];
			$data['oauth_provider'] = $userData['provider'];
			$data['account_email'] = $userData['email'];
			addOauthIdToUser($data);
		}
	}
	if($user != false) {
		$queryArr = array();
		$queryStr = '';
		if($data['profile_image'] == '') {
			$queryArr[]= 'profile_image='.db_quote($userData['profile_image']);
			$data['profile_image'] = $userData['profile_image'];
		}
		if($te && $data['team_email'] == '') {
			$queryArr[]= 'team_email='.db_quote($userData['email']);
			$data['team_email'] = $userData['email'];
		}
		if(count($queryArr) > 0) {
			$queryStr = implode(', ',$queryArr);
			$query = 'UPDATE users SET '.$queryStr.' WHERE user_id = '.db_quote($user['user_id']);
			$result = db_query($query);
		}
	}
/* 	if($user == false) {
		$id = uniqid();
		$date = date('Y-m-d');
		$user_type = 'Student';
		if($userData['age_min'] > 18) {
			$user_type = 'Mentor';
		}
		$team_email = '';
		$email = '';
		if(strpos($userData['email'],'@team2363.org') !== false) {
			$team_email = $userData['email'];
		} else {
			$email = $userData['email'];
		}
		$query = 'insert into users (user_id, email, team_email, fname, lname, gender, profile_image, user_type, creation)
										values ('.db_quote($id).',
												'.db_quote($email).',
												'.db_quote($team_email).',
												'.db_quote($userData['fname']).',
												'.db_quote($userData['lname']).',
												'.db_quote(ucfirst($userData['gender'])).',
												'.db_quote($userData['profile_image']).',
												'.db_quote($user_type).',
												'.db_quote($date).')';
		$result = db_query($query);
		$data = getUserDataFromParam('user_id', $id);
		$data['newUser'] = true;
	} */

	return $data;
}

function addOauthIdToUser($data) {
	//die(json_encode($data));
	$return = false;
	$sel = 'oauth_ids.*';
	$joins = 'LEFT JOIN oauth_ids USING (user_id)';
	$where = 'WHERE oauth_ids.oauth_id='.db_quote($data['oauth_id']).' AND oauth_ids.oauth_provider='.db_quote($data['oauth_provider']);
	$query = userQuery($sel,$joins, $where, $order = '');
	$user = db_select_single($query);
	if(is_null($user)) {
		$auth_id = uniqid();
		$query = 'INSERT INTO oauth_ids (`auth_id`, `user_id`, `oauth_provider`, `oauth_id`, `oauth_user`, `timestamp`) VALUES ('.db_quote($auth_id).','.db_quote($data['user_id']).','.db_quote($data['oauth_provider']).','.db_quote($data['oauth_id']).','.db_quote($data['account_email']).','.db_quote(date('Y-m-d H:i:s')).')';
		$return = db_query($query);
	}
	return $return;
}
function verifyUser($formId, $tokenId, $die = true) {
	if($formId != $tokenId) {
		if($die) {
			die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Unauthorized Action')));
		} else {
			return false;
		}
	} else {
		return true;
	}
}

function checkAdmin($userId, $die = true) {
	$query = 'SELECT * FROM users WHERE user_id='.db_quote($userId).' AND admin="1" AND status="1"';
	$result = db_select_single($query);
	if(!$result) {
		if($die) {
			die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Unauthorized Action')));
		}
		else {
			return false;
		}
	} else {
		return true;
	}
}

function verifyTeamPrivs($userId, $requiredPrivs, $die = true)
{
	global $db;
	$dbPrivs = null;
	$query = 'SELECT team_memberships.* FROM team_memberships WHERE user_id="'.$userId.'"';
	$membership = db_select_single($query);
	if(!is_null($membership))
	{
		$dbPrivs = $membership['privs'];
	}

	$privsArr = array(
		'admin'=>array('admin'),
		'write'=>array('admin','write'),
		'read'=>array('admin','write','read')
	);

	if(!in_array($dbPrivs,$privsArr[$requiredPrivs])) {
		if($die) {
			die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>'Unauthorized Action')));
		}
		else {
			return false;
		}
	} else {
		return true;
	}
}

function getLinkedAccountsByUser($user_id) {
	$query = 'SELECT la.*, UNIX_TIMESTAMP(la.timestamp) AS timestamp_unix FROM oauth_ids la WHERE user_id='.db_quote($user_id);
	$result = db_select($query);
	if(count($result) > 0) {
		return $result;
	} else {
		return array();
	}
}



function getAllUsersFilter($filter = '', $limit = 10, $order = 'full_name', $page = 1, $return=array()) {

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
		if($filter == strtolower('active')) {
			$queryArr[] = '(users.status = "1")';
		} elseif($filter == strtolower('inactive')) {
			$queryArr[] = '(users.status = "0")';
		} else {
		//	$queryArr[] = '(users.fname LIKE '.db_quote('%'.$filter.'%').')';
		//	$queryArr[] = '(users.lname LIKE '.db_quote('%'.$filter.'%').')';
			$queryArr[] = '(users.email LIKE '.db_quote('%'.$filter.'%').')';
			$queryArr[] = '(users.user_type LIKE '.db_quote('%'.$filter.'%').')';
			$queryArr[] = '(users.gender LIKE '.db_quote('%'.$filter.'%').')';
			$queryArr[] = '(full_name LIKE '.db_quote('%'.$filter.'%').')';
			$queryArr[] = '(school_name LIKE '.db_quote('%'.$filter.'%').')';
			$queryArr[] = '(abv LIKE '.db_quote('%'.$filter.'%').')';
			$queryArr[] = '(student_grade LIKE '.db_quote('%'.$filter.'%').')';
		}
	}

	if(count($queryArr) > 0) {
		$queryStr = ' HAVING '.implode(' OR ',$queryArr);
	}

	$orderBy = '';
	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
	if(in_array($orderCol,array('full_name','fname','lname','email','user_type','gender','schoool_name'))) {
		$orderBy = 'ASC';
		if($order[0] == '-') {
			$orderBy = 'DESC';
		}
	}
	$where = $queryStr;
	$query = userQuery($sel='',$joins='', $where, $order = '');
	$result = db_select($query);
	$totalNum = count($result);
	$limitStr = '';
	if($limit > 0) {
		$offset	= ($page - 1) * $limit;
		$limitStr = 'LIMIT '.$offset.', '.$limit;
	}

	$order = ' ORDER BY '.$orderCol.' '.$orderBy.' '.$limitStr;
	//$order = 'ORDER BY '.$orderCol.' '.$orderBy.' LIMIT '.$offset.', '.$limit;
	$query = userQuery($sel='',$joins='', $where, $order);
	//die($query);
	$result = db_select($query);
	if(count($result) > 0) {
		foreach($result as $user) {
			$temp = filterArrayData($user, $return);
			$temp = formatUserData($temp);
			$users[] = $temp;
		}
	}
	$data['data'] = $users;
	$data['query'] = $query;
	$data['total'] = $totalNum;
	$data['maxPage'] = $limit > 0 ? ceil($totalNum/$limit) : 0;

	return $data;
}


?>
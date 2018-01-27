<?php
include('includes.php');

//$authToken = checkToken();

if(!isset($_GET['start_date']) || $_GET['start_date'] == '' || !is_numeric($_GET['start_date'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Start Date.')));
}
if(!isset($_GET['end_date']) || $_GET['end_date'] == '' || !is_numeric($_GET['end_date'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid End Date.')));
}
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

$years = array();
for($i = $start_date; $i <= $end_date; $i++) {
	$years[] = (integer) $i;
}
$series = array('Students','Mentors','Males','Females','Senior','Junior','Sophmore','Freshman','Pre-Freshman','Mentor'); //,'Total'
$data = array();
foreach($series as $se) {
	$data[$se] = array_fill_keys($years,0);
}
//$total = array_fill_keys($years,0);
$query = 'SELECT COUNT(DISTINCT(m.user_id)) as user_count, YEAR(m.time_in) as year, u.user_type FROM meeting_hours m LEFT JOIN users u USING(user_id) WHERE u.user_type <> "" AND YEAR(m.time_in) BETWEEN '.$start_date.' AND '.$end_date.'  GROUP BY year,u.user_type';
$result = db_select($query);
foreach($result as $re) {
	$user_type = $re['user_type'];
	$year = (integer) $re['year'];
	$uc = (integer) $re['user_count'];

	$data[$user_type.'s'][$year] = $uc;
	//$total[$year] = array_sum(array_column($data,$year));
}
$query = 'SELECT COUNT(DISTINCT(m.user_id)) as user_count, YEAR(m.time_in) as year, u.gender FROM meeting_hours m LEFT JOIN users u USING(user_id) WHERE u.gender <> "" AND YEAR(m.time_in) BETWEEN '.$start_date.' AND '.$end_date.' GROUP BY year,u.gender';
$result = db_select($query);
foreach($result as $re) {
	$gender = $re['gender'];
	$year = (integer) $re['year'];
	$uc = (integer) $re['user_count'];

	$data[$gender.'s'][$year] = $uc;
	//$total[$year] = array_sum(array_column($data,$year));
}
//WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,m.time_in,CONCAT(u.grad_year,"-07-01")) <=0  THEN "Graduated"
$query = 'SELECT
 WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,m.time_in,CONCAT(u.grad_year,"-07-01")) <=12 THEN "Senior"
 WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,m.time_in,CONCAT(u.grad_year,"-07-01")) <=24 THEN "Junior"
 WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,m.time_in,CONCAT(u.grad_year,"-07-01")) <=36 THEN "Sophmore"
 WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,m.time_in,CONCAT(u.grad_year,"-07-01")) <=48 THEN "Freshman"
 WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,m.time_in,CONCAT(u.grad_year,"-07-01")) >48 THEN "Pre-Freshman"
 ELSE ""
END AS student_grade, COUNT(DISTINCT(m.user_id)) as user_count, YEAR(m.time_in) as year FROM meeting_hours m LEFT JOIN users u USING(user_id) WHERE u.user_type="student" AND YEAR(m.time_in) BETWEEN '.$start_date.' AND '.$end_date.' GROUP BY year,student_grade';
$result = db_select($query);
foreach($result as $re) {
	$grade = $re['student_grade'];
	$year = (integer) $re['year'];
	$uc = (integer) $re['user_count'];

	$data[$grade][$year] = $uc;
	//$total[$year] = array_sum(array_column($data,$year));
}

//$data['Total'] = $total;
foreach($series as $se) {
	$data[$se] = array_values($data[$se]);
}
$allData = array(
	'labels' => $years,
	'series' => $series,
	'data' => array_values($data),
);
die(json_encode($allData));




?>

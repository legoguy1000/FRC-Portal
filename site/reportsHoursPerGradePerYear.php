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

$series = array('Senior','Junior','Sophmore','Freshman','Mentor');
$data = array();
foreach($series as $se) {
	$data[$se] = array_fill_keys($years,0);
}
$query = 'SELECT CASE
 WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=0  THEN "Graduated"
 WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=12 THEN "Senior"
 WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=24 THEN "Junior"
 WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=36 THEN "Sophmore"
 WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=48 THEN "Freshman"
 ELSE ""
END AS student_grade,
IFNULL(SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600),0) as sum, year(a.time_in) as year from meeting_hours a LEFT JOIN users b USING (user_id) WHERE year(a.time_in) BETWEEN '.$start_date.' AND '.$end_date.' GROUP BY year,student_grade';
$result = db_select($query);
foreach($result as $re) {
	$student_grade = $re['student_grade'];
	$year = $re['year'];
	$sum = $re['sum'];
	if($student_grade == '') {
		$data['Mentor'][$year] = $sum;
	} else {
		$data[$student_grade][$year] = $sum;
	}
}
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

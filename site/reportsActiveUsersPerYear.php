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
$series = array('Students','Mentors','Males','Females'); //,'Total'
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

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
	$years[] = $i;
}

$series = array('Mentor - Sum','Mentor - Avg','Student - Sum','Student - Avg');
$data = array();
foreach($series as $se) {
	$data[$se] = array_fill_keys($years,0);
}
$query = 'SELECT b.user_type, SUM(d.hours) as sum, AVG(d.hours) as avg, d.year from (SELECT a.user_id, SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600) as hours, year(a.time_in) as year from meeting_hours a WHERE year(a.time_in) BETWEEN '.$start_date.' AND '.$end_date.' GROUP BY user_id,year) d LEFT JOIN users b USING (user_id) GROUP BY year,user_type';
$result = db_select($query);
foreach($result as $re) {
	$user_type = $re['user_type'];
	$year = $re['year'];
	$sum = $re['sum'];
	$avg = $re['avg'];

	$data[$user_type.' - Sum'][$year] = $sum;
	$data[$user_type.' - Avg'][$year] = $avg;
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

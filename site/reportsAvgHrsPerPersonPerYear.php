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

$series = array('Sum','Avg');
$data = array();
foreach($series as $se) {
	$data[strtolower($se)] = array_fill_keys($years,0);
}
$query = 'SELECT SUM(d.hours) as sum, AVG(d.hours) as avg, d.year from (SELECT a.user_id, SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600) as hours, year(a.time_in) as year from meeting_hours a WHERE year(a.time_in) BETWEEN '.$start_date.' AND '.$end_date.' GROUP BY user_id,year) d GROUP BY year';
$result = db_select($query);
foreach($result as $re) {
	$year = (integer) $re['year'];
	$sum = (double) $re['sum'];
	$avg = (double) $re['avg'];

	$data['sum'][$year] = $sum;
	$data['avg'][$year] = $avg;
}
$data['sum'] = array_values($data['sum']);
$data['avg'] = array_values($data['avg']);
$csvData = transposeData(array_values($data));
for ($i=0; $i < count($csvData); $i++) {
	array_unshift($csvData[$i],$years[$i]);
}
$csvHeader = $series;
array_unshift($csvHeader,'Year');
$allData = array(
	'labels' => $years,
	'series' => $series,
	'data' => array_values($data),
	'csvData' => array(
		'data' => $csvData,
		'header' => $csvHeader
	)
);
die(json_encode($allData));



?>

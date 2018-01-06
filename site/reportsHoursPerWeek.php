<?php
include('includes.php');

//$authToken = checkToken();

if(!isset($_GET['year']) || $_GET['year'] == '' || !is_numeric($_GET['year'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Start Date.')));
}
$year = $_GET['year'];


$series = array('Total Hours'); //,'Total'
$data = array(array());
$labels = array();
//$total = array_fill_keys($years,0);
$query = 'SELECT SUM(a.hours) as sum, AVG(a.hours) as avg, a.week FROM
(SELECT IFNULL(SUM(time_to_sec(timediff(mh.time_out, mh.time_in)) / 3600),0) as hours, week(mh.time_in) as week from meeting_hours mh WHERE year(mh.time_in)='.db_quote($year).' GROUP BY week) a
GROUP BY week';
//die($query);

$result = db_select($query);
foreach($result as $re) {
	$date = new DateTime();
	$date->setISODate($year,$re['week']);
	$labels[] = $date->format('m/d/Y');
	$data[0][] = (double) $re['sum'];
}
$allData = array(
	'labels' => $labels,
	'series' => $series,
	'data' => $data,
);
die(json_encode($allData));




?>

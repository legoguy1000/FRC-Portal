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
$query = 'SELECT IFNULL(SUM(time_to_sec(timediff(mh.time_out, mh.time_in)) / 3600),0) as hours, year(e.event_start) as year, e.* from meeting_hours mh
LEFT JOIN events e ON mh.time_in >=e.event_start AND mh.time_out < DATE_ADD(e.event_end, INTERVAL 1 DAY)
GROUP BY e.event_id HAVING year='.db_quote($year).' ORDER BY e.event_start';
//die($query);
$result = db_select($query);
foreach($result as $re) {
	$name = $re['name'];
	$sa = explode('-',$re['event_start']);
	$ea = explode('-',$re['event_end']);

	if($re['event_start'] == $re['event_end']) {
		$date = new DateTime($re['event_start']);
		$name .= ' ('.$date->format('M j').')';
	} elseif($sa[1] == $ea[1]) {
		$date = new DateTime($re['event_start']);
		$name .= ' ('.$date->format('M j');
		$date = new DateTime($re['event_end']);
		$name .= '-'.$date->format('j').')';
	} else {
		$date = new DateTime($re['event_start']);
		$name .= ' ('.$date->format('M j');
		$date = new DateTime($re['event_end']);
		$name .= '-'.$date->format('M j').')';
	}
	$labels[] = $name;
	$data[0][] = (double) $re['hours'];
}
$allData = array(
	'labels' => $labels,
	'series' => $series,
	'data' => $data,
	'csvData' => metricsCreateCsvData($data, $series)
);
die(json_encode($allData));




?>

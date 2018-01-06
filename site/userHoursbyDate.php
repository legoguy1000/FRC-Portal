<?php
include('includes.php');

//$authToken = checkToken();
//$user_id = $authToken['data']['user_id'];
$user_id = '5a11bd670484e';

$year = date('Y');
if(isset($_GET['year']) && $_GET['year'] != '') {
	$year = $_GET['year'];
}
if(isset($_GET['user']) && $_GET['user'] != '' && checkAdmin($user_id, $die = false)) {
	$user_id = $_GET['user'];
}

$data = array('sum'=>array());
$labels = array();
$series = array('Sum');
$dates = userHoursbyDate($user_id, $year);
if(count($dates) > 0) {
	foreach($dates as $d) {
		
		$year = $d['year'];
		$date = $d['date'];
		$hours = $d['hours'];
	//	$labels[] = $date;
		$labels[] = date('m/d',strtotime($date));
		$data['sum'][$date] = $hours;
		
	}
	$data['sum'] = array_values($data['sum']);
}


$allData = array(
	'labels' => $labels,
	'series' => $series,
	'data' => array_values($data),
);

if(!empty($allData)) {
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$allData)));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
}



?>
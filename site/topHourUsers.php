<?php
include('includes.php');

//$authToken = checkToken();

$year = date('Y');
if(!isset($_GET['year']) || $_GET['year'] == '') {
	die();
}
$year = $_GET['year'];
$allData = topHourUsers($year);

die(json_encode($allData));



?>
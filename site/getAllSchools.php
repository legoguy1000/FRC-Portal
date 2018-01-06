<?php
include('includes.php');

$authToken = checkToken();

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

$schools = getAllSchools();

die(json_encode($schools));



?>
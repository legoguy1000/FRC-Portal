<?php
include('includes.php');

$authToken = checkToken();

$search = '';
if(isset($_GET['search']) && $_GET['search'] != '') {
	$search = $_GET['search'];
}

$schools = searchAllSchools($search);

die(json_encode($schools));



?>
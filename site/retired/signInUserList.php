<?php
include('includes.php');

//$authToken = checkToken();

$allData = userSignInList();

die(json_encode($allData));



?>
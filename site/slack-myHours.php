<?php
include('./includes.php');

$json = file_get_contents('php://input');
$formData = json_decode($json,true);

echo $json;



?>

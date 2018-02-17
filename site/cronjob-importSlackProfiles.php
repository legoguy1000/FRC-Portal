<?php
include('includes.php');

$users = array();
//$data = http_get('https://slack.com/api/users.list?token=xoxp-10783605152-80482018594-316256639697-04ba2a06532f0849c52bed9783c62fce');
$url = 'https://slack.com/api/users.list?token=xoxp-10783605152-80482018594-316256639697-04ba2a06532f0849c52bed9783c62fce';
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$result = curl_exec($ch);
curl_close($ch);
$data = json_decode($result);
echo $result;
?>

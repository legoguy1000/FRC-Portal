<?php
include(__DIR__ . '/../includes.php');
//

$hids = array();
$date= date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")." -12 hours"));
$result = FrcPortal\MeetingHour::whereNull('time_out')->where('time_in','<=',$date)->delete();

?>

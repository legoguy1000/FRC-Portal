<?php
require_once(__DIR__ . '/../includes.php');
//

$max_hours = getSettingsProp('max_hours');
$max_hours = !empty($max_hours) ? $max_hours : 18;
$date= date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")." -".$max_hours." hours"));
$result = FrcPortal\MeetingHour::whereNull('time_out')->where('time_in','<=',$date)->delete();

?>

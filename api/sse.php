<?php
require_once('app/includes.php');
require_once('app/libraries/CustomAuthRule.php');

// disable default disconnect checks
ignore_user_abort(true);
// set headers for stream
header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header('Connection: keep-alive');
header('X-Accel-Buffering: no');//Nginx: unbuffered responses suitable for Comet and HTTP streaming applications

//5a57eeed0b3ab
// start stream
while(true) {
  $lastEventTimeStamp = array("time"=>date('Y-m-d H:i:s'));
  echo "id: " . $lastEventTimeStamp['time'] . "\n";
  echo "data: ".json_encode($lastEventTimeStamp)." \n\n";
  echo PHP_EOL;
  if (ob_get_contents()) {
    ob_end_flush();
  }
  flush();
  // 2 second sleep then carry on
  sleep(2);
}
?>

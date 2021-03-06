<?php
require_once('app/includes.php');
require_once('app/libraries/CustomAuthRule.php');

// disable default disconnect checks
//ignore_user_abort(true);
// set headers for stream
header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header('Connection: keep-alive');
header('X-Accel-Buffering: no');//Nginx: unbuffered responses suitable for Comet and HTTP streaming applications

$lastEventTimeStamp = floatval(isset($_SERVER["HTTP_LAST_EVENT_ID"]) ? $_SERVER["HTTP_LAST_EVENT_ID"] : 0);
if ($lastEventTimeStamp == 0) {
  $lastEventTimeStamp = floatval(isset($_GET["lastEventId"]) ? $_GET["lastEventId"] : 0);
}

echo ":" . str_repeat(" ", 2048) . "\n"; // 2 kB padding for IE
echo "retry: 2000\n";

//5a57eeed0b3ab
// start stream
while(true) {
  if(connection_aborted()) {
    exit();
  } else {
    // here you will want to get the latest event id you have created on the server, but for now we will increment and force an update
    $hours = FrcPortal\MeetingHour::where('updated_at','>',$lastEventTimeStamp)->get();
    if(count($hours) > 0) {
      sleep(2);
      $users = getSignInList(date('Y'));
      $lastEventTimeStamp = date('Y-m-d H:i:s');
      echo "id: " . $lastEventTimeStamp . "\n";
      echo "data: ".json_encode($users)." \n\n";
      echo PHP_EOL;
      if (ob_get_contents()) {
        ob_end_flush();
      }
      flush();
    }
  }
  // 2 second sleep then carry on
  sleep(2);
}
?>

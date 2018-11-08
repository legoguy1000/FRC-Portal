<?php
require_once('app/includes.php');
require_once('app/libraries/CustomAuthRule.php');

// make session read-only
session_start();
session_write_close();
// disable default disconnect checks
ignore_user_abort(true);
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
    $hours = FrcPortal\MeetingHour::where('updated_at','>',$lastEventTimeStamp)->orderBy('updated_at', 'desc')->first();
    if(!is_null($hours)) {
      $users = getSignInList(date('Y'));
      echo "id: " . $hours->updated_at . "\n";
      echo "data: ".json_encode($hours->hours_id)." \n\n";
      $lastEventTimeStamp = $hours->updated_at;
      ob_flush();
      flush();
    } else {
      // no new data to send
      echo ": heartbeat\n\n";
      ob_flush();
      flush();
    }
  }
  // 2 second sleep then carry on
  sleep(2);

}
?>

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

$lastEventId = floatval(isset($_SERVER["HTTP_LAST_EVENT_ID"]) ? $_SERVER["HTTP_LAST_EVENT_ID"] : 0);
if ($lastEventId == 0) {
  $lastEventId = floatval(isset($_GET["lastEventId"]) ? $_GET["lastEventId"] : 0);
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
    $hours = FrcPortal\MeetingHour::where('hours_id','>',$lastEventId)->orderBy('hours_id', 'desc')->first();
    if(!is_null($hours)) {
      $users = getSignInList(date('Y'));
      echo "id: " . $hours->hours_id . "\n";
      echo "data: ".json_encode($hours->hours_id)." \n\n";
      $lastEventId = $hours->hours_id;
      ob_flush();
      flush();
    } else {
      // no new data to send
      echo ": heartbeat\n\n";
      //ob_flush();
      flush();
    }
  }
  // 2 second sleep then carry on
  sleep(2);

}
?>

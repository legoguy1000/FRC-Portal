<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

include('includes.php');

$time = date('r');
//echo "data: The server time is: {$time}\n\n";
//flush();
$last_id = '';
$idQ = '';
$headers = apache_request_headers();
if(isset($headers['Last-Event-ID'])) {
	$last_id = $headers['Last-Event-ID'];
	$idQ = 'AND note_id > '.db_quote($last_id);
}
while(true) {
	sendMsg('', 'error');
	$query = 'SELECT * FROM notifications WHERE user_id = '.db_quote('5a11bd670484e').' '.$idQ;
	$result = db_select($query);	
	if($result) {
		foreach($result as $res) {
			sendMsg($res['note_id'], json_encode($res));
		}
	} else {
		sendMsg('', 'error');
	}
		
	sleep(5);
}


function sendMsg($id, $msg) {
  echo "id: $id" . PHP_EOL;
  echo "data: $msg" . PHP_EOL;
/*   echo PHP_EOL;*/
  ob_flush(); 
  flush();
}

?>
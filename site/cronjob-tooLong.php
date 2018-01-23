<?php
include('includes.php');

$hids = array();
$date= date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")." -12 hours"));
$query = 'SELECT * FROM meeting_hours WHERE time_out IS NULL AND time_in <= '.db_quote($date); //OR time_to_sec(timediff(time_out, time_in)) <= 300
$result = db_select($query);
if(count($result) > 0) {
	foreach($result as $re) {
		$hids[] = $re['hours_id'];
	}
}

if(count($hids) > 0) {
	$query = 'DELETE FROM meeting_hours WHERE hours_id IN ("'.implode('","',$hids).'")';
	//die($query);
	$result = db_query($query);
	if($result) {
		//die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));
	} else {
		//die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
	}
}
?>

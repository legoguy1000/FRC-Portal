<?php
include('./includes.php');

$authToken = checkToken(true,true);
$user_id = $authToken['data']['user_id'];
checkAdmin($user_id, $die = true);

$json = file_get_contents('php://input');
$formData = json_decode($json,true);

if(!isset($formData['status']) || $formData['status'] == '' || ($formData['status'] != 'approve' && $formData['status'] != 'deny')) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Status')));
}
if(!isset($formData['request_id']) || $formData['request_id'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request ID')));
}
$request_id = $formData['request_id'];
$query = 'SELECT * FROM missing_hours_requests WHERE request_id ='.db_quote($request_id);
$result = db_select_single($query);
if(!is_null($result)) {
	$date = time();
	$requestInfo = $result;
	$user_id = $requestInfo['user_id'];
	if($formData['status'] == 'approve') {
		db_begin_transaction();
		$query = 'UPDATE missing_hours_requests SET approved = "1", approved_date = '.db_quote(date('Y-m-d H:i:s',$date)).', approved_by = '.db_quote($user_id).' WHERE request_id = '.db_quote($request_id);
		$result = db_query($query);
		$hours_id = uniqid();
		$query = 'INSERT INTO meeting_hours (hours_id, user_id, time_in, time_out) VALUES ('.db_quote($hours_id).', '.db_quote($user_id).', '.db_quote($requestInfo['time_in']).', '.db_quote($requestInfo['time_out']).')';
		$result = db_query($query);
		$commit = db_commit();
		$hoursRequestList = getAllMissingHoursRequestsFilter($filter = '', $limit = 10, $order = 'time_in', $page = 1);
		if($commit) {
			die(json_encode(array('status'=>true, 'msg'=>'Missing hours request approved.', 'hoursRequestList'=>$hoursRequestList)));
		} else {
			die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'hoursRequestList'=>$hoursRequestList)));
		}
	} else {
		$query = 'UPDATE missing_hours_requests SET approved = "0", approved_date = '.db_quote(date('Y-m-d H:i:s',$date)).', approved_by = '.db_quote($user_id).' WHERE request_id = '.db_quote($request_id);
		$result = db_query($query);
		$hoursRequestList = getAllMissingHoursRequestsFilter($filter = '', $limit = 10, $order = 'time_in', $page = 1);
		if($result) {
			die(json_encode(array('status'=>true, 'msg'=>'Missing hours request denied.', 'hoursRequestList'=>$hoursRequestList)));
		} else {
			die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'hoursRequestList'=>$hoursRequestList)));
		}
	}
}  else {
	die(json_encode(array('status'=>false, 'msg'=>'PIN is incorrect')));
}



?>

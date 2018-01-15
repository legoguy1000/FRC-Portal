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
	if($formData == 'approve') {
		$query = 'UPDATE missing_hours_requests SET approved = "1", approved_date = '.db_quote(date('Y-m-d H:i:s',$date)).', approved_by = '.db_quote($user_id).' WHERE request_id = '.db_quote($request_id);
		$result = db_query($query);
		if($result) {
			$hoursRequestList = getAllMissingHoursRequestsFilter($filter = '', $limit = 10, $order = 'time_in', $page = 1);
			die(json_encode(array('status'=>true, 'msg'=>'Missing hours request approved.', 'hoursRequestList'=>$hoursRequestList)));
		} else {
			die(json_encode(array('status'=>false, 'msg'=>'Something went wrong')));
		}
	} else {
		$result = db_query($query);
		if($result) {
			$hoursRequestList = getAllMissingHoursRequestsFilter($filter = '', $limit = 10, $order = 'time_in', $page = 1);
			die(json_encode(array('status'=>true, 'msg'=>'Missing hours request denied.', 'hoursRequestList'=>$hoursRequestList)));
		} else {
			die(json_encode(array('status'=>false, 'msg'=>'Something went wrong')));
		}
	}
}  else {
	die(json_encode(array('status'=>false, 'msg'=>'PIN is incorrect')));
}



?>

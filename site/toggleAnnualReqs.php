<?php
include('./includes.php');

$authToken = checkToken(true,true);
$user_id = $authToken['data']['user_id'];
checkAdmin($user_id, $die = true);

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);


if(!isset($formData['users']) || !is_array($formData['users']) || empty($formData['users'])) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid user array')));
}
if(!isset($formData['requirement']) || $formData['requirement'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid requirement')));
}
if(!isset($formData['season_id']) || $formData['season_id'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid season ID')));
}
$query = 'SELECT * FROM seasons WHERE season_id='.db_quote($formData['season_id']);
$result = db_select_single($query);
if(is_null($result)) {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invlaid season ID')));
} 
$year = $result['year'];
$array = array();
$req = $formData['requirement'];
$users = array_column($formData['users'],'user_id');
$needInsert = array_fill_keys($users,true);

$query = 'SELECT * FROM annual_requirements WHERE season_id='.db_quote($formData['season_id']).' AND user_id IN ("'.implode('","',$users).'")';
$array[] = $query;
$result = db_select($query);
if(!empty($result)) {
	db_begin_transaction();
	foreach($result as $re) {
		$cur = $re[$req];
		$new = !$cur;
		$query = 'UPDATE annual_requirements SET '.db_escape($req).'='.db_quote($new).' WHERE season_id='.db_quote($formData['season_id']).' AND user_id='.db_quote($re['user_id']);
		$result = db_query($query);
		$needInsert[$re['user_id']] = false;
		$array[] = $query;
	}
	db_commit();
}

$remainingUsers = array_filter($needInsert);
$array[] = $remainingUsers;
if(!empty($remainingUsers)) {
	db_begin_transaction();
	foreach($remainingUsers as $user_id=>$b) {
		$req_id = uniqid();
		$query = 'INSERT INTO annual_requirements (req_id, user_id, season_id, '.db_escape($req).') VALUES 
		('.db_quote($req_id).', '.db_quote($user_id).', '.db_quote($formData['season_id']).', "1")';
		$result = db_query($query);
		$array[] = $query;
	}
	db_commit();
}
$seasonReqs = userSeasonInfo($user_id = null, $year);
die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$seasonReqs)));


?>
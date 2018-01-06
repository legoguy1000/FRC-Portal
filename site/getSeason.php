<?php
include('includes.php');

$authToken = checkToken();

$season_id = null;
if(isset($_GET['season_id']) && $_GET['season_id'] != '') {
	$season_id = $_GET['season_id'];
}

$season = getSeason($season_id,$reqs = true);
if($season) {
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$season)));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
}



?>
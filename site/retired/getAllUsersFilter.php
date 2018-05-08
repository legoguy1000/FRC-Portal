<?php
include('includes.php');

$authToken = checkToken(true,true);

$filter = null;
$limit = null;
$order = null;
$page = null;
$listOnly = false;
$return = array();
if(isset($_GET['filter']) && $_GET['filter'] != '') {
	$filter = $_GET['filter'];
}
if(isset($_GET['limit']) && $_GET['limit'] != '') {
	$limit = $_GET['limit'];
}
if(isset($_GET['order']) && $_GET['order'] != '') {
	$order = $_GET['order'];
}
if(isset($_GET['page']) && $_GET['page'] != '') {
	$page = $_GET['page'];
}
if(isset($_GET['listOnly']) && $_GET['listOnly'] != '' && $_GET['listOnly']==true) {
	$listOnly = true;
}
if(isset($_GET['return']) && is_array($_GET['return'])) {
	$return = $_GET['return'];
}
$users = getAllUsersFilter($filter, $limit, $order, $page, $return);
if(!empty($users)) {
	if($listOnly) {
		die(json_encode($users['data']));
	}
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$users['data'], 'query'=>$users['query'], 'total'=>$users['total'], 'maxPage'=>$users['maxPage'])));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
}



?>
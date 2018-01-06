<?php
include('includes.php');

$authToken = checkToken();

$filter = null;
$limit = null;
$order = null;
$page = null;
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

$users = getAllSchoolsFilter($filter, $limit, $order, $page);
if(!empty($users)) {
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$users['data'], 'query'=>$users['query'], 'total'=>$users['total'], 'maxPage'=>$users['maxPage'])));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
}



?>
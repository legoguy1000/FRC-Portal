<?php
include('includes.php');

$authToken = checkToken();


$seasons = getAllSeasonsFilter(null, 0, null, null);
if(!empty($seasons)) {
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$seasons['data'], 'query'=>$seasons['query'], 'total'=>$seasons['total'], 'maxPage'=>$seasons['maxPage'])));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
}



?>
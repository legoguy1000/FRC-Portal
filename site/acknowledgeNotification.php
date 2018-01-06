<?php
include('includes.php');


/* $json = file_get_contents('php://input'); 
$formData = json_decode($json,true); */

if(!isset($_POST['tag']) || $_POST['tag'] == '') {
	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid Request.')));
}
$query = 'UPDATE notifications SET acknowledge="1" WHERE note_id='.db_quote($_POST['tag']);
$result = db_query($query);
if($result) {
	$msg = 'Ackowledged';
	die(json_encode(array('status'=>true, 'type'=>'success', 'msg'=>$msg)));
} else {
	$msg = 'Something went wrong';
	die(json_encode(array('status'=>false, 'type'=>'error', 'msg'=>$msg)));
}



?>
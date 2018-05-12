<?php
include(__DIR__ . '/../includes.php');

$users = array();
if(date('n') == 7) {
	$users = FrcPortal\User::where('user_type','Student')->whereNotNull('grad_year')->where('grad_year',date('Y'))->update(['status' => false, 'admin' => false, 'former_student' => true]);
}
?>

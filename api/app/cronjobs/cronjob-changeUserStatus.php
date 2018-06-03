<?php
include(__DIR__ . '/../includes.php');

$users = array();
$month = getSettingsProp('school_month_end');
if(!is_null($month) && date('F') == $month) {
	$users = FrcPortal\User::where('user_type','Student')->whereNotNull('grad_year')->where('grad_year',date('Y'))->update(['status' => false, 'admin' => false, 'former_student' => true]);
}
?>

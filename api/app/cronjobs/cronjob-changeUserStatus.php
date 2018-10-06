<?php
require_once(__DIR__ . '/../includes.php');

$users = array();
$month = getSettingsProp('school_month_end');
if(!is_null($month) && $month != '' && date('F') == $month && date('Y-m-t') == date('Y-m-d')) {
	$users = FrcPortal\User::where('user_type','Student')->whereNotNull('grad_year')->where('grad_year',date('Y'))->update(['status' => false, 'admin' => false, 'former_student' => true]);
}
?>

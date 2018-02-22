<?php
include(__DIR__ . '/../includes.php');

$users = array();
if(date('n') == 7) {
	$query = 'UPDATE users SET status="0",admin="0",former_student="1" WHERE user_type="Student" AND grad_year IS NOT NULL AND grad_year=YEAR(CURRENT_DATE) AND MONTH(CURRENT_DATE)=7';
	$result = db_select($query);
}
?>

<?php
include('includes.php');

$authToken = checkToken();

$search = null;
if(isset($_GET['search']) && $_GET['search'] != '') {
	$search = $_GET['search'];
}

$data = array();
if(stripos($search,'=') !== false) {

} else {
	$query = 'SHOW columns FROM users WHERE FIELD LIKE '.db_quote('%'.$search.'%');
	$result = db_select($query);
	if($result) {
		foreach($result as $re) {
			$temp = array(
				'text' => $re['Field'].'=',
				'disabled' => true
			);
			$data[] = $temp;
		}
	}
}
die(json_encode($data));


?>

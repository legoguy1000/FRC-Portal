<?php
function checkAdmin($user_id) {
	$return = false;
	$user = FrcPortal\User::where('user_id',$user_id)->where('admin',true)->where('status',true)->first();
	if($user) {
		$return = true;
	}
	return $return;
}

?>

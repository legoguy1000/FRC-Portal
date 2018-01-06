<?php
include('./includes.php');

//echo hash('sha256',123456);

//sendPushNotificationByUser('5a11bd670484e', $title='asdfasdf', $body='asdf', $tag='');
$msgData = array(
	'push' => array(
		'title' => 'asdf',
		'body' => 'asdfasdf'
	),
	'email' => array(
		'title' => 'asdf',
		'body' => 'asdfasdf'
	)
);
//sendUserNotification('5a11bd670484e', 'push', $msgData);

//echo userSignInList();
//echo date('e');
//var_dump (db_select('SELECT @@global.time_zone, @@session.time_zone;'));

//echo userQuery($sel='',$joins='', $where = '', $order = '');
var_dump(getUpcommingEvents());


?>
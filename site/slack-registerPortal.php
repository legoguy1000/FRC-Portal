<?php
include('./includes.php');

$token = $_POST['token'];
$user_id = $_POST['user_id'];
$user_name = $_POST['user_name'];


if(isset($_POST['text']) && filter_var($_POST['text'], FILTER_VALIDATE_EMAIL) && $_POST['text'] != '') {
	$email = $_POST['text'];
	$query = 'UPDATE users SET slack_id = '.db_quote($user_id).' WHERE email = '.db_quote($email).' OR team_email = '.db_quote($email);
	$result = db_query($query);
	if($result) {
		die(':thumbsup:');
	}
} else {
	die('Email invalid.  Please use the email account associated with your portal account.');
}



?>

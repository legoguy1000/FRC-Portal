<?php
include('./includes.php');

// $data = array();
// parse_str(file_get_contents("php://input"), $data);
// $json = urldecode($data['payload']);
// $data = json_decode($json,true);
//
// if($data['callback_id'] == 'sign_out' && $data['actions'][0]['name'] == 'sign_out') {
//   $answer = explode('-',$data['actions'][0]['value']);
//   $user_id = $answer[0];
//   $hours_id = $answer[1];
//   $time = date('Y-m-d').' 18:00:00';
//   if(date('N') <= 5) {
//     $time = date('Y-m-d').' 21:00:00';
//   } else {
//       $time = date('Y-m-d').' 18:00:00';
//   }
//   $query = 'UPDATE meeting_hours SET time_out = '.db_quote($time).' WHERE hours_id = '.db_quote($hours_id).' AND time_out IS NULL AND user_id='.db_quote($user_id);
//   $result = db_query($query);
//   if($result) {
//     die('You signed out at '.date('M d, Y H:i A', strtotime($time)));
//   } else {
//     die('Something went wrong.  We were unable to sign you out.');
//   }
// }


/*asdff
$where = ' WHERE users.user_id="5a11bd670484e"';
$query = userQuery($sel='', $joins='', $where, $order='');
$result = db_select_single($query);
echo emailUser($userData = $result, $subject = 'Slack Button Response',$content = $json,$attachments = false); */



?>

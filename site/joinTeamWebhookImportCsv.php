<?php
/* include('./includes.php');

$json = file_get_contents('includes/libraries/csvjson.json'); 
$allForm = json_decode($json,true);

foreach($allForm as $data) {
//	$timestamp = $data['timestamp'];
	$email = $data['email'];
	$fname = $data['fname'];
	$lname = $data['lname'];
	$form_user_type = $data['user_type'];
	$user_type = $form_user_type == 'Adult' ? 'Mentor' : $form_user_type;
//	$birthday = $data['birthday'];
	$grad_year = $data['grad_year'];
	$school = $data['school'];
	$student_id = $data['student_id'];
	$phone = $data['phone'];
	$clean_phone = preg_replace('/[^0-9]/s', '', $phone);

	$user = false;
	$sel = '';
	$joins = '';
	$where = 'WHERE users.email='.db_quote($email);
	$query = userQuery($sel,$joins, $where, $order = '');
	$user = db_select_single($query);
	if(is_null($user)) {
		$sel = '';
		$joins = '';
		$where = 'WHERE users.fname='.db_quote($fname).' AND users.lname='.db_quote($lname).' AND users.user_type='.db_quote($user_type);
		$query = userQuery($sel,$joins, $where, $order = '');
		$user = db_select_single($query);
		$user_id = $user['user_id'];
	}
	if($user == false) {
		$school_id = '';
		if($user_type == 'Student' && $school != '') {
			if(strpos($school,'Menchville') !== false) {
				$query = 'SELECT schools.* FROM schools WHERE school_name LIKE '.db_quote('%Menchville%');
				$schools = db_select_single($query);
				if(!is_null($schools)) {
					$school_id = $schools['school_id'];
				}
			} else {
				$query = 'SELECT schools.* FROM schools WHERE school_name LIKE '.db_quote('%'.$school.'%').' OR abv LIKE '.db_quote('%'.$school.'%');
				$schools = db_select_single($query);
				if(!is_null($schools)) {
					$school_id = $schools['school_id'];
				} else {
					$sid = uniqid();
					$abv = '';
					for($i=0; $i<strlen($school); $i++) {
						if (ctype_upper($school[$i])) {
							$abv .= $school[$i];
						}
					}
					$query = 'insert into schools (school_id, school_name, abv) values ('.db_quote($sid).','.db_quote($school).','.db_quote($abv).')';
					$result = db_query($query);
					if($result) {
						$school_id = $sid;
					}
				}
			}
		}
		$user_id = uniqid();
		$date = date('Y-m-d');
		$columns = 'user_id, email, fname, lname, user_type, creation';
		$values = db_quote($user_id).', '.db_quote($email).', '.db_quote($fname).', '.db_quote($lname).', '.db_quote($user_type).','.db_quote($date);
		if($school_id != '') {
			$columns .= ', school_id';
			$values .= ', '.db_quote($school_id);
		}
		if($grad_year != '') {
			$columns .= ', grad_year';
			$values .= ', '.db_quote($grad_year);
		}
		if($clean_phone != '' && is_numeric($clean_phone)) {
			$columns .= ', phone';
			$values .= ', '.db_quote($clean_phone);
		}
		if($user_type == 'Student' && $student_id != '' && is_numeric($student_id)) {
			$signin_pin = hash('SHA256',$student_id);
			$columns .= ', signin_pin';
			$values .= ', '.db_quote($signin_pin);
		}
		
		$query = 'insert into users ('.$columns.') values ('.$values.')';
		$result = db_query($query);
	}

	$season_id = '';
	$where = 'WHERE bag_day >= '.db_quote(date('Y-m-d'));
	$query = seasonQuery($sel='',$joins='', $where, $order = '');
	$season = db_select_single($query);
	if(!is_null($season)) {
		$season_id = $season['season_id'];
		$query = 'SELECT * FROM annual_requirements WHERE season_id='.db_quote($season_id).' AND user_id='.db_quote($user_id);
		$season = db_select_single($query);
		if(!is_null($season)) {
			$query = 'UPDATE annual_requirements SET join_team="1" WHERE season_id='.db_quote($season_id).' AND user_id='.db_quote($user_id);
		} else {
			$req_id = uniqid();
			$query = 'INSERT INTO annual_requirements (req_id, user_id, season_id, join_team) VALUES 
			('.db_quote($req_id).', '.db_quote($user_id).', '.db_quote($season_id).', "1")';
			$result = db_query($query);
		}
	}
	$sel = '';
	$joins = '';
	$where = 'WHERE users.fname='.db_query($fname).' AND users.lname='.db_query($lname).' AND users.user_type='.db_query($user_type);
	$query = userQuery($sel,$joins, $where, $order = '');
	$user = db_select_single($query);
		
	$userInfo = $user!=false ? json_encode($user) : $query;

	$id = uniqid();
	$date = date('Y-m-d H:i:s');
	$query = 'INSERT INTO webhook_logs (`wh_id`, `webhook_submit`, `user_data`, `timestamp`) VALUES ("'.$id.'", '.db_quote(json_encode($formData)).', '.db_quote($userInfo).', '.db_quote($date).')';
	$result = db_query($query);

} */
?>
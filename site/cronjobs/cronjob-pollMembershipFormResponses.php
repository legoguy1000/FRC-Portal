<?php
include(__DIR__ . '/../includes.php');
//

$season_id = '';
$where = 'WHERE bag_day >= '.db_quote('2018-01-01');
$query = seasonQuery($sel='',$joins='', $where, $order = '');
$season = db_select_single($query);
if(!is_null($season)) {
	$season_id = $season['season_id'];
	$season_spreadsheet = $season['join_spreadsheet'];

	$client = new Google_Client();
	$client->setAuthConfigFile(__DIR__ . '/../includes/libraries/team-2363-portal-0c12aca54f1c.json');
	$client->setScopes(['https://www.googleapis.com/auth/spreadsheets.readonly']);
	$service = new Google_Service_Sheets($client);
	// The ID of the spreadsheet to retrieve data from.
	$spreadsheetId = $season_spreadsheet;  // TODO: Update placeholder value.
	// The A1 notation of the values to retrieve.
	$range = 'Form Responses 1';  // TODO: Update placeholder value.
	$response = $service->spreadsheets_values->get($spreadsheetId, $range);
	$values = $response->getValues();
	$data = array();
	if (count($values) != 0) {
		$headers = array_map('strtolower', array_shift($values));
		foreach ($values as $row) {
			$temp = array();
			for($i=0; $i<count($headers);$i++) {
				$key = $headers[$i];
				$val = isset($row[$i]) ? $row[$i] : '';
				$temp[$key] = $val;
			}
			$data[] = $temp;
		}
	}
	//Itterate through data
	if(count($data) > 0) {
		foreach($data as $userInfo) {
			//	$timestamp = $data['timestamp'];
			$email = $userInfo['email address'];
			$fname = $userInfo['first name'];
			$lname = $userInfo['last name'];
			$form_user_type = $userInfo['member type'];
			$user_type = $form_user_type == 'Adult' ? 'Mentor' : $form_user_type;
			//	$birthday = $userInfo['birthday'];
			$grad_year = $userInfo['year of graduation'];
			$school = $userInfo['school'];
			$student_id = $userInfo['student id'];
			$phone = $userInfo['phone'];
			$clean_phone = preg_replace('/[^0-9]/s', '', $phone);

			$user = false;
			$sel = '';
			$joins = '';
			$where = 'WHERE users.email='.db_quote($email);
			$query = userQuery($sel,$joins, $where, $order = '');
			$user = db_select_single($query);
			$user_id = $user['user_id'];
			if(is_null($user)) {
				$sel = '';
				$joins = '';
				$where = 'WHERE users.fname='.db_quote($fname).' AND users.lname='.db_quote($lname).' AND users.user_type='.db_quote($user_type);
				$query = userQuery($sel,$joins, $where, $order = '');
				$user = db_select_single($query);
				$user_id = $user['user_id'];
			}
			//If user doesn't exist, add data to user table
			if($user == false) {
				$school_id = '';
				if($user_type == 'Student' && $school != '') {
					$school_formated = str_replace('HS', 'High School', $school);
					$school_formated = str_replace('MS', 'Middle School', $school_formated);
					$school_formated = stripos($school_formated,' School') === false ? $school_formated.' School': $school_formated;
					$query = 'SELECT schools.* FROM schools WHERE school_name LIKE '.db_quote('%'.$school_formated.'%').' OR abv LIKE '.db_quote('%'.$school_formated.'%');
					$schools = db_select_single($query);
					if(!is_null($schools)) {
						$school_id = $schools['school_id'];
					} else {
						$sid = uniqid();
						$abv = '';
						for($i=0; $i<strlen($school_formated); $i++) {
							if (ctype_upper($school_formated[$i])) {
								$abv .= $school_formated[$i];
							}
						}
						$query = 'insert into schools (school_id, school_name, abv) values ('.db_quote($sid).','.db_quote($school_formated).','.db_quote($abv).')';
						$result = db_query($query);
						if($result) {
							$school_id = $sid;
						}
					}
	/*				if(strpos($school,'Menchville') !== false) {
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
					} */
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
				//Insert Data
				$query = 'insert into users ('.$columns.') values ('.$values.')';
				$result = db_query($query);
			}

			//Add User info into the Annual Requirements Table
			$query = 'SELECT * FROM annual_requirements WHERE season_id='.db_quote($season_id).' AND user_id='.db_quote($user_id);
			$season = db_select_single($query);
			if(!is_null($season)) {
				$query = 'UPDATE annual_requirements SET join_team="1" WHERE season_id='.db_quote($season_id).' AND user_id='.db_quote($user_id);
			} else {
				$req_id = uniqid();
				$query = 'INSERT INTO annual_requirements (req_id, user_id, season_id, join_team) VALUES ('.db_quote($req_id).', '.db_quote($user_id).', '.db_quote($season_id).', "1")';
				$result = db_query($query);
			}
		}
	}
}



?>

<?php
include(__DIR__ . '/../includes.php');
//

$season_id = null;
$spreadsheetId = null;
$season = FrcPortal\Season::where('bag_day','>=',date('Y-m-d'))->first();
if(!is_null($season)) {
	$season_id = $season->season_id;
	$spreadsheetId = $season->join_spreadsheet != '' ? $season->join_spreadsheet:null;
	if(is_null($spreadsheetId)) {
		$result = getSeasonMembershipForm($season->year);
		if($result['status'] == true) {
			$spreadsheetId = $result['data']['join_spreadsheet'];
			$season->join_spreadsheet = $spreadsheetId;
			$season->save();
		}
	}
} else {
	$year = date('Y')+1;
	$result = getSeasonMembershipForm($year);
	if($result['status'] == true) {
		$spreadsheetId = $result['data']['join_spreadsheet'];
		$season = FrcPortal\Season::where('year',$year)->first();
		if(!is_null($season)) {
			$season_id = $season['season_id'];
			$season->join_spreadsheet = $spreadsheetId;
			$season->save();
		}

	}
}
if(!is_null($spreadsheetId)) {
	$data = array();
	$client = new Google_Client();
	$creds = getServiceAccountFile();
	if($creds['status'] != false) {
		$client->setAuthConfigFile($creds['data']['path']);
		$client->setScopes(['https://www.googleapis.com/auth/spreadsheets.readonly']);
		$service = new Google_Service_Sheets($client);
		// The A1 notation of the values to retrieve.
		$range = 'Form Responses 1';  // TODO: Update placeholder value.
		$response = $service->spreadsheets_values->get($spreadsheetId, $range);
		$values = $response->getValues();
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
	} else {
		//Credentials file doesn't work
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

			$user = null;
			$user = FrcPortal\User::where('email',$email)->first();
			$user_id = $user->user_id;;
			if(is_null($user)) {
				$user = FrcPortal\User::where('fname',$fname)->where('lname',$lname)->where('user_type',$user_type)->first();
				$user_id = $user->user_id;;
			}
			//If user doesn't exist, add data to user table
			if(is_null($user)) {
				$school_id = '';
				if($user_type == 'Student' && $school != '') {
					$school_formated = str_replace('HS', 'High School', $school);
					$school_formated = str_replace('MS', 'Middle School', $school_formated);
					$school_formated = stripos($school_formated,' School') === false ? $school_formated.' School': $school_formated;
					$school = FrcPortal\School::where('school_name','LIKE','%'.$school_formated.'%')->orWhere('abv','LIKE','%'.$school_formated.'%')->first();
					if(!is_null($school)) {
						$school_id = $school['school_id'];
					} else {
						$abv = '';
						for($i=0; $i<strlen($school_formated); $i++) {
							if (ctype_upper($school_formated[$i])) {
								$abv .= $school_formated[$i];
							}
						}
						$school = new FrcPortal\School();
						$school->school_name = $school_formated;
						$school->abv = $abv;
						if($school->save()) {
							$school_id = $school->school_id;
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
				$user = new FrcPortal\User();
				$user->email = $email;
				$user->fname = $fname;
				$user->lname = $lname;
				$user->user_type = $user_type;
				if($school_id != '' && $user_type == 'Student') {
					$user->school_id = $school_id;
				}
				if($grad_year != '' && $user_type == 'Student') {
					$user->grad_year = $grad_year;
				}
				if($clean_phone != '' && is_numeric($clean_phone)) {
					$user->phone = $clean_phone;
				}
				if($user_type == 'Student' && $student_id != '' && is_numeric($student_id)) {
					$signin_pin = hash('SHA256',$student_id);
					$user->signin_pin = $signin_pin;
				}
				//Insert Data
				if($user->save()) {
					$user_id = $user->user_id;
					setDefaultNotifications($user_id);
				}
			}
			//Add User info into the Annual Requirements Table
			if(!is_null($season_id)) {
				$season = FrcPortal\AnnualRequirement::updateOrCreate(['season_id' => $season_id, 'user_id' => $user_id], ['join_team' => true]);
			}
		}
	}
}




?>

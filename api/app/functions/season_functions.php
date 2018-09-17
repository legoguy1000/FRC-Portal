<?php
function getSeasonMembershipForm($year) {
	$result = array(
		'status' => false,
		'msg' => '',
		'data' => null
	);
	if(!is_null($year)) {
		try {
			$client = new Google_Client();
			$creds = getServiceAccountFile();
			$client->setAuthConfigFile($creds['data']['path']);
			$client->setScopes(['https://www.googleapis.com/auth/drive.readonly']);
			$service = new Google_Service_Drive($client);
			$mfn = getMembershipFormName();
			$mfn = str_replace('###YEAR###',$year,$mfn);
			$query = buildGoogleDriveQuery($mfn);
			$parameters = array(
				'corpora' => 'user',
				'q' => $query,
				//'q' => 'name contains "'.$year.'" and name contains "Membership" and name contains "(Responses)" and mimeType = "application/vnd.google-apps.spreadsheet"',
				'supportsTeamDrives' => 'true',
				'pageSize' => '1'
			);
			$teamDrive = getSettingsProp('google_drive_id');
			if(!is_null($teamDrive)) {
				$parameters['corpora'] = 'teamDrive';
				$parameters['teamDriveId'] = $teamDrive;
				$parameters['includeTeamDriveItems'] = 'true';
			}
			$files = $service->files->listFiles($parameters);
			$fileList = $files->getFiles();
			if(count($fileList) > 0) {
				$result['status'] = true;
				$result['data'] = array('join_spreadsheet' => $fileList[0]['id']);
			} else {
				$result['msg'] = 'No membership form found for '.$year;
			}
		} catch (Exception $e) {
				$error = json_decode($e->getMessage(), true);
				//if($error['error']['code'] == 404) {
				//	$result['msg'] = 'Google Calendar event not found';
			//} else {
					$result['msg'] = 'Something went wrong searching Google Drive';
					$result['error'] = $error;
			//	}
		}
	}
	return $result;
}

function updateSeasonMembershipForm($season_id) {
	$result = array(
		'status' => false,
		'msg' => '',
		'data' => null
	);
	$season = FrcPortal\Season::find($season_id);
	if(!is_null($season)) {
		$year = $season->year;
		$searchResult = getSeasonMembershipForm($year);
		if($searchResult['status'] != false) {
			$season->join_spreadsheet = $searchResult['data']['join_spreadsheet'];
			if($season->save()) {
				$result['status'] = true;
				$result['msg'] = $season->year.' membership form added';
				$result['data'] = $season;
			} else {
				$result['msg'] = 'Something went wrong adding the membership form';
			}
		} else {
			$result = $searchResult;
		}
	} else {
		$result['msg'] = 'Season not found';
	}
	return $result;
}

function pollMembershipForm($spreadsheetId, $season = null) {
	$data = false;
	if(!is_null($spreadsheetId)) {
		$data = array();
		$client = new Google_Client();
		$creds = getServiceAccountFile();
		if($creds['status'] != false) {
			$client->setAuthConfigFile($creds['data']['path']);
			$client->setScopes(['https://www.googleapis.com/auth/spreadsheets.readonly']);
			$service = new Google_Service_Sheets($client);
			// The A1 notation of the values to retrieve.
			$range = 'Form Responses 1';
			if(!is_null($season) && !$season instanceof FrcPortal\Season && is_string($season)) {
				$season = FrcPortal\Season::find($season);
			}
			$sheet = $season->membership_form_sheet;
			if(!is_null($sheet) && $sheet != '') {
				$range = $sheet;
			}

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
			$data = false;
		}
	}
	return $data;
}

function itterateMembershipFormData($data = array(), $season = null) {
	$team_num = getSettingsProp('team_number');
	$team_name = getSettingsProp('team_name');
	if(!is_null($season) && !$season instanceof FrcPortal\Season && is_string($season)) {
		$season = FrcPortal\Season::find($season);
	}
	$season_id = $season->season_id;
	$form_map = $season->membership_form_map;
	$email_column = 'email address';
	$fname_column = 'first name';
	$lname_column = 'last name';
	$userType_column = 'member type';
	$grad_column = 'year of graduation';
	$school_column = 'school';
	$pin_column = 'student id';
	$phone_column = 'phone';

	//Itterate through data
	if(count($data) > 0) {
		foreach($data as $userInfo) {
			//	$timestamp = $data['timestamp'];
			$email = $userInfo[$email_column];
			$fname = $userInfo[$fname_column];
			$lname = $userInfo[$lname_column];
			$form_user_type = $userInfo[$userType_column];
			$user_type = $form_user_type == 'Adult' ? 'Mentor' : $form_user_type;
			//	$birthday = $userInfo['birthday'];
			$grad_year = $userInfo[$grad_column];
			$school = $userInfo[$school_column];
			$student_id = $userInfo[$pin_column];
			$phone = $userInfo[$phone_column];
			$clean_phone = preg_replace('/[^0-9]/s', '', $phone);

			$user = null;
			$user_id = null;
			$user = FrcPortal\User::where('email',$email)->first();
			if(is_null($user)) {
				$user = FrcPortal\User::where('fname',$fname)->where('lname',$lname)->where('user_type',$user_type)->first();
			}
			//If user doesn't exist, add data to user table
			if(is_null($user)) {
				$school_id = '';
				if($user_type == 'Student' && $school != '') {
					$school_id = checkSchool($school);
				}
				$user = new FrcPortal\User();
				$user->email = $email;
				$user->fname = $fname;
				$user->lname = $lname;
				$gender = getGenderByFirstName($fname);
				$user->gender = $gender != false ? ucfirst($gender):'';
				$user->user_type = $user_type;
				if($user_type == 'Student') {
					if($school_id != '') {
						$user->school_id = $school_id;
					}
					if($grad_year != '') {
						$user->grad_year = $grad_year;
					}
					if($student_id != '' && is_numeric($student_id)) {
						$signin_pin = hash('SHA256',$student_id);
						$user->signin_pin = $signin_pin;
					}
				}
				if($clean_phone != '' && is_numeric($clean_phone)) {
					$user->phone = $clean_phone;
				}
				//Insert Data
				if($user->save()) {
					$user_id = $user->user_id;
					setDefaultNotifications($user_id);
					$msgData = array(
						'subject' => 'User account created for '.$team_name.'\s team portal',
						'content' =>  'Congratulations! You have been added to '.$team_name.'\s team portal.  Please go to https://'.$_SERVER["HTTP_HOST"].' to view your annual registration, event registration, season hours and more.',
						'userData' => $user
					);
				}
			}
			//Add User info into the Annual Requirements Table
			if(!is_null($season_id) && !is_null($user)) {
				$user_id = $user->user_id;
				$season_join = FrcPortal\AnnualRequirement::updateOrCreate(['season_id' => $season_id, 'user_id' => $user_id], ['join_team' => true]);
				if($season_join) {
					$msgData = array(
						'slack' => array(
						'title' => 'Annual Registration Complete',
						'body' => 'Congratulations! You have completed the Team '.$team_num.' membership form for the '.$season->year.' FRC season.'
						),
					'email' => array(
						'subject' => 'Annual Registration Complete',
						'content' =>  'Congratulations! You have completed the Team '.$team_num.' membership form for the '.$season->year.' FRC season.',
						'userData' => $user
						)
					);
					sendUserNotification($user_id, $type = 'join_team', $msgData);
				}
			}
		}
		return true;
	} else {
		return false;
	}
}

function updateSeasonRegistrationFromForm($season_id) {
	$data = false;
	$return = false;
	if(!is_null($season_id)) {
		$season = FrcPortal\Season::find($season_id);
		if(!is_null($season)) {
			$spreadsheetId = $season->join_spreadsheet != '' ? $season->join_spreadsheet:null;
			if(!is_null($spreadsheetId)) {
				$data = pollMembershipForm($spreadsheetId, $season);
				if($data != false && !empty($data)) {
					$return = itterateMembershipFormData($data, $season);
				}
			}
		}
	}
	return $return;
}

function createSchoolAbv($name = null) {
	$abv = '';
	if(!is_null($name) && is_string($name) && $name != '') {
		for($i=0; $i<strlen($name); $i++) {
			if (ctype_upper($name[$i])) {
				$abv .= $name[$i];
			}
		}
	}
	return $abv;
}


function checkSchool($school) {
	$school_id = '';
	$school_formated = str_replace(' HS', ' High School', $school);
	$school_formated = str_replace(' MS', ' Middle School', $school_formated);
	//$school_formated = stripos($school_formated,' School') === false ? $school_formated.' School': $school_formated;
	$school = FrcPortal\School::where('school_name','LIKE','%'.$school_formated.'%')->orWhere('abv','LIKE','%'.$school_formated.'%')->first();
	if(!is_null($school)) {
		$school_id = $school['school_id'];
	} else {
		$abv = createSchoolAbv($school_formated);
		$school = new FrcPortal\School();
		$school->school_name = $school_formated;
		$school->abv = $abv;
		if($school->save()) {
			$school_id = $school->school_id;
		}
	}
	return $school_id;
}
?>

<?php
function getSeasonMembershipForm($year) {
	$result = false;
	try {
		$creds = getServiceAccountData();
		if($creds == false) {
			return $result;
		}
	} catch (Exception $e) {
		$error = handleExceptionMessage($e);
		insertLogs('Warning', $error);
		throw new Exception($error);
		//$result['msg'] = 'Something went wrong searching Google Drive';
		//$result['error'] = $error;
	}
	if(!empty($year)) {
		try {
			$client = new Google_Client();
			$client->setAuthConfig($creds);
			$client->setScopes(['https://www.googleapis.com/auth/drive.readonly']);
			$service = new Google_Service_Drive($client);
			$mfn = getMembershipFormName();
			$mfn = str_replace('###YEAR###',$year,$mfn);
			$mfn = str_replace('###YEAR-1###',$year-1,$mfn);
			$query = buildGoogleDriveQuery($mfn);
			$parameters = array(
				'corpora' => 'user',
				'q' => $query,
				//'q' => 'name contains "'.$year.'" and name contains "Membership" and name contains "(Responses)" and mimeType = "application/vnd.google-apps.spreadsheet"',
				'supportsTeamDrives' => 'true',
				'pageSize' => '1'
			);
			$teamDrive = getSettingsProp('google_drive_id');
			if(!empty($teamDrive)) {
				$parameters['corpora'] = 'teamDrive';
				$parameters['teamDriveId'] = $teamDrive;
				$parameters['includeTeamDriveItems'] = 'true';
			}
			$files = $service->files->listFiles($parameters);
			$fileList = $files->getFiles();
			$result = array('join_spreadsheet' => '');
			if(count($fileList) > 0) {
				$result['join_spreadsheet'] = $fileList[0]['id'];
			}
		} catch (Exception $e) {
				$error = handleGoogleAPIException($e, 'Google Drive');
				insertLogs('Warning', $error);
				throw new Exception($error);
				//$result['msg'] = 'Something went wrong searching Google Drive';
				//$result['error'] = $error;
		}
	}
	return $result;
}

function getGradYear($input = null) {
	if(empty($input)) {
		return null;
	}
	$year = date('Y');
	$month = date('m');
	$number_grades = array(9,10,11,12);
	if(is_numeric($input) && strlen($input) == 4) {
		return (integer) $input;
	}	else if(is_numeric($input) && strlen($input) == 2 && !in_array($input, $number_grades) && $input > 19) {
			return (integer) '20'.$input;
	} else if(is_numeric($input) && in_array($input, $number_grades)) {
		//Default to early in year < June
		$input = (integer) $input;
		$map = array(
			'year_9' => $year+3,
			'year_10' => $year+2,
			'year_11' => $year+1,
			'year_12' => $year
		);
		if($month >= 6) {
			$map = array(
				'year_9' => $year+4,
				'year_10' => $year+3,
				'year_11' => $year+2,
				'year_12' => $year+1
			);
		}
		return (integer) $map['year_'.$input];
	}
	if(is_string($input) && in_array(strtolower($input), array('f','s','j','sr')) || in_array(strtolower($input), array('freshman','sophmore','junior','senior'))) {
		//Default to early in year < June
		$map = array(
			'f' => $year+3,
			'freshman' => $year+3,
			's' => $year+2,
			'sophmore' => $year+2,
			'j' => $year+1,
			'junior' => $year+1,
			'sr' => $year,
			'senior' => $year
		);
		if($month >= 6) {
			$map = array(
				'f' => $year+4,
				'freshman' => $year+4,
				's' => $year+3,
				'sophmore' => $year+3,
				'j' => $year+2,
				'junior' => $year+2,
				'sr' => $year+1,
				'senior' => $year+1
			);
		}
		return (integer) $map[strtolower($input)];
	}
	return null;
}

function createSchoolAbv($name = null) {
	$abv = '';
	if(!empty($name) && is_string($name) && $name != '') {
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
	$school_formated = trim($school_formated);
	if (strpos(strtolower($school_formated), 'home') !== false && strpos(strtolower($school_formated), 'school') !== false) {
	  $school_formated = 'Homeschool';
	}
	//$school_formated = stripos($school_formated,' School') === false ? $school_formated.' School': $school_formated;
	$school = FrcPortal\School::where('school_name','LIKE','%'.$school_formated.'%')->orWhere('abv','LIKE','%'.$school_formated.'%')->first();
	if(!empty($school)) {
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

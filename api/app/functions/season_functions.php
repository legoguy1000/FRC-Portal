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
?>

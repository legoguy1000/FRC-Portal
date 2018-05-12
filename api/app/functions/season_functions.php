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
			$client->setAuthConfigFile(__DIR__.'/../secured/team-2363-portal-0c12aca54f1c.json');
			$client->setScopes(['https://www.googleapis.com/auth/drive.readonly']);
			$service = new Google_Service_Drive($client);
			$parameters = array(
				'corpora' => 'teamDrive',
				'q' => 'name contains "'.$year.'" and name contains "Membership" and name contains "(Responses)" and mimeType = "application/vnd.google-apps.spreadsheet"',
				'includeTeamDriveItems' => 'true',
				'supportsTeamDrives' => 'true',
				'teamDriveId' => '0AI0WovuxnF1zUk9PVA',
				'pageSize' => '1'
			);
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

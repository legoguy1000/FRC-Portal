<?php
require_once(__DIR__ . '/../includes.php');
//

$season_id = null;
$spreadsheetId = null;
$season = FrcPortal\Season::where('end_date','>=',date('Y-m-d'))->orderBy('start_date', 'ASC')->first();
if(!empty($season)) {
	$season_id = $season->season_id;
	$spreadsheetId = $season->join_spreadsheet != '' ? $season->join_spreadsheet:null;
	if(is_null($spreadsheetId)) {
		try {
      $result = getSeasonMembershipForm($season->year);
			$spreadsheetId = $result['data']['join_spreadsheet'];
			$season->join_spreadsheet = $spreadsheetId;
			$season->save();
    } catch (Exception $e) {
      $spreadsheetId = false;
    }
	}
} else {
	$year = date('Y')+1;
	try {
		$result = getSeasonMembershipForm($season->year);
		$spreadsheetId = $result['data']['join_spreadsheet'];
		$season = FrcPortal\Season::where('year',$year)->first();
		if(!is_null($season)) {
			$season_id = $season['season_id'];
			$season->join_spreadsheet = $spreadsheetId;
			$season->save();
		}
	} catch (Exception $e) {
		$spreadsheetId = false;
	}
}
if(!empty($season)) {
	if($season->pollMembershipForm()) {
		$season->itterateMembershipFormData();
	}
}



?>

<?php
require_once(__DIR__ . '/../includes.php');
//

$season_id = null;
$spreadsheetId = null;
$season = FrcPortal\Season::where('bag_day','>=',date('Y-m-d'))->orderBy('start_date', 'ASC')->first();
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
$data = pollMembershipForm($spreadsheetId, $season);
if($data != false && !empty($data)) {
	$return = itterateMembershipFormData($data, $season);
}



?>

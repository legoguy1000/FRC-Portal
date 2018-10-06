<?php
require_once(__DIR__ . '/../includes.php');

//Change User Status after graduation
$changeUserStatus = getSettingsProp('enable_cronjob-changeUserStatus');
if($changeUserStatus) {
  require_once(__DIR__ . '/cronjob-changeUserStatus.php');
}

//Import Slack Profiles
$importSlackProfiles = getSettingsProp('enable_cronjob-importSlackProfiles');
if($importSlackProfiles && (date('H') == 00 || date('H') == 13)) {
  require_once(__DIR__ . '/cronjob-importSlackProfiles.php');
}

//Poll Membership Form
$pollMembershipFormResponses = getSettingsProp('enable_cronjob-pollMembershipFormResponses');
if($pollMembershipFormResponses) {
  require_once(__DIR__ . '/cronjob-pollMembershipFormResponses.php');
}

//Update Google Events
$updateEventsFromGoogle = getSettingsProp('enable_cronjob-updateEventsFromGoogle');
if($updateEventsFromGoogle) {
  require_once(__DIR__ . '/cronjob-updateEventsFromGoogle.php');
}

//Remove Sign Ins that haven't signed out
$tooLong = getSettingsProp('enable_cronjob-tooLong');
if($tooLong) {
  require_once(__DIR__ . '/cronjob-tooLong.php');
}

//End of Day to Slack
$endOfDayHoursToSlack = getSettingsProp('enable_cronjob-endOfDayHoursToSlack');
if($endOfDayHoursToSlack && date('H') == 21) {
  require_once(__DIR__ . '/cronjob-endOfDayHoursToSlack.php');
}
?>

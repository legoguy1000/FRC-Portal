<?php
include('includes.php');

//$authToken = checkToken(true,true);
$calendar = getIniProp('google_calendar_id');
$api_key = getIniProp('google_api_key');
$optParams = array();
if(isset($_GET['q']) && $_GET['q'] != '') {
	$q = trim($_GET['q']);
	$optParams['q'] = $q;
}
if(isset($_GET['timeMax']) && $_GET['timeMax'] != '') {
	$timeMax = $_GET['timeMax'];
	$optParams['timeMax'] = $timeMax;
}
if(isset($_GET['timeMin']) && $_GET['timeMin'] != '') {
	$timeMin = $_GET['timeMin'];
	$optParams['timeMin'] = $timeMin;
}
$optParams['maxResults'] = 2500;
$optParams['orderBy'] = 'startTime';
$optParams['singleEvents'] = true;

$allEvents = array();
$client = new Google_Client();
$client->setDeveloperKey($api_key);
$service = new Google_Service_Calendar($client);
$events = $service->events->listEvents($calendar, $optParams);

while(true) {
  foreach ($events->getItems() as $event) {
		if($event->status == 'confirmed') {
			$temp = $event;
			$temp->{"allDay"} = false;
			$start = $event->start->dateTime;
			if(empty($start)) {
				$start = $event->start->date;
				$temp->allDay = true;
			}
    	$allEvents[] = $temp;
		}
  }
  $pageToken = $events->getNextPageToken();
  if ($pageToken) {
    $optParams = array('pageToken' => $pageToken);
    $events = $service->events->listEvents($calendar, $optParams);
  } else {
    break;
  }
}
die(json_encode($allEvents));










/*$users = getAllEventsFilter($filter, $limit, $order, $page);
if(!empty($users)) {
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$users['data'], 'query'=>$users['query'], 'total'=>$users['total'], 'maxPage'=>$users['maxPage'])));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
} */



?>

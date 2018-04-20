<?php
include('includes.php');

//$authToken = checkToken(true,true);
$calendar = getIniProp('google_calendar_id');
$api_key = getIniProp('google_api_key');
$optParams = array();
if(isset($_GET['q']) && $_GET['q'] != '' && $_GET['q'] != 'null' && $_GET['q'] != 'undefined') {
	$q = trim($_GET['q']);
	$optParams['q'] = $q;
}
if(isset($_GET['timeMax']) && $_GET['timeMax'] != '' && $_GET['timeMax'] != 'null' && $_GET['timeMax'] != 'undefined') {
	$timeMax = date('c', strtotime($_GET['timeMax']));
	if(is_numeric($_GET['timeMax'])) {
		$timeMax = date('c',$_GET['timeMax']/1000);
	}
	$optParams['timeMax'] = $timeMax;
}
$optParams['timeMin'] = date('c',strtotime('-6 months'));
if(isset($_GET['timeMin']) && $_GET['timeMin'] != '' && $_GET['timeMin'] != 'null' && $_GET['timeMin'] != 'undefined') {
	$timeMin = date('c', strtotime($_GET['timeMin']));
	if(is_numeric($_GET['timeMin'])) {
		$timeMin = date('c',$_GET['timeMin']/1000);
	}
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
			$temp = array(
				'google_event' => $event,
				'name' => $event->summary,
				'location' => $event->location,
				'google_cal_id' => $event->id,
				'allDay' => false,
				'event_start' => null,
				'event_end' => null,
				'event_start_unix' => null,
				'event_end_unix' => null,
				'event_start_iso' => null,
				'event_end_iso' => null,
				'details' => $event->description,
			);
			if(empty($event->start->dateTime)) {
				$temp['allDay'] = true;
				$temp['event_start'] = $event->start->date.' 00:00:00';
				$temp['event_end'] = $event->end->date.' 23:59:59';
			} else {
				$temp['event_start'] = date('Y-m-d H:i:s', strtotime($event->start->dateTime));
				$temp['event_end'] =date('Y-m-d H:i:s', strtotime($event->end->dateTime));
			}
			$temp['event_start_unix'] = strtotime($temp['event_start']);
			$temp['event_end_unix'] = strtotime($temp['event_end']);
			$temp['event_start_iso'] = date('c',strtotime($temp['event_start']));
			$temp['event_end_iso'] = date('c',strtotime($temp['event_end']));
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
$data = array(
	'data'=>$allEvents,
	'count'=>count($allEvents),
	'status'=>true
);
die(json_encode($data));










/*$users = getAllEventsFilter($filter, $limit, $order, $page);
if(!empty($users)) {
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$users['data'], 'query'=>$users['query'], 'total'=>$users['total'], 'maxPage'=>$users['maxPage'])));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
} */



?>

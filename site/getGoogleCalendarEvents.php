<?php
include('includes.php');

//$authToken = checkToken(true,true);

$filter = null;
$limit = null;
$order = null;
$page = null;
if(isset($_GET['filter']) && $_GET['filter'] != '') {
	$filter = $_GET['filter'];
}
if(isset($_GET['limit']) && $_GET['limit'] != '') {
	$limit = $_GET['limit'];
}
if(isset($_GET['order']) && $_GET['order'] != '') {
	$order = $_GET['order'];
}
if(isset($_GET['page']) && $_GET['page'] != '') {
	$page = $_GET['page'];
}

$client = new Google_Client();
$client->setDeveloperKey($api_key);
$service = new Google_Service_Calendar($client);
$events = $service->events->listEvents('iifound.org_qm79uhga7lqeguhn25tjifl8g4@group.calendar.google.com');

while(true) {
  foreach ($events->getItems() as $event) {
    echo $event->getSummary().'<br/>';
  }
  $pageToken = $events->getNextPageToken();
  if ($pageToken) {
    $optParams = array('pageToken' => $pageToken);
    $events = $service->events->listEvents('iifound.org_qm79uhga7lqeguhn25tjifl8g4@group.calendar.google.com', $optParams);
  } else {
    break;
  }
}











/*$users = getAllEventsFilter($filter, $limit, $order, $page);
if(!empty($users)) {
	die(json_encode(array('status'=>true, 'msg'=>'', 'data'=>$users['data'], 'query'=>$users['query'], 'total'=>$users['total'], 'maxPage'=>$users['maxPage'])));
} else {
	die(json_encode(array('status'=>false, 'msg'=>'Something went wrong', 'data'=>array())));
} 8?



?>

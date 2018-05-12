<?php
include(__DIR__ . '/../includes.php');

$events = FrcPortal\Event::havingRaw('date(event_start) >= CURDATE()-interval 3 month')->get();
if(count($events) > 0) {
	foreach($result as $event) {
		if(isset($event->google_cal_id) && $event->google_cal_id != '') {
			syncGoogleCalendarEvent($event['google_cal_id'], $event['event_id']);
		}
	}
}


?>

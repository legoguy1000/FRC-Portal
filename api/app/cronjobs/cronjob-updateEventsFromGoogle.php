<?php
require_once(__DIR__ . '/../includes.php');

$events = FrcPortal\Event::havingRaw('date(event_start) >= CURDATE()-interval 3 month')->get();
if(count($events) > 0) {
	foreach($events as $event) {
		if(isset($event->google_cal_id) && $event->google_cal_id != '') {
			try {
				syncGoogleCalendarEvent($event->event_id);
			} catch (Exception $e) {}
		}
	}
}


?>

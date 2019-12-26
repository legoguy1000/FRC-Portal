<?php
use Illuminate\Database\Capsule\Manager as Capsule;
require_once(__DIR__.'/../includes.php');

if(!Capsule::schema()->hasTable('settings')) {
  require_once('Setting.php');
}
if(!Capsule::schema()->hasTable('seasons')) {
  require_once('Season.php');
}
if(!Capsule::schema()->hasTable('schools')) {
  require_once('School.php');
}
if(!Capsule::schema()->hasTable('users')) {
  require_once('User.php');
}
if(!Capsule::schema()->hasTable('oauth_ids')) {
  require_once('Oauth.php');
}
if(!Capsule::schema()->hasTable('notification_preferences')) {
  require_once('NotificationPreference.php');
}
if(!Capsule::schema()->hasTable('missing_hours_requests')) {
  require_once('MissingHoursRequest.php');
}
if(!Capsule::schema()->hasTable('meeting_hours')) {
  require_once('MeetingHour.php');
}
if(!Capsule::schema()->hasTable('annual_requirements')) {
  require_once('AnnualRequirement.php');
}
if(!Capsule::schema()->hasTable('event_types')) {
  require_once('EventType.php');
}
if(!Capsule::schema()->hasTable('events')) {
  require_once('Event.php');
}
if(!Capsule::schema()->hasTable('event_cars')) {
  require_once('EventCar.php');
}
if(!Capsule::schema()->hasTable('event_rooms')) {
  require_once('EventRoom.php');
}
if(!Capsule::schema()->hasTable('event_requirements')) {
  require_once('EventRequirement.php');
}
if(!Capsule::schema()->hasTable('event_time_slots')) {
  require_once('EventTimeSlot.php');
}
if(!Capsule::schema()->hasTable('event_time_slots_event_requirements')) {
  require_once('EventTimeSlotUser.php');
}
if(!Capsule::schema()->hasTable('event_food')) {
  require_once('EventFood.php');
}
if(!Capsule::schema()->hasTable('event_food_event_requirements')) {
  require_once('EventFoodUser.php');
}
if(!Capsule::schema()->hasTable('logs')) {
  require_once('Logs.php');
}
if(!Capsule::schema()->hasTable('user_credentials')) {
  require_once('UserCredential.php');
}
require_once(__DIR__.'/_CreateForeignKeys.php');

//require_once('UserCategory.php');
//require_once('UserUserCategory.php');




?>

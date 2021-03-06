<?php
require_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;

$users = Capsule::schema()->hasTable('users');
$user_credentials = Capsule::schema()->hasTable('user_credentials');
$schools = Capsule::schema()->hasTable('schools');
$oauth_ids = Capsule::schema()->hasTable('oauth_ids');
$notification_preferences = Capsule::schema()->hasTable('notification_preferences');
//Time
$missing_hours_requests = Capsule::schema()->hasTable('missing_hours_requests');
$meeting_hours = Capsule::schema()->hasTable('meeting_hours');
//Seasons
$seasons = Capsule::schema()->hasTable('seasons');
$annual_requirements = Capsule::schema()->hasTable('annual_requirements');
//Events
$events = Capsule::schema()->hasTable('events');
$event_requirements = Capsule::schema()->hasTable('event_requirements');
$event_cars = Capsule::schema()->hasTable('event_cars');
$event_rooms = Capsule::schema()->hasTable('event_rooms');
$event_types = Capsule::schema()->hasTable('event_types');
$event_time_slots = Capsule::schema()->hasTable('event_time_slots');
$event_time_slots_event_requirements = Capsule::schema()->hasTable('event_time_slots_event_requirements');
$event_food = Capsule::schema()->hasTable('event_food');
$event_food_event_requirements = Capsule::schema()->hasTable('event_food_event_requirements');
//logs
$logs = Capsule::schema()->hasTable('logs');
//Roles & Permissions
/* Not used
$roles = Capsule::schema()->hasTable('roles');
$permissions = Capsule::schema()->hasTable('permissions');
$role_user = Capsule::schema()->hasTable('role_user');
$permission_role = Capsule::schema()->hasTable('permission_role');
*/


//Users Table
if($users && $schools) {
  Capsule::schema()->table('users', function ($table, $as = null, $connection = null) {
    $table->foreign('school_id')->references('school_id')->on('schools')->onDelete('set null')->onUpdate('cascade');
  });
}

//OAuth IDs Table
if($users && $oauth_ids) {
  Capsule::schema()->table('oauth_ids', function ($table, $as = null, $connection = null) {
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
  });
}

//User Credentials Table
if($users && $user_credentials) {
  Capsule::schema()->table('user_credentials', function ($table, $as = null, $connection = null) {
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
  });
}


//Nootification Preferences Table
if($users && $notification_preferences) {
  Capsule::schema()->table('notification_preferences', function ($table, $as = null, $connection = null) {
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
  });
}

//Missing Hours Requests Table
if($users && $missing_hours_requests) {
  Capsule::schema()->table('missing_hours_requests', function ($table, $as = null, $connection = null) {
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
    $table->foreign('approved_by')->references('user_id')->on('users')->onDelete('set null')->onUpdate('cascade');
  });
}

//Meeting Hours Table
if($users && $meeting_hours) {
  Capsule::schema()->table('meeting_hours', function ($table, $as = null, $connection = null) {
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
  });
}

//Annual Requirements Table
if($seasons && $users && $annual_requirements) {
  Capsule::schema()->table('annual_requirements', function ($table, $as = null, $connection = null) {
    $table->foreign('season_id')->references('season_id')->on('seasons')->onDelete('cascade')->onUpdate('cascade');
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
  });
}

//Events Table
if($users && $event_types && $events) {
  Capsule::schema()->table('events', function ($table, $as = null, $connection = null) {
     $table->foreign('poc_id')->references('user_id')->on('users')->onDelete('set null')->onUpdate('cascade');
     $table->foreign('type')->references('type')->on('event_types')->onDelete('set null')->onUpdate('cascade');
  });
}

//Event Requirements Table
if($users && $events && $event_rooms && $event_cars && $event_requirements) {
  Capsule::schema()->table('event_requirements', function ($table, $as = null, $connection = null) {
     $table->foreign('user_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
     $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade')->onUpdate('cascade');
     $table->foreign('room_id')->references('room_id')->on('event_rooms')->onDelete('set null')->onUpdate('cascade');
     $table->foreign('car_id')->references('car_id')->on('event_cars')->onDelete('set null')->onUpdate('cascade');
  });
}

//Event Cars
if($event_requirements && $event_cars) {
  Capsule::schema()->table('event_cars', function ($table, $as = null, $connection = null) {
    $table->foreign(['event_id','user_id'])->references(['event_id','user_id'])->on('event_requirements')->onDelete('cascade')->onUpdate('cascade');
  });
}

//Event Rooms
if($event_requirements && $event_rooms) {
  Capsule::schema()->table('event_rooms', function ($table, $as = null, $connection = null) {
    $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade')->onUpdate('cascade');
  });
}

//Event Time Slots
if($events && $event_time_slots) {
  Capsule::schema()->table('event_time_slots', function ($table, $as = null, $connection = null) {
    $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade')->onUpdate('cascade');
  });
}

//Event Time Slots Users Join Table
if($event_time_slots_event_requirements && $event_time_slots && $event_requirements) {
  Capsule::schema()->table('event_time_slots_event_requirements', function ($table, $as = null, $connection = null) {
    $table->foreign('time_slot_id')->references('time_slot_id')->on('event_time_slots')->onDelete('cascade')->onUpdate('cascade');
    $table->foreign('ereq_id')->references('ereq_id')->on('event_requirements')->onDelete('cascade')->onUpdate('cascade');
  });
}

//Event Food
if($events && $event_food) {
  Capsule::schema()->table('event_food', function ($table, $as = null, $connection = null) {
    $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade')->onUpdate('cascade');
  });
}

//Event Food Users Join Table
if($event_food_event_requirements && $event_food && $event_requirements) {
  Capsule::schema()->table('event_food_event_requirements', function ($table, $as = null, $connection = null) {
    $table->foreign('food_id')->references('food_id')->on('event_food')->onDelete('cascade')->onUpdate('cascade');
    $table->foreign('ereq_id')->references('ereq_id')->on('event_requirements')->onDelete('cascade')->onUpdate('cascade');
  });
}

//Logs and Users
if($logs && $users) {
  Capsule::schema()->table('logs', function ($table, $as = null, $connection = null) {
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null')->onUpdate('cascade');
  });
}
/* Not used
//Roles & Permissions
if($role_user && $roles && $users) {
  Capsule::schema()->table('role_user', function ($table, $as = null, $connection = null) {
    $table->foreign('role_id')->references('role_id')->on('roles')->onUpdate('cascade')->onDelete('cascade');
    $table->foreign('user_id')->references('user_id')->on('users')->onUpdate('cascade')->onDelete('cascade');
  });
}
if($permission_role && $roles && $permissions) {
  Capsule::schema()->table('role_user', function ($table, $as = null, $connection = null) {
    $table->foreign('role_id')->references('role_id')->on('roles')->onUpdate('cascade')->onDelete('cascade');
    $table->foreign('permission_id')->references('permission_id')->on('permissions')->onUpdate('cascade')->onDelete('cascade');
  });
}
*/

?>

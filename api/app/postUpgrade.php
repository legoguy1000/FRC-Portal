<?php
include_once(__DIR__.'/includes.php');
use Illuminate\Database\Capsule\Manager as Capsule;
$version = VERSION;

//always update composer after pull
updateComposer();

/**
* 2.6.0
**/
if($version >= '2.6.0') {
  //Create Event Types Table
  if(Capsule::schema()->hasColumn('events','type')) {
    try {
      Capsule::schema()->table('events', function ($table) {
        $table->string('type')->nullable()->default(null)->change();
      });
    } catch (Exception $e) { }
  }
  if(!Capsule::schema()->hasTable('event_types')) {
    include_once('database/EventType.php');
    try {
      Capsule::schema()->table('events', function ($table) {
        $table->foreign('type')->references('type')->on('event_types')->onDelete('set null')->onUpdate('cascade');
      });
    } catch (Exception $e) { }
  }
}

/**
* 2.7.0
**/
if($version >= '2.7.0') {
  //Change Column Name
  if(Capsule::schema()->hasTable('events') && Capsule::schema()->hasColumn('events','time_slots') && !Capsule::schema()->hasColumn('events','time_slots_required')) {
    //Backup Database
    exportDB();
    try {
      Capsule::schema()->table('events', function($table) {
        $table->renameColumn('time_slots', 'time_slots_required');
      });
    } catch (Exception $e) { }
  }
}

/**
* 2.10.0
**/
if($version >= '2.9.1') {
  if(!Capsule::schema()->hasTable('event_food')) {
    include_once('database/EventFood.php');
    try {
      Capsule::schema()->table('event_food', function ($table) {
        $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade')->onUpdate('cascade');
      });
    } catch (Exception $e) { }
  }
  if(!Capsule::schema()->hasTable('event_food_event_requirements')) {
    include_once('database/EventFoodUser.php');
    try {
      Capsule::schema()->table('event_food_event_requirements', function ($table) {
        $table->foreign('food_id')->references('food_id')->on('event_food')->onDelete('cascade')->onUpdate('cascade');
        $table->foreign('ereq_id')->references('ereq_id')->on('event_requirements')->onDelete('cascade')->onUpdate('cascade');
      });
    } catch (Exception $e) { }
  }
  if(!Capsule::schema()->hasTable('seasons')) {
    try {
      Capsule::schema()->table('seasons', function ($table) {
        $table->integer('hour_requirement_week');
      });
    } catch (Exception $e) { }
  }

}

//Create User Category Tables
/*
include_once('UserCategory.php');
include_once('UserUserCategory.php');
*/


/*
//Add Column
if(Capsule::schema()->hasTable('events') && !Capsule::schema()->hasColumn('events','payment_amount')) {
  //Backup Database
  exportDB();
  try {
    Capsule::schema()->table('events', function($table) {
      $table->decimal('payment_amount', 5, 2)->default(00.00);
    });
  } catch (Exception $e) { }
}
//Add Column
if(Capsule::schema()->hasTable('events') && !Capsule::schema()->hasColumn('events','hotel_info')) {
  //Backup Database
  exportDB();
  try {
    Capsule::schema()->table('events', function($table) {
    $table->text('hotel_info');
    });
  } catch (Exception $e) { }
} */
?>

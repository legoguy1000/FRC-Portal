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
* 2.8.0
**/
if($version >= '2.8.0') {
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
}

//Create User Category Tables
/*
include_once('UserCategory.php');
include_once('UserUserCategory.php');
*/
?>

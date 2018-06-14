<?php
include('includes.php');
use Illuminate\Database\Capsule\Manager as Capsule;
$version = VERSION;

/**
* 2.6.0
**/
//Create Event Types Table
if($version <= '2.6.0') {
  $eventTypesExists = Capsule::schema()->hasTable('event_types');
  if(!$eventTypesExists) {
    include_once('EventType.php');
    try {
      Capsule::schema()->table('events', function ($table) {
        $table->foreign('type')->references('type')->on('event_types')->onDelete('set null')->onUpdate('cascade');
      });
    }
  }
}

/**
* 2.7.0
**/
if($version <= '2.7.0') {

  //add dbal package
  shell_exec("composer install");
  shell_exec("composer dump-autoload");
}

/**
*
**/

?>

<?php
include_once(__DIR__.'/includes.php');
use Illuminate\Database\Capsule\Manager as Capsule;
$version = VERSION;

/**
* 2.6.0
**/
if($version >= '2.6.0') {
  //Create Event Types Table
  $eventTypesExists = Capsule::schema()->hasTable('event_types');
  if(!$eventTypesExists) {
    include_once('EventType.php');
    try {
      Capsule::schema()->table('events', function ($table) {
        $table->foreign('type')->references('type')->on('event_types')->onDelete('set null')->onUpdate('cascade');
      });
    } catch (Exception $e) {

    }
  }
}

/**
* 2.7.0
**/
if($version >= '2.7.0') {

  //Create User Category Tables
  /*
  include_once('UserCategory.php');
  include_once('UserUserCategory.php');

  */
  //add dbal package
  shell_exec("composer install");
  shell_exec("composer dump-autoload");
}

/**
*
**/

?>

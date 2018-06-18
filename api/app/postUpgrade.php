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

  //update Foreign Keys
  $users = Capsule::schema()->hasTable('$users');
  $annual_requirements = Capsule::schema()->hasTable('annual_requirements');
  $event_requirements = Capsule::schema()->hasTable('event_requirements');
  $event_cars = Capsule::schema()->hasTable('event_cars');
  if($users && $annual_requirements) {
    try {
      Capsule::schema()->table('annual_requirements', function ($table) {
        $table->dropForeign('annual_requirements_user_id_foreign');
        $table->foreign('user_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
      });
    } catch (Exception $e) { }
  }
  if($users && $event_requirements) {
    try {
      Capsule::schema()->table('event_requirements', function ($table) {
        $table->dropForeign('event_requirements_user_id_foreign');
        $table->foreign('user_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
        $table->unique(['event_id','user_id']);
      });
    } catch (Exception $e) { }
  }
  if($event_cars && $event_requirements) {
    try {
      Capsule::schema()->table('event_cars', function ($table) {
        $table->dropForeign('event_cars_user_id_foreign');
        $table->dropForeign('event_cars_event_id_foreign');
        $table->unique(['event_id','user_id']);
        $table->foreign(['event_id','user_id'])->references(['event_id','user_id'])->on('event_requirements')->onDelete('cascade')->onUpdate('cascade');
      });
    } catch (Exception $e) { }
  }
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

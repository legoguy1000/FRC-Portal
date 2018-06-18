<?php

include_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('event_time_slots_event_requirements', function ($table) {
  $table->char('time_slot_id',13)->index();
  $table->char('ereq_id',13)->nullable()->index();

  $table->foreign('time_slot_id')->references('time_slot_id')->on('event_time_slots')->onDelete('cascade')->onUpdate('cascade');
  $table->foreign('ereq_id')->references('ereq_id')->on('event_requirements')->onDelete('cascade')->onUpdate('cascade');
});

?>

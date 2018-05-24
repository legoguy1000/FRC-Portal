<?php

require "../includes.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('event_time_slots_users', function ($table) {
  $table->char('time_slot_user_id',13)->primary();
  $table->char('time_slot_id',13)->index();
  $table->char('user_id',13)->index();

  $table->foreign('time_slot_id')->references('time_slot_id')->on('event_time_slots')->onDelete('cascade')->onUpdate('cascade');
  $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null')->onUpdate('cascade');
});

?>

<?php

require "../includes.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('event_time_slots_users', function ($table) {
       $table->char('time_slot_id',13)->primary();
       $table->char('user_id',13)->index();

       $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade')->onUpdate('cascade');
       $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null')->onUpdate('cascade');
});

?>

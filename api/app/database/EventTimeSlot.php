<?php

require "../includes.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('event_time_slots', function ($table) {
       $table->char('time_slot_id',13)->primary();
       $table->char('event_id',13)->index();
       $table->string('name');
       $table->text('description');
       $table->dateTime('time_start');
       $table->dateTime('time_end');

       $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade')->onUpdate('cascade');
});

?>

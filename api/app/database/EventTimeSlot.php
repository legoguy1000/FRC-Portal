<?php

require_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('event_time_slots', function ($table) {
       $table->char('time_slot_id',13)->primary();
       $table->char('event_id',13)->index();
       $table->string('name');
       $table->text('description');
       $table->dateTime('time_start');
       $table->dateTime('time_end');
});

?>

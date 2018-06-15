<?php

include_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('event_rooms', function ($table) {
       $table->char('room_id',13)->primary();
       $table->char('event_id',13)->index();
       $table->string('user_type');
       $table->string('gender');

       $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade')->onUpdate('cascade');
});

?>

<?php

require "../includes.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('event_cars', function ($table) {
       $table->char('car_id',13)->primary();
       $table->char('event_id',13)->index();
       $table->char('user_id',13)->index();
       $table->int('car_space')->default(0);

       $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade')->onUpdate('cascade');
       $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null')->onUpdate('cascade');
       $table->foreign('user_id')->references('user_id')->on('event_requirements')->onDelete('cascade')->onUpdate('cascade');
   });

?>
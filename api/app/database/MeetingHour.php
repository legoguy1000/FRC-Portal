<?php

require_once "../includes.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('meeting_hours', function ($table) {
       $table->char('hours_id',13)->primary();
       $table->char('user_id',13)->index();
       $table->dateTime('time_in')->nullable()->default(null)->index();
       $table->dateTime('time_out')->nullable()->default(null)->index();

       $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
   });

?>

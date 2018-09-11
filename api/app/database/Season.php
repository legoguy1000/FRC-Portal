<?php

include_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('seasons', function ($table) {
       $table->char('season_id',13)->primary();
       $table->integer('year');
       $table->string('game_name');
       $table->string('game_logo',500);
       $table->dateTime('start_date');
       $table->dateTime('bag_day');
       $table->dateTime('end_date');
       $table->integer('hour_requirement');
       $table->integer('hour_requirement_week');
       $table->string('join_spreadsheet',500);
       $table->text('membership_form_map');
       $table->timestamps();
});

?>

<?php

require_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('seasons', function ($table) {
       $table->char('season_id',13)->primary();
       $table->integer('year');
       $table->string('game_name');
       $table->string('game_logo',500);
       $table->dateTime('start_date');
       $table->dateTime('bag_day')->nullable()->default(null);
       $table->dateTime('end_date');
       $table->integer('hour_requirement')->default(0);
       $table->integer('hour_requirement_week')->default(0);
       $table->string('join_spreadsheet',500);
       $table->text('membership_form_map')->nullable()->default(null);
       $table->string('membership_form_sheet')->nullable()->default('Form Responses 1');
       $table->timestamps();
});

?>

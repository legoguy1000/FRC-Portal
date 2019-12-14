<?php

require_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('annual_requirements', function ($table) {
  $table->char('req_id',13)->primary();
  $table->char('user_id',13);
  $table->char('season_id',13);
  $table->boolean('join_team')->default(0);
  $table->boolean('stims')->default(0);
  $table->dateTime('stims_date')->nullable()->default(null);
  $table->boolean('dues')->default(0);
  $table->dateTime('dues_date')->nullable()->default(null);
  $table->timestamps();

  $table->unique(['season_id','user_id']);
});

?>

<?php

include_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('event_cars', function ($table) {
  $table->char('car_id',13)->primary();
  $table->char('event_id',13)->index();
  $table->char('user_id',13)->nullable()->default(null)->index();
  $table->integer('car_space')->default(0);

  $table->unique(['user_id','event_id']);
});

?>

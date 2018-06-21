<?php

include_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('missing_hours_requests', function ($table) {
  $table->char('request_id',13)->primary();
  $table->char('user_id',13)->index();
  $table->dateTime('time_in');
  $table->dateTime('time_out');
  $table->text('comment');
  $table->dateTime('request_date');
  $table->boolean('approved')->nullable()->default(null);
  $table->dateTime('approved_date')->nullable()->default(null);
  $table->char('approved_by',13)->index()->nullable()->default(null);
});

?>

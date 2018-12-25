<?php

require_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('logs', function ($table) {
  $table->char('log_id',13)->primary();
  $table->string('level');
  $table->char('user_id',13)->index()->nullable()->default(null);;
  $table->text('message');
  $table->timestamps();
});

?>

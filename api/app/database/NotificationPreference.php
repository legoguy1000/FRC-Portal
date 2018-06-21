<?php

include_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('notification_preferences', function ($table) {
  $table->char('pref_id',13)->primary();
  $table->char('user_id',13)->index();
  $table->string('method');
  $table->string('type');
});

?>

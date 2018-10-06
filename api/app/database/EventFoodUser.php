<?php

require_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('event_food_event_requirements', function ($table) {
  $table->char('food_id',13)->index();
  $table->char('ereq_id',13)->nullable()->index();
});

?>

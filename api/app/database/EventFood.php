<?php

require_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('event_food', function ($table) {
       $table->char('food_id',13)->primary();
       $table->char('event_id',13)->index();
       $table->string('group');
       $table->text('description');
});

?>

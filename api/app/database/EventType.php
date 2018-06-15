<?php

include_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('event_types', function ($table) {
  $table->char('type_id',13)->primary();
  $table->string('type')->index();
  $table->text('description');
  $table->timestamps();
});

$et = FrcPortal\EventType::updateOrCreate(['type' => 'Demo'], ['description' => 'Demo Events']);
$et = FrcPortal\EventType::updateOrCreate(['type' => 'Season Event'], ['description' => 'Season Events']);
$et = FrcPortal\EventType::updateOrCreate(['type' => 'Off Season Event'], ['description' => 'Off Season Events']);



?>

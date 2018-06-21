<?php

include_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('event_requirements', function ($table) {
  $table->char('ereq_id',13)->primary();
  $table->char('event_id',13);
  $table->char('user_id',13);
  $table->boolean('registration')->default(0);
  $table->boolean('payment')->default(0);
  $table->boolean('permission_slip')->default(0);
  $table->boolean('food')->default(0);
  $table->char('room_id',13)->nullable()->default(null);
  $table->boolean('can_drive')->default(0);
  $table->char('car_id',13)->nullable()->default(null);
  $table->text('comments');
  $table->boolean('attendance_confirmed')->default(0);
  $table->timestamps();

  $table->unique(['event_id','user_id']);
});
?>

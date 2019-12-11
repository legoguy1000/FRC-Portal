<?php

require_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('events', function ($table) {
  $table->char('event_id',13)->primary();
  $table->string('google_cal_id',50)->nullable()->default(null);
  $table->string('name');
  $table->string('type')->nullable()->default(null);
  $table->dateTime('event_start');
  $table->dateTime('event_end');
  $table->dateTime('registration_deadline')->nullable()->default(null);
  $table->string('registration_deadline_gcalid',50)->nullable()->default(null);
  $table->text('details');
  $table->string('location',500)->nullable()->default(null);
  $table->boolean('payment_required')->default(0);
  $table->boolean('permission_slip_required')->default(0);
  $table->boolean('food_required')->default(0);
  $table->boolean('room_required')->default(0);
  $table->boolean('drivers_required')->default(0);
  $table->boolean('time_slots_required')->default(0);
  $table->char('poc_id',13)->nullable()->default(null)->index();
  $table->decimal('payment_amount', 5, 2)->default(00.00);
  $table->text('hotel_info')->default('');
  $table->timestamps();
});

?>

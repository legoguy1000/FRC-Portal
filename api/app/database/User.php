<?php

require_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('users', function ($table) {
  $table->char('user_id',13)->primary();
  $table->string('fname')->default('');
  $table->string('lname')->default('');
  $table->string('email')->default('')->unique();
  $table->char('password',128)->nullable()->default(null);
  $table->char('school_id',13)->nullable()->default(null)->index();
  $table->string('user_type')->default('');
  $table->boolean('former_student')->default(0);
  $table->integer('grad_year')->nullable()->default(null);
  $table->string('team_email')->nullable()->default(null);
  $table->char('phone',10)->nullable()->default(null);
  $table->string('gender')->default('');
  $table->string('profile_image',500)->default('');
  $table->string('slack_id')->default('');
  $table->char('signin_pin',64)->default('');
  $table->boolean('admin')->default(0);
  $table->boolean('first_login')->default(1);
  $table->boolean('status')->default(1);
  $table->timestamps();
});

?>

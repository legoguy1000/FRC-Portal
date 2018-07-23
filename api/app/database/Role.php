<?php
include_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
// Create table for storing roles
Capsule::schema()->create('roles', function ($table) {
  $table->char('role_id',13)->primary();
  $table->string('name')->unique();
  $table->string('display_name')->nullable();
  $table->string('description')->nullable();
  $table->timestamps();
});
// Create table for storing permissions
Capsule::schema()->create('permissions', function ($table) {
    $table->char('permission_id',13)->primary();
    $table->string('name')->unique();
    $table->string('display_name')->nullable();
    $table->string('description')->nullable();
    $table->timestamps();
});
// Create table for associating roles to users and teams (Many To Many Polymorphic)
Capsule::schema()->create('role_user', function ($table) {
    $table->string('role_id');
    $table->string('user_id');

    $table->primary(['user_id', 'role_id']);
});
// Create table for associating permissions to roles (Many-to-Many)
Capsule::schema()->create('permission_role', function ($table) {
    $table->string('permission_id');
    $table->string('role_id');

    $table->primary(['permission_id', 'role_id']);
});
// Create table for associating permissions to users (Many To Many Polymorphic)
/*
Capsule::schema()->create('permission_user', function ($table) {
    $table->string('permission_id');
    $table->string('user_id');

    $table->foreign('permission_id')->references('id')->on('permissions')->onUpdate('cascade')->onDelete('cascade');
    $table->unique(['user_id', 'permission_id']);
});
*/








?>

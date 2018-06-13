<?php

require_once "../includes.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('users_user_categories', function ($table) {
  $table->char('cat_id',13)->index();
  $table->char('user_id',13)->index();

  $table->foreign('cat_id')->references('cat_id')->on('user_categories')->onDelete('cascade')->onUpdate('cascade');
  $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
});

?>

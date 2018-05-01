<?php

require "../bootstrap.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('users', function ($table) {
       $table->increments('id');
       $table->string('name');
       $table->string('email')->unique();
       $table->rememberToken();
       $table->timestamps();
   });

?>

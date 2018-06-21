<?php

include_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('oauth_ids', function ($table) {
       $table->char('auth_id',13)->primary();
       $table->char('user_id',13)->index();
       $table->string('oauth_id');
       $table->string('oauth_provider');
       $table->string('oauth_user');
       $table->timestamps();

   });

?>

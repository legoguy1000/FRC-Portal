<?php

require "../includes.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('annual_requirements', function ($table) {
       $table->char('req_id',13)->primary();
       $table->char('user_id',13)->index();
       $table->char('season_id',13)->index();
       $table->boolean('join_team')->default(0);
       $table->boolean('stims')->default(0);
       $table->boolean('dues')->default(0);
       $table->timestamps();

       $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
       $table->foreign('season_id')->references('season_id')->on('seasons')->onDelete('cascade')->onUpdate('cascade');
   });

?>

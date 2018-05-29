<?php

require_once "../includes.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('schools', function ($table) {
       $table->char('school_id',13)->primary();
       $table->string('school_name');
       $table->string('abv');
       $table->string('logo_url',500);
       $table->timestamps();
   });

?>

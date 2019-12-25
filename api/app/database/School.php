<?php

require_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('schools', function ($table) {
       $table->char('school_id',13)->primary();
       $table->string('school_name')->default('');
       $table->string('abv')->default('');
       $table->string('logo_url',500)->default('');
       $table->timestamps();
   });

?>

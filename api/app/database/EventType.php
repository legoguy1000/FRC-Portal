<?php

require_once "../includes.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('event_types', function ($table) {
       $table->char('type_id',13)->primary();
       $table->string('type')->index();
       $table->text('description');
       $table->timestamps();
   });

?>

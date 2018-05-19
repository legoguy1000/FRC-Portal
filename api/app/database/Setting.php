<?php

require "../includes.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('settings', function ($table) {
       $table->char('setting_id',13)->primary();
       $table->string('section');
       $table->string('setting');
       $table->text('value');
       $table->string('type');
   });

?>

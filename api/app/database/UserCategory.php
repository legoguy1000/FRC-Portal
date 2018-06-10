<?php

require_once "../includes.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('user_categories', function ($table) {
       $table->char('cat_id',13)->primary();
       $table->string('name');
       $table->text('description');
       $table->timestamps();
});

?>

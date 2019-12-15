<?php

require_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('user_credentials', function ($table) {
     $table->char('cred_id',13)->primary();
     $table->text('credential_id')->unique();
     $table->text('public_key');
     $table->text('user_handle');
     $table->char('user_id',13)->nullable()->default(null);
     $table->timestamps();
});

?>

<?php

require "../includes.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('users', function ($table) {
       $table->char('user_id',13)->primary();
       $table->string('fname');
       $table->string('lname');
       $table->string('email');
       $table->string('password')->nullable()->default(null);
       $table->char('school_id',13)->nullable()->default(null)->index();
       $table->string('user_type');
       $table->boolean('former_student')->default(0);
       $table->integer('grad_year')->nullable()->default(null);
       $table->string('team_email');
       $table->char('phone',10)->nullable()->default(null);
       $table->string('gender');
       $table->string('profile_image',500);
       $table->string('slack_id');
       $table->string('signin_pin');
       $table->boolean('admin')->default(0);
       $table->boolean('first_login')->default(1);
       $table->boolean('status')->default(1);
       $table->timestamps();

       $table->foreign('school_id')->references('school_id')->on('schools')->onDelete('set null')->onUpdate('cascade');
   });

?>

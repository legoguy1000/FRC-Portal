<?php

require_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('users', function ($table) {
  $table->char('user_id',13)->primary();
  $table->string('fname');
  $table->string('lname');
  $table->string('email')->unique();
  $table->char('password',128)->nullable()->default(null);
  $table->char('school_id',13)->nullable()->default(null)->index();
  $table->string('user_type');
  $table->boolean('former_student')->default(0);
  $table->integer('grad_year')->nullable()->default(null);
  $table->string('team_email');
  $table->char('phone',10)->nullable()->default(null);
  $table->string('gender');
  $table->string('profile_image',500);
  $table->string('slack_id');
  $table->char('signin_pin',64);
  $table->boolean('admin')->default(0);
  $table->boolean('first_login')->default(1);
  $table->boolean('status')->default(1);
  $table->timestamps();
});
//create Admin Account
$email = 'admin@example.org';
$password = bin2hex(openssl_random_pseudo_bytes(4));
$user = FrcPortal\User::create([
  'fname' => 'admin',
  'lname' => 'admin',
  'email' => $email,
  'password' => hash('sha512',$password),
  'user_type' => 'Mentor',
  'admin' => true,
]);
echo 'Admin Account Created:\n';
echo 'Email: '.$email.'\n';
echo 'Password: '.$password.'\n\n';
?>

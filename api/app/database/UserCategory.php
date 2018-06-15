<?php

require_once "../includes.php";

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('user_categories', function ($table) {
       $table->char('cat_id',13)->primary();
       $table->string('name');
       $table->text('description');
       $table->string('type');
       $table->boolean('system')->default(0);
});
$cat = FrcPortal\UserCategory::updateOrCreate(['name' => 'Mentor', 'type' => 'user_type', 'system' => true], ['description' => 'All team mentors']);
$cat = FrcPortal\UserCategory::updateOrCreate(['name' => 'Student', 'type' => 'user_type', 'system' => true], ['description' => 'All team students']);
$cat = FrcPortal\UserCategory::updateOrCreate(['name' => 'Parent', 'type' => 'user_type', 'system' => true], ['description' => 'All team parents']);
$cat = FrcPortal\UserCategory::updateOrCreate(['name' => 'Alumni', 'type' => 'user_type', 'system' => true], ['description' => 'All team alumni']);

?>

<?php
include('includes.php');
use Illuminate\Database\Capsule\Manager as Capsule;
$version = VERSION;

shell_exec("composer install");
shell_exec("composer dump-autoload");
shell_exec("database/_CreateDatabasse.php");


?>

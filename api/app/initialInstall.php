<?php
include_once('includes.php');
use Illuminate\Database\Capsule\Manager as Capsule;
$version = VERSION;

shell_exec("composer install");
shell_exec("composer dump-autoload");
//shell_exec("php database/_CreateDatabase.php");
include_once('database/_CreateDatabase.php');
//shell_exec("mkdir secured");
//shell_exec("cp config.example.ini secured/config.ini");


?>

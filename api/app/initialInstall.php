<?php
//require_once(__DIR__.'/includes.php');
//use Illuminate\Database\Capsule\Manager as Capsule;
//$version = VERSION;

shell_exec("composer install");
shell_exec("composer dump-autoload");

require_once('database/_CreateDatabase.php');


?>

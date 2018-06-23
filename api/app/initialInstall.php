<?php
include_once(__DIR__.'/includes.php');
use Illuminate\Database\Capsule\Manager as Capsule;
$version = VERSION;

shell_exec("composer install");
shell_exec("composer dump-autoload");

/*
$secured = __DIR__.'/secured/';
if (!file_exists($secured)) {
    mkdir($secured,0777,true);
}
$config = __DIR__.'/config.example.ini';
if (file_exists($config) && file_exists($config) ) {
    copy($config, $secured.'/config.ini');
}
*/

include_once('database/_CreateDatabase.php');
include_once('database/_CreateForeignKeys.php');

//shell_exec("mkdir secured");
//shell_exec("cp config.example.ini secured/config.ini");


?>

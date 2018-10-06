<?php
require_once(__DIR__.'/includes.php');
//use Illuminate\Database\Capsule\Manager as Capsule;
//$version = VERSION;

$iniData = array();
$line = clinput($question = 'This file will install and configure FRC Portal v'.VERSION.'.  Type "yes" to continue: ', $required = true);
if($line != 'yes'){
    echo "Aborting!\n";
    exit;
}
$db_data = array();
$question = "Please input the MYSQL DB server (hostname or ip): ";
$db_data['db_host'] = clinput($question, $required = true);

$question =  "Please input the MYSQL DB user: ";
$db_data['db_user'] = clinput($question, $required = true);

$question =  "Please input the MYSQL DB password: ";
$db_data['db_pass'] = clinput($question, $required = true);

$question =  "Please input the MYSQL DB name: ";
$db_data['db_name'] = clinput($question, $required = true);

$iniData['db'] = $db_data;
mkdir("secured");

write_ini_file($iniData, __DIR__.'/secured/data.ini', true);

shell_exec("composer install");
shell_exec("composer dump-autoload");
require_once('database/_CreateDatabase.php');


?>

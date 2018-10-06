<?php
//require_once(__DIR__.'/includes.php');
//use Illuminate\Database\Capsule\Manager as Capsule;
//$version = VERSION;

$iniData = array();
echo "This file will setup the FRC Portal.  Type 'yes' to continue: ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
if(trim($line) != 'yes'){
    echo "Aborting!\n";
    exit;
}
$db_data = array();
echo "Please input the MYSQL DB server (hostname or ip): ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
if(trim($line) != ''){
    echo "No input. Aborting!\n";
    exit;
}
$db_data['db_host'] = trim($line);
echo "Please input the MYSQL DB user: ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
if(trim($line) != ''){
    echo "No input. Aborting!\n";
    exit;
}
$db_data['db_user'] = trim($line);
echo "Please input the MYSQL DB password: ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
if(trim($line) != ''){
    echo "No input. Aborting!\n";
    exit;
}
$db_data['db_pass'] = trim($line);
echo "Please input the MYSQL DB name: ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
if(trim($line) != ''){
    echo "No input. Aborting!\n";
    exit;
}
$db_data['db_name'] = trim($line);

shell_exec("composer install");
shell_exec("composer dump-autoload");

write_ini_file($iniData, __DIR__.'/secured/data.ini', true);
require_once('database/_CreateDatabase.php');


?>

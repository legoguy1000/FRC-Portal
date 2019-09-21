<?php
require_once(__DIR__.'/includes.php');
require_once(__DIR__.'/version.php');

$iniData = array();
$question = 'This file will install and configure FRC Portal v'.VERSION.'.  Type "yes/y" to continue: ';
$line = clinput($question, $required = true);
if($line != 'yes' && $line != 'y'){
    echo "Aborting!\n";
    exit;
}
shell_exec("composer install");
shell_exec("composer dump-autoload");

$db_data = array();
if(file_exists(__DIR__.'/secured/config.ini')) {
  $db_data = parse_ini_file(__DIR__.'/secured/config.ini');
}

if($db_data['db_host'] == '') {
  $question = "Please input the MYSQL DB server (hostname or ip): ";
  $db_data['db_host'] = clinput($question, $required = true);
}
if($db_data['db_user'] == '') {
  $question =  "Please input the MYSQL DB user: ";
  $db_data['db_user'] = clinput($question, $required = true);
}
if($db_data['db_pass'] == '') {
  $question =  "Please input the MYSQL DB password: ";
  $db_data['db_pass'] = clinput($question, $required = true);
}
if($db_data['db_name'] == '') {
  $question =  "Please input the MYSQL DB name: ";
  $db_data['db_name'] = clinput($question, $required = true);
}
$iniData['db'] = $db_data;

//create Admin Account
$admin_data = array();
$admin_data['admin_user'] = 'admin';
$password = bin2hex(openssl_random_pseudo_bytes(10));
$admin_data['admin_pass'] = hash('sha512',$password);
$iniData['admin'] = $admin_data;
write_ini_file($iniData, __DIR__.'/secured/config.ini', true);

if (!file_exists(__DIR__.'/../../favicons')) {
  mkdir(__DIR__.'/../../favicons');
}
require_once(__DIR__.'/database/_CreateDatabase.php');

echo 'Local Admin Account Created:' . PHP_EOL;
echo 'User: admin' . PHP_EOL;
echo 'Password: '.$password . PHP_EOL . PHP_EOL;
echo  'Your portal has beeen installed.
       Please go to the site and login using the local admin account created to configure and customize the site for your team.
       You can find the settings under "Admin" -> "Site Settings".'.PHP_EOL.PHP_EOL;
?>

<?php
$root = __DIR__;
require_once($root.'/functions/general_functions.php');
require_once($root.'/version.php');

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
$question = "Please input the MYSQL DB server (hostname or ip): ";
$db_data['db_host'] = clinput($question, $required = true);

$question =  "Please input the MYSQL DB user: ";
$db_data['db_user'] = clinput($question, $required = true);

$question =  "Please input the MYSQL DB password: ";
$db_data['db_pass'] = clinput($question, $required = true);

$question =  "Please input the MYSQL DB name: ";
$db_data['db_name'] = clinput($question, $required = true);

$iniData['db'] = $db_data;
write_ini_file($iniData, __DIR__.'/secured/config.ini', true);

if (!file_exists('favicons')) {
  mkdir('../favicons');
}
require_once(__DIR__.'/includes.php');
require_once('database/_CreateDatabase.php');
//create Admin Account
$email = 'admin@local.local';
$password = bin2hex(openssl_random_pseudo_bytes(4));
$user = FrcPortal\User::create([
  'fname' => 'Local',
  'lname' => 'Admin',
  'email' => $email,
  'password' => hash('sha512',$password),
  'user_type' => 'Mentor',
  'admin' => true,
]);
echo 'Local Admin Account Created:' . PHP_EOL;
echo 'Email: '.$email . PHP_EOL;
echo 'Password: '.$password . PHP_EOL . PHP_EOL;
echo  'Your portal has beeen installed.
       Please go to the site and login using the local admin account created to configure and customize the site for your team.
       You can find the settings under "Admin" -> "Site Settings".'.PHP_EOL.PHP_EOL;
?>

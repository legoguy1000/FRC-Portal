<?php
$root = __DIR__;
require_once($root.'/functions/general_functions.php');
require_once($root.'/version.php');
shell_exec("composer install");
shell_exec("composer dump-autoload");
//use Illuminate\Database\Capsule\Manager as Capsule;
//$version = VERSION;

$iniData = array();
$question = 'This file will install and configure FRC Portal v'.VERSION.'.  Type "yes" to continue: ';
$line = clinput($question, $required = true);
if($line != 'yes' || $line != 'y'){
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
if (!file_exists('secured')) {
  mkdir('secured');
}
write_ini_file($iniData, __DIR__.'/secured/config.ini', true);



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
echo 'Admin Account Created:' . PHP_EOL;
echo 'Email: '.$email . PHP_EOL;
echo 'Password: '.$password . PHP_EOL . PHP_EOL;

?>

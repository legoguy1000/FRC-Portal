<?php
//require_once(__DIR__.'/includes.php');
require_once(__DIR__.'/functions/general_functions.php');
require_once(__DIR__.'/version.php');

$iniData = array();
$version = getVersion();
$question = 'This file will install and configure FRC Portal v'.$version.'.  Type "yes/y" to continue: ';
$line = clinput($question, $required = true);
if($line != 'yes' && $line != 'y'){
    echo "Aborting!\n";
    exit;
}
shell_exec("composer install");
shell_exec("composer dump-autoload");


$iniData = array();
if(file_exists(__DIR__.'/secured/config.ini')) {
  $iniData = parse_ini_file(__DIR__.'/secured/config.ini', true);
}

$db_exists = array_key_exists('db', $iniData);
if(!$db_exists || $iniData['db']['db_host'] == '') {
  $question = "Please input the MYSQL DB server (hostname or ip): ";
  $iniData['db']['db_host'] = clinput($question, $required = true);
}
if(!$db_exists || $iniData['db']['db_user'] == '') {
  $question =  "Please input the MYSQL DB user: ";
  $iniData['db']['db_user'] = clinput($question, $required = true);
}
if(!$db_exists || $iniData['db']['db_pass'] == '') {
  $question =  "Please input the MYSQL DB password: ";
  $iniData['db']['db_pass'] = clinput($question, $required = true);
}
if(!$db_exists || $iniData['db']['db_name'] == '') {
  $question =  "Please input the MYSQL DB name: ";
  $iniData['db']['db_name'] = clinput($question, $required = true);
}

//create Admin Account
$iniData['admin']['admin_user'] = 'admin';
$password = bin2hex(openssl_random_pseudo_bytes(10));
$iniData['admin']['admin_pass'] = hash('sha512',$password);

//Create AES
$iniData['encryption'] = array();
$iniData['encryption']['encryption_key'] = bin2hex(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
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

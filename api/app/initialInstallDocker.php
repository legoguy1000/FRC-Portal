<?php
require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/functions/getConfigFile.php');
require_once(__DIR__.'/functions/general_functions.php');
require_once(__DIR__.'/version.php');

$iniData = array();
$version = VERSION;

$iniData = array();
if(file_exists(__DIR__.'/secured/config.ini')) {
  $iniData = parse_ini_file(__DIR__.'/secured/config.ini', true);
}

//create Admin Account
if(!array_key_exists('admin', $iniData)) {
  $iniData['admin'] = array(
    'admin_user' => '',
    'admin_pass' => ''
  );
}
if(!array_key_exists('admin_user', $iniData['admin']) || $iniData['admin']['admin_user'] == '') {
  $iniData['admin']['admin_user'] = 'admin';
}
$password = '';
if(!array_key_exists('admin_pass', $iniData['admin']) || $iniData['admin']['admin_pass'] == '') {
  $password = bin2hex(openssl_random_pseudo_bytes(10));
  $iniData['admin']['admin_pass'] = hash('sha512',$password);
}

//Create AES
if(!array_key_exists('encryption', $iniData)) {
  $iniData['encryption'] = array(
    'encryption_key' => '',
  );
}
if(!array_key_exists('encryption_key', $iniData['encryption']) || $iniData['encryption']['encryption_key'] == '') {
  $iniData['encryption']['encryption_key'] = bin2hex(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
}
write_ini_file($iniData, __DIR__.'/secured/config.ini', true);

if (!file_exists(__DIR__.'/../../favicons')) {
  mkdir(__DIR__.'/../../favicons');
}

require_once(__DIR__.'/database/_CreateDatabase.php');

echo 'Local Admin Account Created:' . PHP_EOL;
echo 'User: '.$iniData['admin']['admin_user'] . PHP_EOL;
echo 'Password: '.$password . PHP_EOL . PHP_EOL;
echo  'Your portal has beeen installed.
       Please go to the site and login using the local admin account created to configure and customize the site for your team.
       You can find the settings under "Admin" -> "Site Settings".'.PHP_EOL.PHP_EOL;
?>

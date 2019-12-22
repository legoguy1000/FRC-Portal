<?php
require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/functions/general_functions.php');
require_once(__DIR__.'/version.php');
use Illuminate\Database\Capsule\Manager as Capsule;
use FrcPortal\Utilities\IniConfig;

$iniData = array();
if(file_exists(__DIR__.'/secured/config.ini')) {
  $iniData = parse_ini_file(__DIR__.'/secured/config.ini', true);
}

$iniData['db']['db_host'] = getenv('DB_HOST');
$iniData['db']['db_user'] = getenv('DB_USER');
$iniData['db']['db_pass'] = getenv('DB_PASS');
$iniData['db']['db_name'] = getenv('DB_NAME');

write_ini_file($iniData, __DIR__.'/secured/config.ini', true);

try {
  while(true) {
    $mysqli = @new mysqli(IniConfig::iniDataProperty('db_host'), IniConfig::iniDataProperty('db_user'), IniConfig::iniDataProperty('db_pass'), IniConfig::iniDataProperty('db_name'));
    if (!$mysqli->connect_errno) {
      break;
    }
    sleep(2);
  }
  $tables = array();
  if ($result = $mysqli->query("SHOW TABLES FROM ".IniConfig::iniDataProperty('db_name').";")) {
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $tables[] = $row[0];
    }
  }
  if(count($tables) > 0) {
    require_once('postUpgrade.php');
  } else {
    throw new Exception('DB exists');
  }
} catch (Exception $e) {
  require_once('initialInstallDocker.php');
}
if(Capsule::schema()->hasTable('settings')) {
  if(getenv('ENV_URL') != null && getenv('ENV_URL') != '') {
    $setting = FrcPortal\Setting::firstOrUpdate(['section' => 'team', 'setting' => 'env_url'], ['value' => getenv('ENV_URL')]);
  }
  if(getenv('TEAM_NAME') != null && getenv('TEAM_NAME') != '') {
    $setting = FrcPortal\Setting::firstOrUpdate(['section' => 'team', 'setting' => 'team_name'], ['value' => getenv('TEAM_NAME')]);
  }
  if(getenv('TEAM_NUMBER') != null && getenv('TEAM_NUMBER') != '') {
    $setting = FrcPortal\Setting::firstOrUpdate(['section' => 'team', 'setting' => 'team_number'], ['value' => getenv('TEAM_NUMBER')]);
  }
}

?>

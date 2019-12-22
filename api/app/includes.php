<?php
use Illuminate\Database\Capsule\Manager as Capsule;
use FrcPortal\Utilities\IniConfig;

ini_set("error_reporting", E_ALL);
ini_set("expose_php", false);



$root = __DIR__;
require_once($root.'/version.php');
require_once($root.'/vendor/autoload.php');

IniConfig::parseIniFile();
$capsule = new Capsule;
$capsule->addConnection(array("driver" => "mysql", "host" =>IniConfig::iniDataProperty('db_host'), "database" => IniConfig::iniDataProperty('db_name'), "username" => IniConfig::iniDataProperty('db_user'), "password" => IniConfig::iniDataProperty('db_pass')));
$capsule->setAsGlobal();
$capsule->bootEloquent();

$timeZone = FrcPortal\Setting::where('setting','timezone')->first();
$tz = Capsule::schema()->hasTable('settings') ? $timeZone->value : null;
$time_zone = !is_null($tz) && $tz != '' ? $tz:date_default_timezone_get();
date_default_timezone_set($time_zone);



require_once($root.'/functions/general_functions.php');
require_once($root.'/functions/season_functions.php');
require_once($root.'/functions/event_functions.php');
require_once($root.'/functions/user_functions.php');
require_once($root.'/functions/notification_functions.php');
require_once($root.'/functions/report_functions.php');
require_once($root.'/functions/time_functions.php');


?>

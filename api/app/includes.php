<?php
use Illuminate\Database\Capsule\Manager as Capsule;
ini_set("error_reporting", E_ALL);
ini_set("expose_php", false);



$root = __DIR__;
require_once($root.'/version.php');
require_once($root.'/vendor/autoload.php');
require_once($root.'/functions/getConfigFile.php');

$capsule = new Capsule;
$capsule->addConnection(array("driver" => "mysql", "host" =>getIniProp('db_host'), "database" => getIniProp('db_name'), "username" => getIniProp('db_user'), "password" => getIniProp('db_pass')));
$capsule->setAsGlobal();
$capsule->bootEloquent();

$tz = Capsule::schema()->hasTable('settings') ? getSettingsProp('timezone') : null;
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

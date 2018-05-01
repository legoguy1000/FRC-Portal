<?php
ini_set("error_reporting", E_ALL);
ini_set("expose_php", false);
date_default_timezone_set('America/New_York');

$root = '/home/team2363_admin/portal.team2363.org';
require $root.'/site/includes/vendor/autoload.php';
include($root.'/site/includes/functions/getConfigFile.php');
/*
include($root.'/site/includes/functions/db_functions.php');
include($root.'/site/includes/functions/user_functions.php');
include($root.'/site/includes/functions/general_functions.php');
include($root.'/site/includes/functions/school_functions.php');
include($root.'/site/includes/functions/report_functions.php');
include($root.'/site/includes/functions/season_functions.php');
include($root.'/site/includes/functions/event_functions.php');
include($root.'/site/includes/functions/time_functions.php');
include($root.'/site/includes/functions/email_functions.php');
*/
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection(array("driver" => "mysql", "host" =>getIniProp('db_host'), "database" => getIniProp('db_name').'_test', "username" => getIniProp('db_user'), "password" => getIniProp('db_pass')));

$capsule->setAsGlobal();
$capsule->bootEloquent();

?>

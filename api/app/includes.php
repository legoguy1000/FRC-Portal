<?php
ini_set("error_reporting", E_ALL);
ini_set("expose_php", false);
date_default_timezone_set('America/New_York');

$root = __DIR__;
require $root.'/../site/includes/vendor/autoload.php';
include($root.'/functions/getConfigFile.php');
include($root.'/functions/season_functions.php');
include($root.'/functions/event_functions.php');
include($root.'/functions/user_functions.php');
include($root.'/functions/notification_functions.php');


?>

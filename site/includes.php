<?php
ini_set("error_reporting", E_ALL);
ini_set("expose_php", false);
date_default_timezone_set('America/New_York');

//$root = $_SERVER['DOCUMENT_ROOT'];

require __DIR__.'/includes/vendor/autoload.php';
include(__DIR__.'/includes/functions/getConfigFile.php');


//
include(__DIR__.'/includes/functions/db_functions.php');
include(__DIR__.'/includes/functions/user_functions.php');
include(__DIR__.'/includes/functions/general_functions.php');
include(__DIR__.'/includes/functions/school_functions.php');
include(__DIR__.'/includes/functions/report_functions.php');
include(__DIR__.'/includes/functions/season_functions.php');
include(__DIR__.'/includes/functions/event_functions.php');
include(__DIR__.'/includes/functions/time_functions.php');
include(__DIR__.'/includes/functions/email_functions.php');


?>

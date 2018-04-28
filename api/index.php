<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

ini_set("error_reporting", E_ALL);
ini_set("expose_php", false);
date_default_timezone_set('America/New_York');

$root = '/home/team2363_admin/portal.team2363.org';
require $root.'/site/includes/vendor/autoload.php';
include($root.'/site/includes/functions/getConfigFile.php');
//
include($root.'/site/includes/functions/db_functions.php');
include($root.'/site/includes/functions/user_functions.php');
include($root.'/site/includes/functions/general_functions.php');
include($root.'/site/includes/functions/school_functions.php');
include($root.'/site/includes/functions/report_functions.php');
include($root.'/site/includes/functions/season_functions.php');
include($root.'/site/includes/functions/event_functions.php');
include($root.'/site/includes/functions/time_functions.php');
include($root.'/site/includes/functions/email_functions.php');


$app = new \Slim\App;
$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");

    return $response;
});
$app->run();

?>

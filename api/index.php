<?php
include('app/includes.php');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$config = array();
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['driver']   = 'mysql'; //your mysql server
$config['db']['host']   = getIniProp('db_host'); //your mysql server
$config['db']['user']   = getIniProp('db_user'); //your mysql server username
$config['db']['pass']   = getIniProp('db_pass'); //your mysql server password
$config['db']['dbname'] = getIniProp('db_name'); //the mysql database to use
$config['db']['charset'] = 'utf8';
$config['db']['collation'] = 'utf8_unicode_ci';
$config['db']['prefix'] = '';
 //asdf
$app = new \Slim\App(['settings' => $config]);
$app->add(new Tuupola\Middleware\JwtAuthentication([
    "secret" => getSettingsProp('jwt_key'),
    "path" => ['/users', '/seasons', '/events', '/schools','/hours/missingHoursRequests','/hours/signIn/records','/settings'],
    "passthrough" => ['/auth','/reports','/slack','/hours/signIn','/config'],
]));
$container = $app->getContainer();
/* $container['db'] = function ($c) {
    $dbConfig = $c['settings']['db'];

    $db = mysqli_init();
      // Try and connect to the database, if a connection has not been established yet
  	if (!$db) {
  		//die('mysqli_init failed');
  	}
  	if (!$db->options(MYSQLI_INIT_COMMAND, 'SET time_zone = "America/New_York"')) {
  		//die('Setting MYSQLI_INIT_COMMAND failed');
  	}
  	if (!$db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
  		//die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
  	}
    if (!$db->real_connect($dbConfig['host'], $dbConfig['user'], $dbConfig['pass'], $dbConfig['dbname'])) {
      return $db->connect_error;
    }
    return $db;
};
// Service factory for the ORM
$container['db'] = function ($container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};*/
$app->get('/version', function (Request $request, Response $response, array $args) {
    $responseArr = array(
      'version' => '2.3.5'
    );
    $response = $response->withJson($responseArr);
    return $response;
});
$app->get('/config', function ($request, $response, $args) {
  $configArr = array(
    'google_oauth_client_id',
    'facebook_oauth_client_id',
    'microsoft_oauth_client_id',
    'team_name',
    'team_number',
    'team_logo_url',
    'google_calendar_id',
    'slack_team_id',
    'slack_url',
  );
  $settings = FrcPortal\Setting::all();

  $responseStr = 'angular.module("FrcPortal")';
  foreach($settings as $set) {
    if(in_array($set->setting,$configArr)) {
      $responseStr .= '.constant("'.$set->setting.'", "'.$set->value.'")';
    }
  }
  $responseStr .= ';';
  $response->getBody()->write($responseStr);
  $response = $response->withHeader('Content-type', 'application/javascript');
  return $response;
});


include('./app/routes/auth.php');
include('./app/routes/seasons.php');
include('./app/routes/hours.php');
include('./app/routes/users.php');
include('./app/routes/events.php');
include('./app/routes/reports.php');
include('./app/routes/schools.php');
include('./app/routes/slack.php');
include('./app/routes/settings.php');

$app->run();

?>

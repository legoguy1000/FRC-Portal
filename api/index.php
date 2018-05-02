<?php
ini_set("error_reporting", E_ALL);
ini_set("expose_php", false);
date_default_timezone_set('America/New_York');

$root = '/home/team2363_admin/portal.team2363.org';
require $root.'/site/includes/vendor/autoload.php';
include($root.'/site/includes/functions/getConfigFile.php');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$config = array();
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['driver']   = 'mysql'; //your mysql server
$config['db']['host']   = getIniProp('db_host'); //your mysql server
$config['db']['user']   = getIniProp('db_user'); //your mysql server username
$config['db']['pass']   = getIniProp('db_pass'); //your mysql server password
$config['db']['dbname'] = getIniProp('db_name').'_test'; //the mysql database to use
$config['db']['charset'] = 'utf8';
$config['db']['collation'] = 'utf8_unicode_ci';
$config['db']['prefix'] = '';

$app = new \Slim\App(['settings' => $config]);
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
use Illuminate\Database\Capsule\Manager as Capsule;
$capsule = new Capsule;
$capsule->addConnection(array("driver" => "mysql", "host" =>getIniProp('db_host'), "database" => getIniProp('db_name').'_test', "username" => getIniProp('db_user'), "password" => getIniProp('db_pass')));
$capsule->setAsGlobal();
$capsule->bootEloquent();
$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];

    $db = $this->db;
    $result = $db->query('SELECT * FROM users WHERE fname = "'.$name.'" LIMIT 1');
    $row = $result->fetch_assoc();
    $response->getBody()->write(json_encode($row));
    return $response;
});
$app->group('/users', function () {
  $this->get('', function ($request, $response, $args) {

    $response->getBody()->write(json_encode('Get all Users '));
    return $response;
  });
  $this->post('', function ($request, $response, $args) {
    $user = FrcPortal\User::Create(['user_id'=>uniqid(),'fname' => "Ahmed", 'lname' => "Ahmed", 'email' => "ahmed.khan@lbs.com"]);
    $response->getBody()->write(json_encode('Add new user'));
    return $response;
  });
  $this->get('/{user_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $user_id = $args['user_id'];
    $response->getBody()->write(json_encode('Get User '.$user_id));
    return $response;
  });
  $this->put('/{user_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $user_id = $args['user_id'];
    $response->getBody()->write(json_encode('Update User '.$user_id));
    return $response;
  });
  $this->delete('/{user_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $user_id = $args['user_id'];
    $response->getBody()->write(json_encode('Delete User '.$user_id));
    return $response;
  });
});
$app->group('/seasons', function () {
  $this->get('', function ($request, $response, $args) {

    $response->getBody()->write(json_encode('Get all Seasons '));
    return $response;
  });
  $this->get('/{season_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $season_id = $args['season_id'];
    $response->getBody()->write(json_encode('Get Season '.$season_id));
    return $response;
  });
  $this->post('', function ($request, $response, $args) {

    $response->getBody()->write(json_encode('New Season'));
    return $response;
  });
  $this->put('/{season_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $season_id = $args['season_id'];
    $response->getBody()->write(json_encode('Update Season '.$season_id));
    return $response;
  });
  $this->delete('/{season_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $season_id = $args['season_id'];
    $response->getBody()->write(json_encode('Delete Season '.$season_id));
    return $response;
  });
});
$app->group('/events', function () {
  $this->get('', function ($request, $response, $args) {

    $response->getBody()->write(json_encode('Get all Events '));
    return $response;
  });
  $this->get('/{event_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $event_id = $args['event_id'];
    $response->getBody()->write(json_encode('Get Event '.$event_id));
    return $response;
  });
  $this->post('', function ($request, $response, $args) {

    $response->getBody()->write(json_encode('New Event'));
    return $response;
  });
  $this->put('/{event_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $event_id = $args['event_id'];
    $response->getBody()->write(json_encode('Update Event '.$event_id));
    return $response;
  });
  $this->delete('/{event_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $event_id = $args['event_id'];
    $response->getBody()->write(json_encode('Delete Event '.$event_id));
    return $response;
  });
});
$app->run();

?>

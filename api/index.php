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



$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = getIniProp('db_host'); //your mysql server
$config['db']['user']   = getIniProp('db_user'); //your mysql server username
$config['db']['pass']   = getIniProp('db_pass'); //your mysql server password
$config['db']['dbname'] = getIniProp('db_name'); //the mysql database to use

$app = new \Slim\App(['settings' => $config]);
$container = $app->getContainer();
$container['db'] = function ($c) {
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
/*    $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;*/
};

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];

    $db = $this->db;
    $result = $db->query('SELECT * FROM users WHERE fname = "'.$name.'" LIMIT 1');
    $row = $result->fetch_assoc();
    $response->getBody()->write(json_encode($row));
    return $response;
});
$app->run();

?>

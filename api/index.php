<?php
require_once('app/includes.php');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;
use FrcPortal\Utilities\Auth;
use FrcPortal\Utilities\IniConfig;


// Run app
$app = (new FrcPortal\App())->get();
$app->run();

?>

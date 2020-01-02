<?php
use Illuminate\Database\Capsule\Manager as DB;
use Slim\Routing\RouteCollectorProxy;

$app->group('/public', function(RouteCollectorProxy $group) {
  $group->get('/timezones', function ($request, $response, $args) {
    $tz = timezone_identifiers_list();
    $response = $response->withJson($tz);
    return $response;
  })->setName('Get Timezones');
});

















?>

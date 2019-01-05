<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/public', function () {
  $this->get('/timezones', function ($request, $response, $args) {
    $tz = timezone_identifiers_list();
    $response = $response->withJson($tz);
    return $response;
  })->setName('Get Timezones');
});

















?>

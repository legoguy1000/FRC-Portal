<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/settings', function () {
  $this->get('', function ($request, $response, $args) {


    $response = $response->withJson($data);
    return $response;
  });
  $this->group('/{setting_id:[a-z0-9]{13}}', function () {
    $this->get('', function ($request, $response, $args) {


      $response = $response->withJson($data);
      return $response;
    });
    $this->put('', function ($request, $response, $args) {

      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->delete('', function ($request, $response, $args) {

      $response = $response->withJson($responseArr);
      return $response;
    });
  });
  $this->post('', function ($request, $response, $args) {

    $response = $response->withJson($responseArr);
    return $response;
  });
});

















?>

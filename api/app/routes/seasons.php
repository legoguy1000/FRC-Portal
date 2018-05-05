<?php
$app->group('/seasons', function () {
  $this->get('', function ($request, $response, $args) {
    $seasons = FrcPortal\Season::all();
    $response = $response->withJson($seasons);
    return $response;
  });
  $this->get('/{season_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $season_id = $args['season_id'];
    $season = FrcPortal\Season::find($season_id);
    $response = $response->withJson($season);
    return $response;
  });
  $this->group('/{year:[0-9]{4}}', function ($request, $response, $args) {
    $this->get('/topHourUsers', function ($request, $response, $args) {
      $year = $args['year'];
      $season = FrcPortal\Season::where('year',$year)->get();
      $users = FrcPortal\AnnualRequirement::with('users')->where('season_id',$season[0]->season_id)->get();
      $users = $users->sortByDesc('total_hours')->values()->slice(0,5);
      $response = $response->withJson($users);
      return $response;
    });
  });
  $this->post('', function ($request, $response, $args) {


  });
  $this->put('/{season_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $season_id = $args['season_id'];

    return $response;
  });
  $this->delete('/{season_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $season_id = $args['season_id'];

    return $response;
  });
});

















?>

<?php
$app->group('/meeting_hours', function () {
  $this->get('/signInList', function ($request, $response, $args) {
    $season = FrcPortal\Season::where('year',2018)->get();
    $users = FrcPortal\User::with(['annual_requirements' => function ($query) {
      $query->where('season_id', $season[0]->season_id); // fields from comments table,
    }, 'last_sign_in'])->where('status','1')->get();
    $response = $response->withJson($season[0]);
    return $response;
  });
});

















?>

<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/public', function () {
  $this->group('/events/{event_id:[a-z0-9]{13}}', function () {
    $this->get('', function ($request, $response, $args) {
      $event_id = $args['event_id'];
      $reqsBool = $request->getParam('requirements') !== null && $request->getParam('requirements')==true ? true:false;
      $event = FrcPortal\Event::with('poc')->find($event_id);
      if($reqsBool) {
        $event->users = FrcPortal\User::with(['event_requirements' => function ($query) use ($event_id) {
                        		$query->where('event_id','=',$event_id);
                          }])->get();
      }
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $event);
      $response = $response->withJson($responseArr);
      return $response;
    });
  });
});

















?>

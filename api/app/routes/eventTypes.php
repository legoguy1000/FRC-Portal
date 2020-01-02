<?php
use Illuminate\Database\Capsule\Manager as DB;
use Slim\Routing\RouteCollectorProxy;

$app->group('/eventTypes', function(RouteCollectorProxy $group) {
  $group->get('', function ($request, $response, $args) {
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

    $users = FrcPortal\EventType::all();
    $responseArr = array(
      'status' => true,
      'msg' => '',
      'data' => $users
    );
    $response = $response->withJson($responseArr);
    return $response;
  })->setName('Get Event Types');
  $group->post('', function ($request, $response, $args) {
    $userId = FrcPortal\Utilities\Auth::user()->user_id;
    $formData = $request->getParsedBody();
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    if(!FrcPortal\Utilities\Auth::isAdmin()) {
      return unauthorizedResponse($response);
    }

    if(empty($formData['type'])) {
      $responseArr['msg'] = 'Type cannot be blank';
      $response = $response->withJson($responseArr,400);
      return $response;
    }

    $type = new FrcPortal\EventType();
    $type->type = $formData['type'];
    $type->description = !empty($formData['description']) ? $formData['description']:'';
    if($type->save()) {
      $responseArr['data'] = $type;
      $responseArr['msg'] = 'New event type added';
      $responseArr['status'] = true;
    }
    $response = $response->withJson($responseArr);
    return $response;
  })->setName('Add Event Type');
  $group->group('/{type_id:[a-z0-9]{13}}', function(RouteCollectorProxy $group) {
    $group->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $type_id = $args['type_id'];

      if(empty($formData['type'])) {
        $responseArr['msg'] = 'Type cannot be blank';
        $response = $response->withJson($responseArr,400);
        return $response;
      }

      $type = FrcPortal\EventType::find($type_id);
      if(!is_null($type_id)) {
        $type->type = $formData['type'];
        $type->description = !empty($formData['description']) ? $formData['description']:'';
        if($type->save()) {
          $responseArr['data'] = $type;
          $responseArr['msg'] = 'Event type updated';
          $responseArr['status'] = true;
        }
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update Event Types');
    $group->delete('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $type_id = $args['type_id'];

      $type = FrcPortal\EventType::destroy($type_id);
      $responseArr['msg'] = 'Event type deleted';
      $responseArr['status'] = true;

      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Delete Events Types');
  });
});

















?>

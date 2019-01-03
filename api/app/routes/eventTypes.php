<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/eventTypes', function () {
  $this->get('', function ($request, $response, $args) {
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
  $this->post('', function ($request, $response, $args) {
    $userId = FrcPortal\Auth::user()->user_id;
    $formData = $request->getParsedBody();
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    if(!FrcPortal\Auth::isAdmin()) {
      return unauthorizedResponse($response);
    }

    if(!isset($formData['type']) || $formData['type'] == '') {
      $responseArr['msg'] = 'Type cannot be blank';
      $response = $response->withJson($responseArr,400);
      return $response;
    }

    $type = new FrcPortal\EventType();
    $type->type = $formData['type'];
    $type->description = isset($formData['description']) ? $formData['description']:'';
    if($type->save()) {
      $responseArr['data'] = $type;
      $responseArr['msg'] = 'New event type added';
      $responseArr['status'] = true;
    }
    $response = $response->withJson($responseArr);
    return $response;
  })->setName('Add Event Type');
  $this->group('/{type_id:[a-z0-9]{13}}', function () {
    $this->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $type_id = $args['type_id'];

      if(!isset($formData['type']) || $formData['type'] == '') {
        $responseArr['msg'] = 'Type cannot be blank';
        $response = $response->withJson($responseArr,400);
        return $response;
      }

      $type = FrcPortal\EventType::find($type_id);
      if(!is_null($type_id)) {
        $type->type = $formData['type'];
        $type->description = isset($formData['description']) ? $formData['description']:'';
        if($type->save()) {
          $responseArr['data'] = $type;
          $responseArr['msg'] = 'Event type updated';
          $responseArr['status'] = true;
        }
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update Event Types');
    $this->delete('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
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

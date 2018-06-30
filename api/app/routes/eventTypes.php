<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/eventTypes', function () {
  $this->get('', function ($request, $response, $args) {
    $responseArr = array(
      'status' => false,
      'msg' => 'Something went wrong',
      'data' => null
    );
    $users = FrcPortal\EventType::all();
    $responseArr = array(
      'status' => true,
      'msg' => '',
      'data' => $users
    );
    $response = $response->withJson($responseArr);
    return $response;
  });
  $this->post('', function ($request, $response, $args) {
    $userId = FrcPortal\Auth::user()->user_id;
    $formData = $request->getParsedBody();
    $responseArr = array(
      'status' => false,
      'msg' => 'Something went wrong',
      'data' => null
    );
    if(!FrcPortal\Auth::isAdmin()) {
      $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
      $response = $response->withJson($responseArr,403);
      return $response;
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
      $responseArr['data'] = FrcPortal\EventType::all();
      $responseArr['msg'] = 'New event type added';
      $responseArr['status'] = true;
    }
    $response = $response->withJson($responseArr);
    return $response;
  });
  $this->group('/{type_id:[a-z0-9]{13}}', function () {
    $this->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if(!FrcPortal\Auth::isAdmin()) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
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
          $responseArr['data'] = FrcPortal\EventType::all();
          $responseArr['msg'] = 'Event type updated';
          $responseArr['status'] = true;
        }
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->delete('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if(!FrcPortal\Auth::isAdmin()) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }
      $type_id = $args['type_id'];

      $type = FrcPortal\EventType::destroy($type_id);
      $responseArr['data'] = FrcPortal\EventType::all();
      $responseArr['msg'] = 'Event type deleted';
      $responseArr['status'] = true;

      $response = $response->withJson($responseArr);
      return $response;
    });
  });
});

















?>

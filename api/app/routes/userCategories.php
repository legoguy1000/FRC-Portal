<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/userCategories', function () {
  $this->get('', function ($request, $response, $args) {
    $responseArr = array(
      'status' => false,
      'msg' => 'Something went wrong',
      'data' => null
    );
    $cats = FrcPortal\UserCategory::all();
    $responseArr = array(
      'status' => true,
      'msg' => '',
      'data' => $cats
    );
    $response = $response->withJson($responseArr);
    return $response;
  });
  $this->post('', function ($request, $response, $args) {
    $authToken = $request->getAttribute("token");
    $userId = $authToken['data']->user_id;
    $formData = $request->getParsedBody();
    $responseArr = array(
      'status' => false,
      'msg' => 'Something went wrong',
      'data' => null
    );
    if(!checkAdmin($userId)) {
      $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
      $response = $response->withJson($responseArr,403);
      return $response;
    }

    if(!isset($formData['name']) || $formData['name'] == '') {
      $responseArr['msg'] = 'Name cannot be blank';
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(!isset($formData['type']) || $formData['type'] == '') {
      $responseArr['msg'] = 'Type cannot be blank';
      $response = $response->withJson($responseArr,400);
      return $response;
    }

    $cat = new FrcPortal\UserCategory();
    $cat->name = $formData['name'];
    $cat->type = str_replace(' ','_',strtolower($formData['type']));
    $cat->description = isset($formData['description']) ? $formData['description']:'';
    if($cat->save()) {
      $responseArr['data'] = FrcPortal\UserCategory::all();
      $responseArr['msg'] = 'New user category added';
      $responseArr['status'] = true;
    }
    $response = $response->withJson($responseArr);
    return $response;
  });
  $this->group('/{cat_id:[a-z0-9]{13}}', function () {
    $this->put('', function ($request, $response, $args) {
      $authToken = $request->getAttribute("token");
      $userId = $authToken['data']->user_id;
      $cat_id = $args['cat_id'];
      $formData = $request->getParsedBody();
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if(!checkAdmin($userId)) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }

      if(!isset($formData['name']) || $formData['name'] == '') {
        $responseArr['msg'] = 'Name cannot be blank';
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if(!isset($formData['type']) || $formData['type'] == '') {
        $responseArr['msg'] = 'Type cannot be blank';
        $response = $response->withJson($responseArr,400);
        return $response;
      }

      $cat = FrcPortal\UserCategory::find($cat_id);
      if(is_null($cat)) {
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if($cat->system) {
        $responseArr = array('status'=>false, 'msg'=>'Cannot modify built-in categories');
        $response = $response->withJson($responseArr);
        return $response;
      }
      $cat->name = $formData['name'];
      $cat->type = str_replace(' ','_',strtolower($formData['type']));
      $cat->description = isset($formData['description']) ? $formData['description']:'';
      if($cat->save()) {
        $responseArr['data'] = FrcPortal\UserCategory::all();
        $responseArr['msg'] = 'User Category updated';
        $responseArr['status'] = true;
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->delete('', function ($request, $response, $args) {
      $authToken = $request->getAttribute("token");
      $userId = $authToken['data']->user_id;
      $cat_id = $args['cat_id'];
      $formData = $request->getParsedBody();
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if(!checkAdmin($userId)) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }

      $cat = FrcPortal\UserCategory::find($cat_id);
      if(is_null($cat)) {
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if($cat->system) {
        $responseArr = array('status'=>false, 'msg'=>'Cannot delete built-in categories');
        $response = $response->withJson($responseArr);
        return $response;
      }
      $cat->delete();
      $responseArr['data'] = FrcPortal\UserCategory::all();
      $responseArr['msg'] = 'User category deleted';
      $responseArr['status'] = true;

      $response = $response->withJson($responseArr);
      return $response;
    });
  });
});

















?>

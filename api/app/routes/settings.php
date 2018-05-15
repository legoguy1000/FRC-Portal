<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/settings', function () {
  $this->get('', function ($request, $response, $args) {
    $authToken = $request->getAttribute("token");
    $loggedInUser = $authToken['data']->user_id;
    $responseArr = array(
      'status' => false,
      'msg' => 'Something went wrong',
      'data' => null
    );
    if(!checkAdmin($loggedInUser)) {
      $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
      $response = $response->withJson($responseArr,403);
      return $response;
    }
    $settings = FrcPortal\Setting::all();
    $normalArr = array();
    foreach($settings as $set) {
      $normalArr[$set->setting] = $set->value;
    }
    $responseArr['status'] = true;
    $responseArr['msg'] = '';
    $responseArr['data'] = array(
      'raw' => $settings,
      'normalized' => $normalArr
    );
    $response = $response->withJson($responseArr);
    return $response;
  });
  $this->get('/config', function ($request, $response, $args) {
    $configArr = array(
      'google_oauth_client_id',
      'facebook_oauth_client_id',
      'microsoft_oauth_client_id',
      'team_name',
      'team_number',
    );
    $settings = FrcPortal\Setting::all();

    $response = 'angular.module("FrcPortal")';
    foreach($settings as $set) {
      if(in_array($set->setting,$configArr)) {
        $response .= '.constant("'.$set->setting.'", "'$set->value'")';
      }
    }
    $response = $response->withHeader('Content-type', 'application/javascript');
    return $response;
  });
  $this->group('/{setting_id:[a-z0-9]{13}}', function () {
    $this->get('', function ($request, $response, $args) {
      $authToken = $request->getAttribute("token");
      $loggedInUser = $authToken['data']->user_id;
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if(!checkAdmin($loggedInUser)) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }
      $setting_id = $args['setting_id'];
      $setting = FrcPortal\Setting::find($setting_id);
      $responseArr['status'] = true;
      $responseArr['msg'] = '';
      $responseArr['data'] = $setting;
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('', function ($request, $response, $args) {
      $authToken = $request->getAttribute("token");
      $loggedInUser = $authToken['data']->user_id;
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if(!checkAdmin($loggedInUser)) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }
      $setting_id = $args['setting_id'];
      $formData = $request->getParsedBody();
      $setting = FrcPortal\Setting::find($setting_id);
      $setting->value = $formData['setting'];
      if($setting->save()) {
        $responseArr['status'] = true;
        $responseArr['msg'] = 'Setting updated';
        $responseArr['data'] = $setting;
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->delete('', function ($request, $response, $args) {
      $authToken = $request->getAttribute("token");
      $loggedInUser = $authToken['data']->user_id;
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if(!checkAdmin($loggedInUser)) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }
      $setting_id = $args['setting_id'];
      $setting = FrcPortal\Setting::destroy($setting_id);
      if($setting) {
        $responseArr['status'] = true;
        $responseArr['msg'] = 'Setting removed';
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
  });
  $this->group('/{setting}', function () {
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

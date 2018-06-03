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
    $groupedArr = array();
    foreach($settings as $set) {
      $temp = $set->value;
      $type =$set->type;
    /*  if($type == 'string') {
        $temp = (string) $temp;
      } elseif($type == 'boolean') {
        $temp = (boolean) $temp;
      } */
      settype($temp,$type);
      $normalArr[$set->setting] = $temp;
      $groupedArr[$set->section][$set->setting] = $temp;
    }
    $responseArr['status'] = true;
    $responseArr['msg'] = '';
    $responseArr['data'] = array(
      'raw' => $settings,
      'normalized' => $normalArr,
      'grouped' => $groupedArr,
    );
    $response = $response->withJson($responseArr);
    return $response;
  });
/*  $this->group('/{setting_id:[a-z0-9]{13}}', function () {
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
  });*/
  $this->group('/section/{section}', function () {
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
      $section = $args['section'];
      $settings = FrcPortal\Setting::where('section',$section)->get();
      $data = array();
      foreach($settings as $set) {
        $temp = $set->value;
        $type =$set->type;
        if(strpos($set->setting, 'enable') !== false) {
          $temp = (boolean) $temp;
        }
        //settype($temp,$type);
        $data[$set->setting] = $temp;
      }
      $responseArr['status'] = true;
      $responseArr['msg'] = '';
      $responseArr['data'] = $data;
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
      $section = $args['section'];
      $formData = $request->getParsedBody();
      //loop through,
      //Do update or create
      foreach($formData as $setting=>$value) {
        $val = $value;
        if(strpos($setting, 'enable') !== false) {
          $val = $value != false ? 1:0;
        }
        $set = FrcPortal\Setting::updateOrCreate(
            ['section' => $section, 'setting' => $setting], ['value' => $val]
        );
      }
      $responseArr['status'] = true;
      $responseArr['msg'] = ucwords($section).' Settings Updated';
      //$responseArr['data'] = $data;
      $response = $response->withJson($responseArr);
      return $response;
    });
  });
  $this->post('/serviceAccountCredentials', function ($request, $response, $args) {
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

    $directory = $this->get('upload_directory');
    $uploadedFiles = $request->getUploadedFiles();
    $uploadedFile = $uploadedFiles['credentials'];
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
      $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
      $temp = file_get_contents($uploadedFile);
      $validJson = json_validate($temp);
      if($extension != 'json' || !$validJson['status']) {
        $responseArr = array('status'=>false, 'msg'=>'File must be a valid JSON file. '.$validJson['msg']);
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      $filename = 'service_account_credentials.json';
      $uploadedFile->moveTo($directory.'/'.$filename);
      $responseArr['status'] = true;
      $responseArr['msg'] = 'Service account credentials uploaded';
      $responseArr['data'] = json_decode(file_get_contents($directory.'/'.$filename),true);
    }
    $response = $response->withJson($responseArr);
    return $response;
  });
  $this->post('', function ($request, $response, $args) {

    $response = $response->withJson($responseArr);
    return $response;
  });
});





?>

<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/settings', function () {
  $this->get('', function ($request, $response, $args) {
    $userId = FrcPortal\Auth::user()->user_id;
    $formData = $request->getParsedBody();
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    if(!FrcPortal\Auth::isAdmin()) {
      return unauthorizedResponse($response);
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
  })->setName('Get Settings');
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
      $userId = FrcPortal\Auth::user()->user_id;
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      $section = $args['section'];
      $settings = FrcPortal\Setting::where('section',$section)->get();
      $data = array();
      foreach($settings as $set) {
        $data[$set->setting] = formatSettings($set->setting, $set->value);
      }
      $responseArr['status'] = true;
      $responseArr['msg'] = '';
      $responseArr['data'] = $data;
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Get Settings by Section');
    $this->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      $section = $args['section'];
      //loop through,
      //Do update or create
      foreach($formData as $setting=>$value) {
        $val = formatSettings($setting, $value);
        $set = FrcPortal\Setting::where('section', $section)->where('setting', $setting)->update(['value' => $val]);
      }
      $responseArr['status'] = true;
      $responseArr['msg'] = ucwords($section).' Settings Updated';
      //$responseArr['data'] = $data;
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update Settings');
  });
  $this->group('/serviceAccountCredentials', function () {
    $this->get('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $gsa_data = FrcPortal\Setting::where('section', 'service_account')->where('setting', 'google_service_account_data')->first();
      if(!is_null($gsa_data)) {
        $gsa_arr = explode(',',$gsa_data->value);
        $responseArr['data'] = array('client_email' => $gsa_arr[0]);
        $responseArr['status'] = true;
        $responseArr['msg'] = '';
      } else {
        $responseArr['msg'] = 'No Google Service Account information';
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Get Service Account Credentials');
    $this->post('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      $directory = $this->get('upload_directory');
      $uploadedFiles = $request->getUploadedFiles();
      $uploadedFile = $uploadedFiles['file'];
      if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $temp = file_get_contents($uploadedFile->file);
        $validJson = json_validate($temp);
        if($extension != 'json' || !$validJson['status']) {
          return badRequestResponse($response, $msg = 'File must be a valid JSON file. '.$validJson['msg']);
        }
        if($validJson['data']['type'] != 'service_account' || !isset($validJson['data']['client_email']) || $validJson['data']['client_email'] == ''
                                                           || !isset($validJson['data']['client_id']) || $validJson['data']['client_id'] == ''
                                                           || !isset($validJson['data']['private_key']) || $validJson['data']['private_key'] == '') {
          return badRequestResponse($response, $msg = 'File is not a valid Google Serice Account Credential JSON file.');
        }
        $json = json_encode($validJson['data']);
        $client_email = $validJson['data']['client_email'];
        $json_encypt = encryptItems($json) ;
        $data = $client_email.','.$json_encypt;
        $setting = FrcPortal\Setting::updateOrCreate(['section' => 'service_account', 'setting' => 'google_service_account_data'], ['value' => $data]);
        $responseArr['data'] = array('client_email'=>$client_email);
        $responseArr['status'] = true;
        $responseArr['msg'] = 'Service account credentials uploaded';
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update Service Account Credentials');
  });
  $this->group('/oauth/{provider}', function () {
    $this->get('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $provider = $args['provider'];
      $data = array(
        'client_id' => '',
        'client_secret' => ''
      );
      $client_id = FrcPortal\Setting::where('section','oauth')->where('setting',$provider.'_oauth_client_id')->first();
      $client_secret = FrcPortal\Setting::where('section','oauth')->where('setting',$provider.'_oauth_client_secret')->first();
      if(!is_null($client_id)) {
        $data['client_id'] = $client_id->value;
      }
      if(!is_null($client_secret) && $client_secret->value != '') {
        $data['client_secret'] = decryptItems($client_secret->value);
      }
      $responseArr['status'] = true;
      $responseArr['msg'] = '';
      $responseArr['data'] = $data;
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Get OAuth Credentials');
    $this->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $setting = FrcPortal\Setting::updateOrCreate(['section' => 'oauth', 'setting' => $provider.'_oauth_client_id'], ['value' => $formData['client_id']]);
      if($formData['client_secret'] != '') {
        $client_secret = encryptItems($formData['client_secret']);
        $setting = FrcPortal\Setting::updateOrCreate(['section' => 'oauth', 'setting' => $provider.'_oauth_client_id'], ['value' => $client_secret]);
      }

      $responseArr['status'] = true;
      $responseArr['msg'] = ucfirst($provider).' OAuth credentials updated';
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update OAuth Credentials');
  });
  $this->post('/testSlack', function ($request, $response, $args) {
    $userId = FrcPortal\Auth::user()->user_id;
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    if(!FrcPortal\Auth::isAdmin()) {
      return unauthorizedResponse($response);
    }

    $slackMsg = 'Test Slack notification.';
    if(slackMessageToUser($userId, $slackMsg)) {
      $responseArr = array(
        'status' => true,
        'msg' => 'Test Slack notification sent',
        'data' => null
      );
    }
    $response = $response->withJson($responseArr);
    return $response;
  })->setName('Test Slack');
  $this->post('', function ($request, $response, $args) {

    $response = $response->withJson($responseArr);
    return $response;
  });
});





?>

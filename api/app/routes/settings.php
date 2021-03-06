<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/settings', function () {
  $this->get('', function ($request, $response, $args) {
    $userId = FrcPortal\Utilities\Auth::user()->user_id;
    $formData = $request->getParsedBody();
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    if(!FrcPortal\Utilities\Auth::isAdmin()) {
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
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      $section = $args['section'];
      $settings = FrcPortal\Setting::where('section',$section)->get();
      $data = array();
      foreach($settings as $set) {
        $data[$set->setting] = formatSettings($set->setting, $set->value);
      }

      if($section == 'team') {
        $data['env_url'] = rtrim($data['env_url'],'/');
      } else if($section == 'notification') {
        $data['email_smtp_password'] = !empty($data['email_smtp_password']) ? decryptItems($data['email_smtp_password']) : '';
        $data['slack_api_token'] = !empty($data['slack_api_token']) ? decryptItems($data['slack_api_token']) : '';
      } else if($section == 'other') {
        $data['google_api_key'] = !empty($data['google_api_key']) ? decryptItems($data['google_api_key']) : '';
      }

      $responseArr['status'] = true;
      $responseArr['msg'] = '';
      $responseArr['data'] = $data;
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Get Settings by Section');
    $this->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      $section = $args['section'];
      if($section == 'team') {
        $formData['env_url'] = rtrim($formData['env_url'],'/');
      } else if($section == 'notification') {
        $formData['email_smtp_password'] = !empty($formData['email_smtp_password']) ? encryptItems($formData['email_smtp_password']) : '';
        $formData['slack_api_token'] = !empty($formData['slack_api_token']) ? encryptItems($formData['slack_api_token']) : '';
      } else if($section == 'other') {
        $formData['google_api_key'] = !empty($formData['google_api_key']) ? encryptItems($formData['google_api_key']) : '';
      }
      //loop through,
      //Do update or create
      foreach($formData as $setting=>$value) {
        $val = formatSettings($setting, $value);
        $set = FrcPortal\Setting::where('section', $section)->where('setting', $setting)->update(['value' => $val]);
      }
      $responseArr['status'] = true;
      $responseArr['msg'] = ucwords($section).' Settings Updated';
      insertLogs($level = 'Information', $message = 'Successfully updated '.ucwords($section).' settings');
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update Settings');
  });
  $this->group('/serviceAccountCredentials', function () {
    $this->get('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
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
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
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
        if($validJson['data']['type'] != 'service_account' || empty($validJson['data']['client_email']) || empty($validJson['data']['client_id']) || empty($validJson['data']['private_key'])) {
          return badRequestResponse($response, $msg = 'File is not a valid Google Serice Account Credential JSON file.');
        }
        $json = json_encode($validJson['data']);
        $client_email = $validJson['data']['client_email'];
        $json_encypt = encryptItems($json) ;
        $data = $client_email.','.$json_encypt;
        $setting = FrcPortal\Setting::updateOrCreate(['section' => 'service_account', 'setting' => 'google_service_account_data'], ['value' => $data]);
        $responseArr['data'] = array('client_email'=>$client_email);
        $responseArr['status'] = true;
        $responseArr['msg'] = 'Google Service account credentials uploaded';
        insertLogs($level = 'Information', $message = 'Successfully updated Google Service Account credentials');
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update Service Account Credentials');
    $this->delete('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $setting = FrcPortal\Setting::updateOrCreate(['section' => 'service_account', 'setting' => 'google_service_account_data'], ['value' => '']);
      $responseArr['status'] = true;
      $responseArr['msg'] = 'Google Service account credentials deleted';
      insertLogs($level = 'Information', $message = 'Successfully deleted Google Service Account credentials');
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Delete Google Service Account Credentials');
  });
  $this->group('/firstPortalCredentials', function () {
    $this->get('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $creds_data = FrcPortal\Setting::where('section', 'service_account')->where('setting', 'firstportal_credential_data')->first();
      if(!is_null($creds_data)) {
        $creds_arr = explode(',',$creds_data->value);
        $responseArr['data'] = array('email' => $creds_arr[0]);
        $responseArr['status'] = true;
        $responseArr['msg'] = '';
      } else {
        $responseArr['msg'] = 'No FIRST Portal credentials';
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Get Service Account Credentials');
    $this->post('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      if(empty($formData['email'])) {
        return badRequestResponse($response, $msg = 'Email cannot be blank');
      }
      if(empty($formData['password'])) {
        return badRequestResponse($response, $msg = 'Password cannot be blank');
      }
      $email = $formData['email'];
      $password = $formData['password'];
      $cookie = loginToFirst($email, $password);
      if($cookie == false) {
        $responseArr['data'] = array('email'=>$email);
        $responseArr['msg'] = 'Invalid Credentials';
      } else if($cookie != '') {
        $cookie_encypt = encryptItems($cookie);
        $data = $email.','.$cookie_encypt;
        $setting = FrcPortal\Setting::updateOrCreate(['section' => 'service_account', 'setting' => 'firstportal_credential_data'], ['value' => $data]);
        $responseArr['data'] = array('email'=>$email);
        $responseArr['status'] = true;
        $responseArr['msg'] = 'FIRST Portal credentials updated';
        insertLogs($level = 'Information', $message = 'Successfully updated FIRST Portal credentials');
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update FIRST Portal Credentials');
    $this->delete('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $setting = FrcPortal\Setting::updateOrCreate(['section' => 'service_account', 'setting' => 'firstportal_credential_data'], ['value' => '']);
      $responseArr['status'] = true;
      $responseArr['msg'] = 'FIRST Portal credentials deleted';
      insertLogs($level = 'Information', $message = 'Successfully deleted FIRST Portal credentials');
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Delete FIRST Portal Credentials');
  });
  $this->group('/oauth/{provider}', function () {
    $this->get('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
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
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $provider = $args['provider'];
      $setting = FrcPortal\Setting::updateOrCreate(['section' => 'oauth', 'setting' => $provider.'_oauth_client_id'], ['value' => $formData['client_id']]);
      if($formData['client_secret'] != '') {
        $client_secret = encryptItems($formData['client_secret']);
        $setting = FrcPortal\Setting::updateOrCreate(['section' => 'oauth', 'setting' => $provider.'_oauth_client_secret'], ['value' => $client_secret]);
      }

      $responseArr['status'] = true;
      $responseArr['msg'] = ucfirst($provider).' OAuth credentials updated';
      insertLogs($level = 'Information', $message = 'Successfully updated '.ucfirst($provider).' OAuth credentials');
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update OAuth Credentials');
  });
  $this->post('/resetAdminPass', function ($request, $response, $args) {
    $userId = FrcPortal\Utilities\Auth::user()->user_id;
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    if(!FrcPortal\Utilities\Auth::isAdmin()) {
      return unauthorizedResponse($response);
    }
    $password = bin2hex(random_bytes(20));
    $iniData = array();
    if(file_exists(__DIR__.'/secured/config.ini')) {
      $iniData = parse_ini_file(__DIR__.'/secured/config.ini');
    }
    $iniData['admin']['admin_pass'] = hash('sha512',$password);
    write_ini_file($iniData, __DIR__.'/secured/config.ini', true);
    $responseArr = standardResponse($status = true, $msg = 'Admin password reset', $data = array('password'=>$password));
    $response = $response->withJson($responseArr);
    return $response;
  })->setName('Reset Admin Password');
  $this->post('/testSlack', function ($request, $response, $args) {
    $user = FrcPortal\Utilities\Auth::user();
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    if(!FrcPortal\Utilities\Auth::isAdmin()) {
      return unauthorizedResponse($response);
    }

    $slackMsg = 'Test Slack notification.';
    if($user->slackMessage($slackMsg)) {
      $responseArr = array(
        'status' => true,
        'msg' => 'Test Slack notification sent',
        'data' => null
      );
    }
    $response = $response->withJson($responseArr);
    return $response;
  })->setName('Test Slack');
  $this->post('/testEmail', function ($request, $response, $args) {
    $user = FrcPortal\Utilities\Auth::user();
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    if(!FrcPortal\Utilities\Auth::isAdmin()) {
      return unauthorizedResponse($response);
    }
    $email = $user->emailUser($subject = 'Test Email Notification',$content = 'Test Email Notification.',$attachments = false);
    if($email) {
      $responseArr = array(
        'status' => true,
        'msg' => 'Test email notification sent',
        'data' => null
      );
    }
    $response = $response->withJson($responseArr);
    return $response;
  })->setName('Test Email');
  $this->group('/update', function () {
    $this->get('/branches', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $responseArr = standardResponse($status = true, $msg = '', $data = getBranchOptions());
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Get Version Branch Options');
    $this->get('/check', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
    	$branch = $request->getParam('branch') !== null ? $request->getParam('filter'):null;
      $responseArr = standardResponse($status = true, $msg = '', $data = check_github($branch=null));


      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Check for update');
  });
});





?>

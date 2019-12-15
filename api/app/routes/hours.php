<?php
use \Firebase\JWT\JWT;
use \Firebase\JWT\UnexpectedValueException;
use \Firebase\JWT\ExpiredException;
use \Firebase\JWT\SignatureInvalidException;
use \Firebase\JWT\BeforeValidException;
use \Firebase\JWT\Exception;
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/hours', function () {
  $this->group('/missingHoursRequests', function () {
    $this->get('', function ($request, $response, $args) {
      $users = array();
    	$data = array();

      $defaults = array(
    		'filter' => '',
    		'limit' => 10,
    		'order' => '-time_in',
    		'page' => 1,
    	);
      $inputs = checkSearchInputs($request, $defaults);
      $filter = $inputs['filter'];
      $limit = $inputs['limit'];
      $order = $inputs['order'];
      $page = $inputs['page'];

      $listOnly = $request->getParam('listOnly') !== null && $request->getParam('listOnly')==true ? true:false;
      $totalNum = 0;
      $users = FrcPortal\MissingHoursRequest::with(['approver','user']);
      $queryArr = array();
    	$queryStr = '';
    	if($filter != '') {
        //$queryArr[] = '(full_name LIKE "%'.$filter.'%")';
      	$filterArr = explode(' ',$filter);
        $users = $users->whereHas('user', function ($query) use ($filterArr) {
      		foreach($filterArr as $filter) {
      			$query->where('fname', 'like', '%'.$filter.'%');
      			$query->orWhere('lname', 'like', '%'.$filter.'%');
      		}
      	});
        //$users = $users->orHavingRaw('email LIKE ?',array('%'.$filter.'%'));
    	}
      $totalNum = count($users->get());

      $orderBy = '';
    	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
    	if(in_array($orderCol,array('full_name','time_in','time_out','hours'))) {
    		$orderBy = 'ASC';
    		if($order[0] == '-') {
    			$orderBy = 'DESC';
    		}
    	}

      $offset = 0;
    	if($limit > 0) {
    		$offset	= ($page - 1) * $limit;
    	} elseif($limit == 0) {
        $limit = $totalNum;
      }
      $users = $users->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();

      $data['data'] = $users;
      $data['total'] = $totalNum;
      $data['maxPage'] = $limit > 0 ? ceil($totalNum/$limit) : 0;
      $data['status'] =true;
      $data['msg'] = '';
      if($listOnly) {
        $data = $users;
      }

      $response = $response->withJson($data);
      return $response;
    })->setName('Get Missing Hours Requests');
    $this->group('/{request_id:[a-z0-9]{13}}', function () {
      $this->put('/approve', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
        }
        $request_id = $args['request_id'];

        $mhRequest = FrcPortal\MissingHoursRequest::find($request_id);
        $mh = new FrcPortal\MeetingHour();
      	$date = time();
      	$user_id = $mhRequest->user_id;
        if(!is_null($mhRequest)) {
          $mhRequest->approved = true;
          $mhRequest->approved_date = date('Y-m-d H:i:s',$date);
          $mhRequest->approved_by = $userId;
          $mh->user_id = $user_id;
          $mh->time_in = $mhRequest->time_in;
          $mh->time_out = $mhRequest->time_out;
          try {
             DB::beginTransaction();
             $mhRequest->save();
             $mh->save();
             DB::commit();
             $responseArr['status'] = true;
             $responseArr['msg'] = 'Missing hours request approved';
             $mhRequest->load('user');
             insertLogs($level = 'Information', $message = 'Missing hours request approved for '.$mhRequest->user->full_name.'. ('.$mhRequest['time_in'].' - '.$mhRequest['time_out'].')');
          } catch(Exception $e){
             DB::rollback();
          }
        }
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Approve Missing Hours Request');
      $this->put('/deny', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
        }
        $request_id = $args['request_id'];

        $mhRequest = FrcPortal\MissingHoursRequest::find($request_id);
        $date = time();
        if(!is_null($request)) {
          $mhRequest->approved = false;
          $mhRequest->approved_date = date('Y-m-d H:i:s',$date);
          $mhRequest->approved_by = $userId;
          if($mhRequest->save()) {
             $responseArr['status'] = true;
             $responseArr['msg'] = 'Missing hours request denied';
             insertLogs($level = 'Information', $message = 'Missing hours request denied for '.$mhRequest->user->full_name.'. ('.$mhRequest['time_in'].' - '.$mhRequest['time_out'].')');
          }
        }
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Deny Missing Hours Request');
    });
  });
  $this->group('/signIn', function() {
    //Get the list of users and their last sign/out and hours
    $this->get('/list', function ($request, $response, $args) {
      $authed = FrcPortal\Auth::isAuthenticated() ? true:false;
      if(!$authed && !is_null($request->getParam('signin_token'))) {
        $key = getSettingsProp('jwt_signin_key');
        $signin_token = $request->getParam('signin_token');
        try {
          $decoded = JWT::decode($signin_token, $key, array('HS256'));
          $authed = $decoded->data->signin;
        } catch(ExpiredException $e) {
          insertLogs($level = 'Warning', $message = 'Authorization Error: '.$e->getMessage());
          return unauthorizedResponse($response, $msg = 'Authorization Error.');
        } catch(SignatureInvalidException $e){
          insertLogs($level = 'Warning', $message = 'Authorization Error: '.$e->getMessage());
          return unauthorizedResponse($response, $msg = 'Authorization Error.');
        }
      }else if(!$authed) {
        return unauthorizedResponse($response);
      }
      $users = getSignInList(date('Y'));
      $response = $response->withJson($users);
      return $response;
    })->setName('Get Sign In List');
    //Time Sheet
    $this->get('/timeSheet/{date:(?:[1-9]\d{3})-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12]\d|3[01])}', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      $date = $args['date'];
      $users = FrcPortal\User::with(['meeting_hours' => function ($query) use ($date)  {
        $query->where('time_in', 'LIKE', $date.' %'); // fields from comments table,
      }])->whereHas('meeting_hours', function ($query) use ($date) {
        $query->where('time_in', 'LIKE', $date.' %'); // fields from comments table,
      })->orWhere('status',true)->orderBy('fname', 'ASC')->get();

      $responseArr = array(
        'status' => true,
        'msg' => '',
        'data' => $users
      );
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Get Timesheet');
    //Get the list of all sign in/out records
    $this->get('/records', function ($request, $response, $args) {
      $users = array();
      $data = array();

      $defaults = array(
        'filter' => '',
        'limit' => 10,
        'order' => 'full_name',
        'page' => 1,
      );
      $inputs = checkSearchInputs($request, $defaults);
      $filter = $inputs['filter'];
      $limit = $inputs['limit'];
      $order = $inputs['order'];
      $page = $inputs['page'];
      $listOnly = $request->getParam('listOnly') !== null && $request->getParam('listOnly')==true ? true:false;

      $users = FrcPortal\MeetingHour::with('user')->select()->addSelect(DB::raw('(time_to_sec(IFNULL(timediff(time_out, time_in),0)) / 3600) as hours'));
      $totalNum = 0;
      if($filter != '') {
        $filterArr = explode(' ',$filter);
        $users = $users->whereHas('user', function ($query) use ($filterArr) {
      		foreach($filterArr as $filter) {
      			$query->where('email', 'like', '%'.$filter.'%');
            $query->orWhere('fname', 'like', '%'.$filter.'%');
      			$query->orWhere('lname', 'like', '%'.$filter.'%');
      		}
      	});
        $users = $users->orHavingRaw('hours LIKE ?',array('%'.$filter.'%'));
      }
      $totalNum = count($users->get());

      $orderBy = '';
      $orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
      if(in_array($orderCol,array('full_name','time_in','time_out','hours'))) {
        $orderBy = 'ASC';
        if($order[0] == '-') {
          $orderBy = 'DESC';
        }
      }

      if($limit > 0) {
        $offset	= ($page - 1) * $limit;
      } elseif($limit == 0) {
        $limit = $totalNum;
      }

      $users = $users->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();

      $data['data'] = $users;
      $data['total'] = $totalNum;
      $data['maxPage'] = $limit > 0 ? ceil($totalNum/$limit) : 0;
      $data['status'] =true;
      $data['msg'] = '';
      if($listOnly) {
        $data = $users;
      }

      $response = $response->withJson($data);
      return $response;
    })->setName('Get Sign In Records');
    //Create a new signin token
    $this->post('/authorize', function ($request, $response, $args) {
      $args = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

      $user = null;
      if(isset($args['auth_token'])) {
        $key = getSettingsProp('jwt_key');
        $jwt = $args['auth_token'];
        try {
          $decoded = JWT::decode($jwt, $key, array('HS256'));
          $user = $decoded->data;
          FrcPortal\Auth::setCurrentUser($user->user_id);
        } catch(ExpiredException $e) {
          insertLogs($level = 'Warning', $message = 'Tried to authorize sign in. User token was expired. '.$e->getMessage());
          return unauthorizedReloginResponse($response, $msg = 'Authorization Error. Please login in again.');
        } catch(SignatureInvalidException $e){
          insertLogs($level = 'Warning', $message = 'Tried to generate sign in token. Old user token signature was invalid. '.$e->getMessage());
          return unauthorizedReloginResponse($response, $msg = 'Authorization Error. Please login in again.');
        } catch(BeforeValidException $e){
          insertLogs($level = 'Warning', $message = 'Tried to generate sign in token. '.$e->getMessage());
          return unauthorizedReloginResponse($response, $msg = 'Authorization Error. Please login in again.');
        } catch(UnexpectedValueException $e){
          insertLogs($level = 'Warning', $message = 'Tried to generate sign in token. '.$e->getMessage());
          return unauthorizedReloginResponse($response, $msg = 'Authorization Error. Please login in again.');
        }
      } elseif(isset($args['auth_code'])) {
        $user = FrcPortal\User::where('signin_pin',hash('sha256',$args['auth_code']))->where('status',true)->where('admin',true)->first();
        FrcPortal\Auth::setCurrentUser($user->user_id);
      } else {
        insertLogs($level = 'Warning', $message = 'Sign in authorization failed.');
        return badRequestResponse($response);
      }
      if(!is_null($user) && $user->status && $user->admin) {
        $ts = time();
        $te = time()+60*60*12; //12 hours liftime
        $tokenArr = generateSignInToken($ts, $te);
        $responseArr = array('status'=>true, 'type'=>'success', 'msg'=>'Sign In Authorized', 'signin_token'=>$tokenArr['token'], 'qr_code'=>$tokenArr['qr_code']);
        insertLogs($level = 'Information', $message = 'Sign in authorized.');
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        insertLogs($level = 'Warning', $message = 'Sign in authorization failed. Unauthorized user');
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Authorize Sign In');
    //Create a new signin token
    $this->post('/token', function ($request, $response, $args) {
      $args = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

      $decoded = false;
      $ts = time();
      $te = time()+60*60*12; //12 hours liftime
      $key = getSettingsProp('jwt_signin_key');
      if(isset($args['token'])) {
        $jwt = $args['token'];
        try {
          $decoded = JWT::decode($jwt, $key, array('HS256'));
          $data = (array) $decoded;
          $te = time()+60*60; //30 second liftime
        } catch(ExpiredException $e) {
          insertLogs($level = 'Warning', $message = 'Tried to generate sign in token. Old token was expired. '.$e->getMessage());
          return unauthorizedResponse($response, $msg = 'Authorization Error. Token was expired. Please Reauthorize sign in.');
        } catch(SignatureInvalidException $e){
          insertLogs($level = 'Warning', $message = 'Tried to generate sign in token. Old token signature was invalid. '.$e->getMessage());
          return unauthorizedResponse($response, $msg = 'Authorization Error. Please Deauthorize and Reauthorize sign in.');
        } catch(BeforeValidException $e){
          $date = new DateTime($data['nbf']);
          insertLogs($level = 'Warning', $message = 'Tried to generate sign in token. Old token is not valid before '.$date->format('F j, Y g:i:s A').'. '.$e->getMessage());
          return unauthorizedResponse($response, $msg = 'Authorization Error. Token is not valid before '.$date->format('F j, Y g:i:s A').'.');
        } catch(UnexpectedValueException $e){
          insertLogs($level = 'Warning', $message = 'Tried to generate sign in token. '.$e->getMessage());
          return unauthorizedResponse($response, $msg = 'Authorization Error. Please Deauthorize and Reauthorize sign in.');
        }
      }
      if(isset($args['time_start']) && $args['time_start'] != '') {
        $ts = strtotime($args['time_start']);
      }
      if(isset($args['time_end']) && $args['time_end'] != '') {
        $te = strtotime($args['time_end']);
      }
      if(FrcPortal\Auth::isAdmin() || $decoded !== false) {
        $tokenArr = generateSignInToken($ts, $te);
        $responseArr = array('status'=>true, 'type'=>'success', 'msg'=>'Sign In Authorized', 'signin_token'=>$tokenArr['token'], 'qr_code'=>$tokenArr['qr_code']);
      } else {
        return unauthorizedResponse($response);
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Get Sign In Token');
    //Deauthorize the current signin token
    $this->post('/deauthorize', function ($request, $response, $args) {
      $responseArr = array('status'=>true, 'type'=>'success', 'msg'=>'Sign In Deauthorized');
      insertLogs($level = 'Information', $message = 'Sign In deauthorized.');
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Deauthorize Sign In');
    //Clock in and Out
    $this->post('', function ($request, $response, $args) {
      $args = $request->getParsedBody();
      FrcPortal\Auth::setCurrentUser($args['user_id']);
      if(isset($args['token'])) {
        $key = getSettingsProp('jwt_signin_key');
        try{
          $decoded = JWT::decode($args['token'], $key, array('HS256'));
          $data = (array) $decoded;
          if(isset($data['jti']) || $data['jti'] != '') {
            $jti = $data['jti'];
            if(isset($args['pin']) && isset($args['user_id']) && $args['pin'] != '' && $args['user_id'] != '') {
              $user = FrcPortal\User::where('signin_pin',hash('sha256',$args['pin']))->where('user_id',$args['user_id'])->where('status',true)->first();
              if($user != null) {
                if($user->other_adult) {
                  insertLogs($level = 'Information', $message = $user->user_type.' user type is not authorized for sign in.');
                  return unauthorizedResponse($response, $msg = $user->user_type.' user type is not authorized for sign in.');
                }
                $user_id = $user->user_id;
                $name = $user->full_name;
                $date = time();
                $hours = FrcPortal\MeetingHour::where('user_id',$user_id)->whereNotNull('time_in')->whereNull('time_out')->orderBy('time_in','DESC')->first();
                if($hours != null) {
                  $hours_id = $hours->hours_id;
                  $hours->time_out = date('Y-m-d H:i:s',$date);
                  if($hours->save()) {
                    $emailData = array(
                      'signin_time' => date('M d, Y H:i A', $date),
                      'signin_out' => 'sign_out'
                    );
                    $emailInfo = emailSignInOut($user_id,$emailData);
                    $msgData = array(
                      'slack' => array(
                        'title' => 'Sign out',
                        'body' => 'You signed out at '.$emailData['signin_time']
                      ),
                      'email' => array(
                        'subject' => $emailInfo['subject'],
                        'content' =>  $emailInfo['content'],
                        'userData' => $user
                      )
                    );
                    $user->sendUserNotification('sign_in_out', $msgData);
                    $users = getSignInList(date('Y'));
                    insertLogs($level = 'Information', $message = 'User signed out using PIN');
                    $responseArr = array('status'=>true, 'msg'=>$name.' signed out at '.date('M d, Y H:i A', $date), 'signInList'=>$users);
                  } else {
                  $responseArr = 	array('status'=>false, 'msg'=>'Something went wrong signing out');
                  }
                } else {
                  $hours = FrcPortal\MeetingHour::create(['user_id' => $user_id, 'time_in' => date('Y-m-d H:i:s',$date)]);
                  if($hours) {
                    $emailData = array(
                      'signin_time' => date('M d, Y H:i A', $date),
                      'signin_out' => 'sign_in'
                    );
                    $emailInfo = emailSignInOut($user_id,$emailData);
                    $msgData = array(
                      'slack' => array(
                        'title' => 'Sign In',
                        'body' => 'You signed in at '.$emailData['signin_time']
                      ),
                      'email' => array(
                        'subject' => $emailInfo['subject'],
                        'content' =>  $emailInfo['content'],
                        'userData' => $user
                      )
                    );
                    $user->sendUserNotification('sign_in_out', $msgData);
                    $users = getSignInList(date('Y'));
                    insertLogs($level = 'Information', $message = 'User signed in using PIN');
                    $responseArr = array('status'=>true, 'msg'=>$name.' Signed In at '.date('M d, Y H:i A', $date), 'signInList'=>$users);
                  } else {
                    $responseArr = array('status'=>false, 'msg'=>'Something went wrong signing in');
                    insertLogs($level = 'Warning', $message = 'User tried to sign in using PIN. Something went wrong');
                  }
                }
              }  else {
                $responseArr = array('status'=>false, 'msg'=>'PIN is incorrect');
                  insertLogs($level = 'Information', $message = 'User tried to sign in using PIN. PIN was incorrect.');
              }
            } else {
              insertLogs($level = 'Information', $message = 'User tried to sign in using PIN. User ID and/or PIN cannot be blank.');
              $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'User ID and/or PIN cannot be blank!');
            }
          } else {
            $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid JTI.');
          }
        } catch(ExpiredException $e) {
          insertLogs($level = 'Warning', $message = 'User tried to sign in using PIN. Token was expired. '.$e->getMessage());
          return unauthorizedResponse($response, $msg = 'Authorization Error. Token was expired.');
        } catch(SignatureInvalidException $e){
          insertLogs($level = 'Warning', $message = 'User tried to sign in using PIN. Token signature was invalid. '.$e->getMessage());
          return unauthorizedResponse($response, $msg = 'Authorization Error. Please Deauthorize and Reauthorize sign in.');
        } catch(BeforeValidException $e){
          $date = new DateTime($data['nbf']);
          insertLogs($level = 'Warning', $message = 'User tried to sign in using PIN. Token is not valid before '.$date->format('F j, Y g:i:s A').'. '.$e->getMessage());
          return unauthorizedResponse($response, $msg = 'Authorization Error. Token is not valid before '.$date->format('F j, Y g:i:s A').'.');
        } catch(UnexpectedValueException $e){
          insertLogs($level = 'Warning', $message = 'User tried to sign in using PIN. '.$e->getMessage());
          return unauthorizedResponse($response, $msg = 'Authorization Error. Please Deauthorize and Reauthorize sign in.');
        }
      } else {
        insertLogs($level = 'Information', $message = 'User tried to sign in using PIN. Sign in not authorized at this tim on this device.');
        $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Sign in is not authorized at this time and/or on this device. Please see a mentor.');
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Sign In with PIN');
    //Clock in and Out
    $this->post('/qr', function ($request, $response, $args) {
      $user = FrcPortal\Auth::user();
      $args = $request->getParsedBody();
      if($user->other_adult) {
        insertLogs($level = 'Information', $message = $user->user_type.' user type is not authorized for sign in.');
        return unauthorizedResponse($response, $msg = $user->user_type.' user type is not authorized for sign in.');
      }
      if(!isset($args['token']) ||!checkJwtFormat($args['token']) ) {
        insertLogs($level = 'Information', $message = 'Invalid QR code value. "'.$args['token'].'" is not a valid token.');
        return badRequestResponse($response, $msg = 'Invalid token. QR code value ('.$args['token'].') is not a valid token.');
      }
      $key = getSettingsProp('jwt_signin_key');
      try{
        $decoded = JWT::decode($args['token'], $key, array('HS256'));
        $data = (array) $decoded;
        if(isset($data['jti']) || $data['jti'] != '') {
          $jti = $data['jti'];
          $user_id = $user->user_id;
          $name = $user->full_name;
          $date = time();
          $hours = FrcPortal\MeetingHour::where('user_id',$user_id)->whereNotNull('time_in')->whereNull('time_out')->orderBy('time_in','DESC')->first();
          if($hours != null) {
            $hours_id = $hours->hours_id;
            $hours->time_out = date('Y-m-d H:i:s',$date);
            if($hours->save()) {
              $ti = $hours->time_in;
              $to = $hours->time_out;
              $hourTotal = round((strtotime($to) - strtotime($ti))/3600, 2);
              $emailData = array(
                'signin_time' => date('M d, Y H:i A', $date),
                'signin_out' => 'sign_out'
              );
              $emailInfo = emailSignInOut($user_id,$emailData);
              $msgData = array(
                'slack' => array(
                  'title' => 'Sign out',
                  'body' => 'You signed out at '.$emailData['signin_time']
                ),
                'email' => array(
                  'subject' => $emailInfo['subject'],
                  'content' =>  $emailInfo['content'],
                  'userData' => $user
                )
              );
              $user->sendUserNotification('sign_in_out', $msgData);
              insertLogs($level = 'Information', $message = 'User signed out using QR');
              $responseArr = array('status'=>true, 'msg'=>$name.' signed out at '.date('M d, Y H:i A', $date).' for a total of '.$hourTotal.' hours');
            } else {
            $responseArr = 	array('status'=>false, 'msg'=>'Something went wrong signing out');
            }
          } else {
            $hours = FrcPortal\MeetingHour::create(['user_id' => $user_id, 'time_in' => date('Y-m-d H:i:s',$date)]);
            if($hours) {
              $emailData = array(
                'signin_time' => date('M d, Y H:i A', $date),
                'signin_out' => 'sign_in'
              );
              $emailInfo = emailSignInOut($user_id,$emailData);
              $msgData = array(
                'slack' => array(
                  'title' => 'Sign In',
                  'body' => 'You signed in at '.$emailData['signin_time']
                ),
                'email' => array(
                  'subject' => $emailInfo['subject'],
                  'content' =>  $emailInfo['content'],
                  'userData' => $user
                )
              );
              $user->sendUserNotification('sign_in_out', $msgData);
              insertLogs($level = 'Information', $message = 'User signed in using QR');
              $responseArr = array('status'=>true, 'msg'=>$name.' Signed In at '.date('M d, Y H:i A', $date));
            } else {
              $responseArr = array('status'=>false, 'msg'=>'Something went wrong signing in');
            }
          }
        } else {
          $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid JTI.');
        }
      } catch(ExpiredException $e) {
        insertLogs($level = 'Warning', $message = 'User tried to sign in using QR. Token was expired. '.$e->getMessage());
        return unauthorizedResponse($response, $msg = 'Authorization Error. Token was expired.');
      } catch(SignatureInvalidException $e){
        insertLogs($level = 'Warning', $message = 'User tried to sign in using QR. Token signature was invalid. '.$e->getMessage());
        return unauthorizedResponse($response, $msg = 'Authorization Error. Please Deauthorize and Reauthorize sign in.');
      } catch(BeforeValidException $e){
        $date = new DateTime($data['nbf']);
        insertLogs($level = 'Warning', $message = 'User tried to sign in using QR. Token is not valid before '.$date->format('F j, Y g:i:s A').'. '.$e->getMessage());
        return unauthorizedResponse($response, $msg = 'Authorization Error. Token is not valid before '.$date->format('F j, Y g:i:s A').'.');
      } catch(UnexpectedValueException $e){
        insertLogs($level = 'Warning', $message = 'User tried to sign in using QR. '.$e->getMessage());
        return unauthorizedResponse($response, $msg = 'Authorization Error. Please Deauthorize and Reauthorize sign in.');
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Sign In with QR');
  });
});

















?>

<?php
use \Firebase\JWT\JWT;
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
    });
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
          } catch(\Exception $e){
             DB::rollback();
          }
        }
        $response = $response->withJson($responseArr);
        return $response;
      });
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
          if($request->save()) {
             $responseArr['status'] = true;
             $responseArr['msg'] = 'Missing hours request denied';
             insertLogs($level = 'Information', $message = 'Missing hours request denied for '.$mhRequest->user->full_name.'. ('.$mhRequest['time_in'].' - '.$mhRequest['time_out'].')');
          }
        }
        $response = $response->withJson($responseArr);
        return $response;
      });
    });
  });
  $this->group('/signIn', function() {
    //Get the list of users and their last sign/out and hours
    $this->get('/list', function ($request, $response, $args) {
      $authed = FrcPortal\Auth::isAuthenticated() ? true:false;
      if(!$authed && isset($args['signin_token'])) {
        $key = getSettingsProp('jwt_signin_key');
        $signin_token = $args['signin_token'];
        try {
          $decoded = JWT::decode($signin_token, $key, array('HS256'));
          $authed = $decoded->data->signin;
          die($authed);
        } catch(\ExpiredException $e) {
          return unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage());
        } catch(\SignatureInvalidException $e){
          return unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage());
        }
      }
      if(!$authed) {
        return unauthorizedResponse($response);
      }
      $users = getSignInList(date('Y'));
      $response = $response->withJson($users);
      return $response;
    });
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
    });
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
    });
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
          $user = $decoded->data->status && $decoded->data->admin;
        } catch(\ExpiredException $e) {
          $responseArr = unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage());
        } catch(\SignatureInvalidException $e){
          $responseArr = unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage());
        }
      } elseif(isset($args['auth_code'])) {
        $user = FrcPortal\User::where('signin_pin',hash('sha256',$args['auth_code']))->where('status',true)->where('admin',true)->first();
      } else {
        insertLogs($level = 'Warning', $message = 'Sign In Authorization Failed.');
        return badRequestResponse($response);
      }
      if(!is_null($user)) {
        $ts = time();
        $te = time()+30; //12 hours liftime
        $tokenArr = generateSignInToken($ts, $te);
        $responseArr = array('status'=>true, 'type'=>'success', 'msg'=>'Sign In Authorized', 'signin_token'=>$tokenArr['token'], 'qr_code'=>$tokenArr['qr_code']);
        insertLogs($level = 'Information', $message = 'Sign In authorized by '.$user->full_name.'.');
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        insertLogs($level = 'Warning', $message = 'Sign In Authorization Failed.');
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
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
          $te = time()+30; //30 second liftime
        } catch(\ExpiredException $e) {
          $responseArr = unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage());
        } catch(\SignatureInvalidException $e){
          $responseArr = unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage());
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
    });
    //Deauthorize the current signin token
    $this->post('/deauthorize', function ($request, $response, $args) {
      $responseArr = array('status'=>true, 'type'=>'success', 'msg'=>'Sign In Deauthorized');
      insertLogs($level = 'Information', $message = 'Sign In deauthorized.');
      $response = $response->withJson($responseArr);
      return $response;
    });
    //Clock in and Out
    $this->post('', function ($request, $response, $args) {
      $args = $request->getParsedBody();
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
                    $responseArr = array('status'=>true, 'msg'=>$name.' Signed In at '.date('M d, Y H:i A', $date), 'signInList'=>$users);
                  } else {
                    $responseArr = array('status'=>false, 'msg'=>'Something went wrong signing in');
                  }
                }
              }  else {
                $responseArr = array('status'=>false, 'msg'=>'PIN is incorrect');
              }
            } else {
              $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'User ID and/or PIN number cannot be blank!');
            }
          } else {
            $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid JTI.');
          }
        } catch(\ExpiredException $e) {
          return unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage().'.  Please see Mentor.');
        } catch(\SignatureInvalidException $e){
          return unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage().'.  Please see Mentor.');
        } catch(\BeforeValidException $e){
          return unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage().'.  Please see Mentor.');
        } catch(\UnexpectedValueException $e){
          return unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage().'.  Please see Mentor.');
        }
      } else {
        $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Sign in is not authorized at this time and/or on this device. Please see a mentor.');
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    //Clock in and Out
    $this->post('/qr', function ($request, $response, $args) {
      $user = FrcPortal\Auth::user();
      $args = $request->getParsedBody();
      if(isset($args['token'])) {
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
                //$users = getSignInList(date('Y'));
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
                //$users = getSignInList(date('Y'));
                $responseArr = array('status'=>true, 'msg'=>$name.' Signed In at '.date('M d, Y H:i A', $date));
              } else {
                $responseArr = array('status'=>false, 'msg'=>'Something went wrong signing in');
              }
            }
          } else {
            $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid JTI.');
          }
        } catch(\UnexpectedValueException $e){
          return unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage().'.  Please see Mentor.');
        } catch(\ExpiredException $e) {
          return unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage().'.  Please see Mentor.');
        } catch(\SignatureInvalidException $e){
          return unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage().'.  Please see Mentor.');
        } catch(\BeforeValidException $e){
          return unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage().'.  Please see Mentor.');
        } catch(\Exception $e){
          return unauthorizedResponse($response, $msg = 'Authorization Error. '.$e->getMessage().'.  Please see Mentor.');
        }
      } else {
        $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Invalid token');
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
  });
});

















?>

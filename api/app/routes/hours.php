<?php
use \Firebase\JWT\JWT;
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/hours', function () {
  $this->group('/missingHoursRequests', function () {
    $this->get('', function ($request, $response, $args) {
      $users = array();
    	$data = array();

      $filter = $request->getParam('filter') !== null ? $request->getParam('filter'):'';
      $limit = $request->getParam('limit') !== null ? $request->getParam('limit'):10;
      $order = $request->getParam('order') !== null ? $request->getParam('order'):'full_name';
      $page = $request->getParam('page') !== null ? $request->getParam('page'):1;
      $listOnly = $request->getParam('listOnly') !== null && $request->getParam('listOnly')==true ? true:false;

      $queryArr = array();
    	$queryStr = '';
    	if($filter != '') {
        $queryArr[] = '(full_name LIKE "%'.$filter.'%")';
    	}
      $totalNum = 0;
    	if(count($queryArr) > 0) {
    		$queryStr = implode(' OR ',$queryArr);
        $users = FrcPortal\MissingHoursRequest::with(['approver'])->leftJoin('users', 'users.user_id', '=', 'missing_hours_requests.user_id')->addSelect(DB::raw('missing_hours_requests.*, CONCAT(users.fname," ",users.lname) AS full_name'))->havingRaw($queryStr)->get();
        $totalNum = count($users);
    	} else {
        $totalNum = FrcPortal\MissingHoursRequest::count();
      }

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

      if($filter != '' ) {
        $users = FrcPortal\MissingHoursRequest::with(['approver'])->leftJoin('users', 'users.user_id', '=', 'missing_hours_requests.user_id')->addSelect(DB::raw('missing_hours_requests.*, CONCAT(users.fname," ",users.lname) AS full_name'))->havingRaw($queryStr)->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();
      } else {
        $users = FrcPortal\MissingHoursRequest::with(['approver'])->leftJoin('users', 'users.user_id', '=', 'missing_hours_requests.user_id')->addSelect(DB::raw('missing_hours_requests.*, CONCAT(users.fname," ",users.lname) AS full_name'))->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();
      }


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
        $authToken = $request->getAttribute("token");
        $userId = $authToken['data']->user_id;
        $request_id = $args['request_id'];
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
        $request = FrcPortal\MissingHoursRequest::find($request_id);
        $mh = new FrcPortal\MeetingHour();
      	$date = time();
      	$user_id = $request['user_id'];
        if(!is_null($request)) {
          $request->approved = true;
          $request->approved_date = date('Y-m-d H:i:s',$date);
          $request->approved_by = $userId;
          $mh->user_id = $user_id;
          $mh->time_in = $request['time_in'];
          $mh->time_out = $request['time_out'];
          try {
             DB::beginTransaction();
             $request->save();
             $mh->save();
             DB::commit();
             $responseArr['Status'] = true;
             $responseArr['msg'] = 'Missing hours request approved';
          } catch(\Exception $e){
             DB::rollback();
          }
        }
        $response = $response->withJson($responseArr);
        return $response;
      });
      $this->put('/deny', function ($request, $response, $args) {
        $authToken = $request->getAttribute("token");
        $userId = $authToken['data']->user_id;
        $request_id = $args['request_id'];
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
        $request = FrcPortal\MissingHoursRequest::find($request_id);
        $date = time();
        if(!is_null($request)) {
          $request->approved = false;
          $request->approved_date = date('Y-m-d H:i:s',$date);
          $request->approved_by = $userId;
          if($request->save()) {
             $responseArr['Status'] = true;
             $responseArr['msg'] = 'Missing hours request denied';
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
      $season = FrcPortal\Season::where('year',date('Y'))->first();
      $users = FrcPortal\User::with(['annual_requirements' => function ($query) use ($season)  {
        $query->where('season_id', $season->season_id); // fields from comments table,
      }, 'last_sign_in'])->where('status','1')->get();
      $response = $response->withJson($users);
      return $response;
    });
    //Time Sheet
    $this->get('/timeSheet/{date:([1-9]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))}', function ($request, $response, $args) {
      $authToken = $request->getAttribute("token");
      $userId = $authToken['data']->user_id;
      $date = $args['date'];
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
      $users = FrcPortal\User::with(['meeting_hours' => function ($query) use ($date)  {
        $query->where('time_in', 'LIKE', $date.' %'); // fields from comments table,
      }])->where('status',true)->get();
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

      $filter = $request->getParam('filter') !== null ? $request->getParam('filter'):'';
      $limit = $request->getParam('limit') !== null ? $request->getParam('limit'):10;
      $order = $request->getParam('order') !== null ? $request->getParam('order'):'full_name';
      $page = $request->getParam('page') !== null ? $request->getParam('page'):1;
      $listOnly = $request->getParam('listOnly') !== null && $request->getParam('listOnly')==true ? true:false;

      $queryArr = array();
      $queryStr = '';
      if($filter != '') {
        $queryArr[] = '(users.email LIKE "%'.$filter.'%")';
        $queryArr[] = '(full_name LIKE "%'.$filter.'%")';
        $queryArr[] = '(hours LIKE "%'.$filter.'%")';
      }
      $totalNum = 0;
      if(count($queryArr) > 0) {
        $queryStr = implode(' OR ',$queryArr);
        $users = FrcPortal\MeetingHour::leftJoin('users', 'users.user_id', '=', 'meeting_hours.user_id')->select('users.*', 'meeting_hours.*',DB::raw('CONCAT(users.fname," ",users.lname) AS full_name, UNIX_TIMESTAMP(time_in) AS time_in_unix, UNIX_TIMESTAMP(time_out) AS time_out_unix, (time_to_sec(IFNULL(timediff(time_out, time_in),0)) / 3600) as hours'))->havingRaw($queryStr)->get();
        $totalNum = count($users);
      } else {
        $totalNum = FrcPortal\MeetingHour::count();
      }

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

      if($filter != '' ) {
        $users = FrcPortal\MeetingHour::leftJoin('users', 'users.user_id', '=', 'meeting_hours.user_id')->select('users.*', 'meeting_hours.*',DB::raw('CONCAT(users.fname," ",users.lname) AS full_name, UNIX_TIMESTAMP(time_in) AS time_in_unix, UNIX_TIMESTAMP(time_out) AS time_out_unix, (time_to_sec(IFNULL(timediff(time_out, time_in),0)) / 3600) as hours'))->havingRaw($queryStr)->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();
      } else {
        $users = FrcPortal\MeetingHour::leftJoin('users', 'users.user_id', '=', 'meeting_hours.user_id')->select('users.*', 'meeting_hours.*',DB::raw('CONCAT(users.fname," ",users.lname) AS full_name, UNIX_TIMESTAMP(time_in) AS time_in_unix, UNIX_TIMESTAMP(time_out) AS time_out_unix, (time_to_sec(IFNULL(timediff(time_out, time_in),0)) / 3600) as hours'))->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();
      }


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
      $responseArr = array();
      $user = false;
      if(isset($args['auth_token'])) {
        $key = getSettingsProp('jwt_key');
        $jwt = $args['auth_token'];
        try {
          $decoded = JWT::decode($jwt, $key, array('HS256'));
          $user = $decoded->data;
        } catch(\Firebase\JWT\ExpiredException $e) {
          $responseArr = array('status'=>false, 'msg'=>'Authorization Error. '.$e->getMessage());
        } catch(\Firebase\JWT\SignatureInvalidException $e){
          $responseArr = array('status'=>false, 'msg'=>'Authorization Error. '.$e->getMessage());
        }
      } elseif(isset($args['auth_code'])) {
        $user = FrcPortal\User::where('signin_pin',hash('sha256',$args['auth_code']))->where('status','=','1')->where('admin','=','1')->first();
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Invalid request');
      }
      if($user != false) {
        $jti = md5(random_bytes(20));
        $key = getSettingsProp('jwt_signin_key');
        $token = array(
          "iss" => getSettingsProp('env_url'),
          "iat" => time(),
          "exp" => time()+60*60*12, //12 hours liftime
          "jti" => $jti,
          'data' => array(
            'signin' => true
          )
        );
        $jwt = JWT::encode($token, $key);
        $responseArr = array('status'=>true, 'type'=>'success', 'msg'=>'Sign In Authorized', 'signin_token'=>$jwt);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    //Deauthorize the current signin token
    $this->post('/deauthorize', function ($request, $response, $args) {
      $responseArr = array('status'=>true, 'type'=>'success', 'msg'=>'Sign In Deauthorized');
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
                  //  $emailInfo = emailSignInOut($user_id,$emailData);
                    $msgData = array(
                      'slack' => array(
                        'title' => 'Sign out',
                        'body' => 'You signed out at '.$emailData['signin_time']
                      ),
                      'email' => array(
                        'subject' => '', //$emailInfo['subject'],
                        'content' =>  '', //$emailInfo['content'],
                        'userData' => $user
                      )
                    );
                    sendUserNotification($user_id, 'sign_in_out', $msgData);
                    $season = FrcPortal\Season::where('year',date('Y'))->first();
                    $users = FrcPortal\User::with(['annual_requirements' => function ($query) use ($season)  {
                      $query->where('season_id', $season->season_id); // fields from comments table,
                    }, 'last_sign_in'])->where('status',true)->get();

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
                  //  $emailInfo = emailSignInOut($user_id,$emailData);
                    $msgData = array(
                      'slack' => array(
                        'title' => 'Sign In',
                        'body' => 'You signed in at '.$emailData['signin_time']
                      ),
                      'email' => array(
                        'subject' => '', //$emailInfo['subject'],
                        'content' =>  '', //$emailInfo['content'],
                        'userData' => $user
                      )
                    );
                    sendUserNotification($user_id, 'sign_in_out', $msgData);
                    $season = FrcPortal\Season::where('year',date('Y'))->first();
                    $users = FrcPortal\User::with(['annual_requirements' => function ($query) use ($season)  {
                      $query->where('season_id', $season->season_id); // fields from comments table,
                    }, 'last_sign_in'])->where('status','1')->get();

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
        } catch(\Firebase\JWT\ExpiredException $e){
          $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Authorization Error. '.$e->getMessage().'.  Please see Mentor.');
        } catch(\Firebase\JWT\SignatureInvalidException $e){
          $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Authorization Error. '.$e->getMessage().'.  Please see Mentor.');
        }
      } else {
        $responseArr = array('status'=>false, 'type'=>'warning', 'msg'=>'Sign in is not authorized at this time and/or on this device. Please see a mentor.');
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
  });
});

















?>

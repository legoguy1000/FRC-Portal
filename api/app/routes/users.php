<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/users', function () {
  $this->get('', function ($request, $response, $args) {
    $users = array();
  	$data = array();
    $searchProperties = array(
      'name' => '',
      'user_type' => '',
      'school' => '',
      'email' => '',
      'gender' => '',
      'status' => true,
    );
    $defaults = array(
      'filter' => '',
      'limit' => 10,
      'order' => '-full_name',
      'page' => 1,
    );
    $inputs = checkSearchInputs($request, $defaults);
    $filter = $inputs['filter'];
    $limit = $inputs['limit'];
    $order = $inputs['order'];
    $page = $inputs['page'];
    $listOnly = $request->getParam('listOnly') !== null && $request->getParam('listOnly')==true ? true:false;
    $search = $request->getParam('search') !== null ? $request->getParam('search'):$searchProperties;
    $returnColumns = $request->getParam('return') !== null && is_array($request->getParam('return')) ? $request->getParam('return'):array();

    $queryArr = array();
    $queryArr2 = array();
    if(!empty($search['user_type'])) {
      $queryArr2[] = array('user_type', '=', $search['user_type']);
    }
    if(!empty($search['status'])) {
      $bool = $search['status'] == 'true' ? '1': '0';
      $queryArr2[] = array('status', '=', $bool);
    //  die($bool );
    }
    $totalNum = 0;
    $users = FrcPortal\User::with('school')->addSelect('schools.school_name','schools.abv')->leftJoin('schools', 'users.school_id', '=', 'schools.school_id')->where($queryArr2);
  	if($filter != '') {
      $users = $users->orHavingRaw('email LIKE ?',array('%'.$filter.'%'));
      $users = $users->orHavingRaw('full_name LIKE ?',array('%'.$filter.'%'));
      $users = $users->orHavingRaw('schools.school_name LIKE ?',array('%'.$filter.'%'));
      $users = $users->orHavingRaw('schools.abv LIKE ?',array('%'.$filter.'%'));
      $users = $users->orHavingRaw('student_grade LIKE ?',array('%'.$filter.'%'));
      $users = $users->orHavingRaw('gender = ?',array('%'.$filter.'%'));
    }
    $totalNum = count($users->get());
    $orderBy = '';
  	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
  	if(in_array($orderCol,array('full_name','fname','lname','email','user_type','gender','school_name'))) {
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
    if(!empty($returnColumns)) {
      $users = $users->map(function ($user) use ($returnColumns) {
          return $user->only($returnColumns);
      });
    }



    $data['data'] = $users;
    $data['total'] = $totalNum;
    $data['maxPage'] = $limit > 0 ? ceil($totalNum/$limit) : 0;
    $data['status'] =true;
    $data['msg'] = $queryArr;
    if($listOnly) {
      $data = $users;
    }

    $response = $response->withJson($data);
    return $response;
  })->setName('Get Users');
  //$this->post('', function ($request, $response, $args) { });
  $this->group('/{user_id:[a-z0-9]{13}}', function () {
    $this->get('', function ($request, $response, $args) {
      $user_id = $args['user_id'];
      $reqsBool = $request->getParam('requirements') !== null && $request->getParam('requirements')==true ? true:false;
      //User passed from middleware
      $user = $request->getAttribute('user');
      $user->load('school');
      //$user = FrcPortal\User::with('school')->find($user_id);
      if($reqsBool) {
        $user->seasons = FrcPortal\Season::with(['annual_requirements' => function ($query) use ($user_id) {
              		$query->where('user_id','=',$user_id); // fields from comments table,
              	}])->get();
      }
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user);
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Get User');
    $this->group('/annualRequirements', function () {
      $this->get('', function ($request, $response, $args) {
        $user_id = $args['user_id'];
        $user = FrcPortal\Season::with(['annual_requirements' => function ($query) use ($user_id) {
                  $query->where('user_id','=',$user_id); // fields from comments table,
                }])->get();
        $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user);
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Get User Annual Requirements');
      $this->get('/{season_id:[a-z0-9]{13}}', function ($request, $response, $args) {
        $user_id = $args['user_id'];
        $season_id = $args['season_id'];
        $user = FrcPortal\Season::with(['annual_requirements' => function ($query) use ($user_id) {
                  $query->where('user_id','=',$user_id); // fields from comments table,
                }])->where('season_id','=',$season_id)->get();
        $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user);
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Get User Annual Requirements by Season');
    });
    $this->group('/eventRequirements', function () {
      $this->get('', function ($request, $response, $args) {
        $user_id = $args['user_id'];
        $user = FrcPortal\Event::with(['event_requirements' => function ($query) use ($user_id) {
                  $query->where('user_id','=',$user_id); // fields from comments table,
                }])->get();
        $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user);
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Get User Event Requirements');
      $this->group('/{event_id:[a-z0-9]{13}}', function () {
        $this->get('', function ($request, $response, $args) {
          $user_id = $args['user_id'];
          $event_id = $args['event_id'];
          $user = FrcPortal\Event::with(['event_requirements.event_cars.passengers', 'event_requirements.event_rooms.users', 'event_requirements.event_time_slots', 'event_requirements.event_food', 'event_requirements' => function ($query) use ($user_id) {
                    $query->where('user_id',$user_id); // fields from comments table,
                  }])->where('event_id',$event_id)->first();
          $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user);
          $response = $response->withJson($responseArr);
          return $response;
        })->setName('Get User Event Requirements by Event');
      });
    });
    $this->put('/pin', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      $user_id = $args['user_id'];

      //User passed from middleware
      $user = $request->getAttribute('user');
      if(!empty($formData['pin'])) {
        insertLogs($level = 'Information', $message = 'PIN update failed. PIN cannot be blank.');
        return badRequestResponse($response, $msg = 'PIN cannot be blank');
      }
      if(!is_numeric($formData['pin'])) {
        insertLogs($level = 'Information', $message = 'PIN update failed. PIN must be numbers only, 0-9.');
        return badRequestResponse($response, $msg = 'PIN must be numbers only, 0-9');
      }
      if(strlen($formData['pin']) < 4 || strlen($formData['pin']) > 8) {
        insertLogs($level = 'Information', $message = 'PIN update failed. PIN must be between 4 to 8 numbers.');
        return badRequestResponse($response, $msg = 'PIN must be between 4 to 8 numbers');
      }
      $user->signin_pin = hash('SHA256', $formData['pin']);
      $user->save();
      insertLogs($level = 'Information', $message = 'PIN updated');
      $responseArr = standardResponse($status = true, $msg = 'PIN has been changed', $data = $user);
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update User Sign In PIN');
    $this->get('/hoursByDate/{year:[0-9]{4}}', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

      $user_id = $args['user_id'];
      $year = $args['year'];

      $data = array('sum'=>array());
      $labels = array();
      $series = array('Sum');

      $dates =  DB::table('meeting_hours AS a')
      ->where('user_id',$user_id)
      ->where(DB::raw('year(a.time_in)'),$year)
      ->select(DB::raw('year(a.time_in) as year, DATE(a.time_in) as date,  ROUND(SUM(time_to_sec(IFNULL(timediff(a.time_out, a.time_in),0)) / 3600),1) as hours'))
      ->groupBy('date')->orderBy('date','ASC')->get();

      if(count($dates) > 0) {
      	foreach($dates as $d) {

      		$year = $d->year;
      		$date = $d->date;
      		$hours = $d->hours;
      	//	$labels[] = $date;
      		$labels[] = date('m/d',strtotime($date));
      		$data['sum'][$date] = $hours;

      	}
      	$data['sum'] = array_values($data['sum']);
      }
      $allData = array(
      	'labels' => $labels,
      	'series' => $series,
      	'data' => array_values($data),
      );
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $allData);
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Get User Hours by Year');
    $this->group('/linkedAccounts', function () {
      $this->get('', function ($request, $response, $args) {
        $userId = FrcPortal\Utilities\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $user = $request->getAttribute('user');
        $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user->oauth()->get());
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Get User Linked Accounts');
      $this->delete('/{auth_id:[a-z0-9]{13}}', function ($request, $response, $args) {
        $userId = FrcPortal\Utilities\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong unlinking the account', $data = null);
        $user = $request->getAttribute('user');
        if($user->deleteLinkedAccount($args['auth_id'])) {
          $responseArr['status'] = true;
          $responseArr['msg'] ='Linked Account Removed';
          $responseArr['data'] = $user->oauth()->get();
        }
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Delete User Linked Account');
    });
    $this->group('/webAuthnCredentials', function () {
      $this->get('', function ($request, $response, $args) {
        $userId = FrcPortal\Utilities\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $user = $request->getAttribute('user');
        $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user->web_authn_credentials()->get());
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Get User Web Authn Credentials');
      $this->delete('/{cred_id:[a-z0-9]{13}}', function ($request, $response, $args) {
        $userId = FrcPortal\Utilities\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong unlinking the account', $data = null);
        $user = $request->getAttribute('user');
        if($user->deleteWebAuthnCredential($args['cred_id'])) {
          $responseArr['status'] = true;
          $responseArr['msg'] ='Device Credential Deleted';
          $responseArr['data'] = $user->web_authn_credentials()->get();
        }
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Delete User Web Authn Credentials');
    });
    $this->group('/notificationPreferences', function () {
      $this->get('', function ($request, $response, $args) {
        $userId = FrcPortal\Utilities\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        $user = $request->getAttribute('user');
        $responseArr = standardResponse($status = true, $msg = '', $data = $user->getNotificationPreferences());
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Get User Notification Preferences');
      $this->put('', function ($request, $response, $args) {
        $userId = FrcPortal\Utilities\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        $user_id = $args['user_id'];
        $user = $request->getAttribute('user');
        if(!empty($formData['method'])) {
          return badRequestResponse($response, $msg = 'Notification method is required');
        }
        if(!empty($formData['type'])) {
          return badRequestResponse($response, $msg = 'Notification type is required');
        }
        if(!array_key_exists('value',$formData)) {
          return badRequestResponse($response, $msg = 'Value is required');
        }
        if($formData['value'] == true) {
          $pref = new FrcPortal\NotificationPreference();
          $pref->user_id = $user_id;
          $pref->method = $formData['method'];
          $pref->type = $formData['type'];
          if($pref->save()) {
            $responseArr['status'] = true;
            $responseArr['msg'] ='Notification Preferences updated';
          }
        } else if($formData['value'] == false) {
          $pref = $user->notification_preferences()->where('method',$formData['method'])->where('type',$formData['type'])->delete();
          if($pref) {
            $responseArr['status'] = true;
            $responseArr['msg'] ='Notification Preferences updated';
          }
        }
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Update User Notification Preferences');
    });
    $this->post('/requestMissingHours', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      $user_id = $args['user_id'];
      //User passed from middleware
      $user = $request->getAttribute('user');
      if($user_id != $userId) {
        insertLogs($level = 'Warning', $message = 'Attempted to submit a missing hours request for '.$user->full_name);
        return unauthorizedResponse($response);
      }

      if(empty($formData['date'])) {
        insertLogs($level = 'Information', $message = 'Missing hours request failed. Date cannot be blank.');
        return badRequestResponse($response, $msg = 'Date cannot be blank');
      }
      if(empty($formData['start_time'])) {
        insertLogs($level = 'Information', $message = 'Missing hours request failed. Start time cannot be blank.');
        return badRequestResponse($response, $msg = 'Start Time cannot be blank');
      }
      if(empty($formData['end_time'])) {
        insertLogs($level = 'Information', $message = 'Missing hours request failed. End time cannot be blank.');
        return badRequestResponse($response, $msg = 'End Time cannot be blank');
      }
      if(empty($formData['comment'])) {
        insertLogs($level = 'Information', $message = 'Missing hours request failed. Comment cannot be blank.');
        return badRequestResponse($response, $msg = 'Comment cannot be blank');
      }
      $date = date('Y-m-d',strtotime($formData['date']));
      $start_time = date('H:i:s',strtotime($formData['start_time']));
      $end_time = date('H:i:s',strtotime($formData['end_time']));
      $request_date = date('Y-m-d H:i:s');

      $request = new FrcPortal\MissingHoursRequest();
      $request->user_id = $user_id;
      $request->time_in = $date.' '.$start_time;
      $request->time_out = $date.' '.$end_time;
      $request->comment = $formData['comment'];
      $request->request_date = $request_date;
      if($request->save()) {
        $responseArr['status'] = true;
        $responseArr['msg'] = 'Request submited';
        insertLogs($level = 'Information', $message = 'Missing hours request submitted');
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Request Missing Hours');
    $this->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

      $user_id = $args['user_id'];
      $selfUpdate = $user_id == $userId;
      $admin = FrcPortal\Utilities\Auth::isAdmin();
      if(!$selfUpdate && !$admin) {
        insertLogs($level = 'Warning', $message = 'User information update failed. Unauthorized user.');
        return unauthorizedResponse($response);
      }
      if(empty($formData['email'])) {
        insertLogs($level = 'Information', $message = 'User profile update failed. Email is required.');
        return badRequestResponse($response,$msg = 'Email is required.');
      }
      $teamDomain = getSettingsProp('team_domain');
      if(!empty($formData['team_email']) && !empty($teamDomain) && preg_match('/[a-z0-9._%+-]+@'.$teamDomain.'$/i', $formData['team_email']) == false) {
        insertLogs($level = 'Information', $message = 'User profile update failed. Team Email must be a "@'.$teamDomain.'" email address.');
        return badRequestResponse($response,$msg = 'Team Email must be a "@'.$teamDomain.'" email address.');
      }
      //User passed from middleware
      $user = $request->getAttribute('user');
      // $user = FrcPortal\User::with('school')->find($user_id);
      $user->fname = $formData['fname'];
      $user->lname = $formData['lname'];
      $user->email = $formData['email'];
      $user->team_email = !empty($formData['team_email']) ? $formData['team_email']:'';
      $user->phone = $formData['phone'];
      $user->user_type = $formData['user_type'];
      $user->gender = $formData['gender'];
      if($formData['user_type'] == 'Student') {
        $user->school_id = $formData['school_id'];
        $user->grad_year = $formData['grad_year'];
      }
      if($selfUpdate && $user->first_login) {
        $user->first_login = false;
      }
      if($admin && !empty($formData['admin'])) {
        $user->admin = $formData['admin'];
      }
      if($admin && !empty($formData['status'])) {
        $user->status = $formData['status'];
      }
      if($user->save()) {
        $user->load('school');
        insertLogs($level = 'Information', $message = $user->full_name.'\'s profile information updated.');
        $responseArr = array('status'=>true, 'msg'=>'User Information Saved', 'data' => $user);
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update User');
    $this->delete('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

      $user_id = $args['user_id'];
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        insertLogs($level = 'Warning', $message = 'User deletetion failed. Unauthorized user.');
        return unauthorizedResponse($response);
      }
      $user = FrcPortal\User::destroy($user_id);
      if($user) {
        insertLogs($level = 'Information', $message = 'User deleted.');
        $responseArr = array('status'=>true, 'msg'=>'User Deleted', 'data' => $user);
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Delete User');
  })->add(function ($request, $response, $next) {
    //User Midddleware to pull season data
    // get the route from the request
    $route = FrcPortal\Utilities\Auth::getRoute();
    if (!$route) {
        // no route matched
        return $next($request, $response);
    }
    $userId = FrcPortal\Utilities\Auth::user()->user_id;
    $args = $route->getArguments();
    $user_id = $args['user_id'];
    $user = FrcPortal\User::find($user_id);
    if(empty($user)) {
      return notFoundResponse($response, $msg = 'User not found');
    }
    if($user_id != $userId && !FrcPortal\Utilities\Auth::isAdmin()) {
      return unauthorizedResponse($response);
    }
    $request = $request->withAttribute('user', $user);
    return $next($request, $response);
  });
});

















?>

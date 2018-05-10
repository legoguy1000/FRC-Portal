<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/users', function () {
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
  		if($filter == strtolower('active')) {
  			$queryArr[] = '(users.status = "1")';
  		} elseif($filter == strtolower('inactive')) {
  			$queryArr[] = '(users.status = "0")';
  		} else {
  		//	$queryArr[] = '(users.fname LIKE '.db_quote('%'.$filter.'%').')';
  		//	$queryArr[] = '(users.lname LIKE '.db_quote('%'.$filter.'%').')';
  			$queryArr[] = '(users.email LIKE "%'.$filter.'%")';
  			$queryArr[] = '(users.user_type LIKE "%'.$filter.'%")';
  			$queryArr[] = '(users.gender = "'.$filter.'")';
  			$queryArr[] = '(full_name LIKE "%'.$filter.'%")';
  			$queryArr[] = '(schools.school_name LIKE "%'.$filter.'%")';
  			$queryArr[] = '(schools.abv LIKE "%'.$filter.'%")';
  			$queryArr[] = '(student_grade LIKE "%'.$filter.'%")';
  		}
  	}
    $totalNum = 0;
  	if(count($queryArr) > 0) {
  		$queryStr = implode(' OR ',$queryArr);
      $users = FrcPortal\User::leftJoin('schools', 'users.school_id', '=', 'schools.school_id')->addSelect('schools.school_name', 'schools.abv')->havingRaw($queryStr)->get();
      $totalNum = count($users);
  	} else {
      $totalNum = FrcPortal\User::count();
    }



    $orderBy = '';
  	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
  	if(in_array($orderCol,array('full_name','fname','lname','email','user_type','gender','school_name'))) {
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
      $users = FrcPortal\User::with('school')->leftJoin('schools', 'users.school_id', '=', 'schools.school_id')->addSelect('schools.school_name', 'schools.abv')->havingRaw($queryStr)->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();
    } else {
      $users = FrcPortal\User::with('school')->leftJoin('schools', 'users.school_id', '=', 'schools.school_id')->addSelect('schools.school_name', 'schools.abv')->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();
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
  //$this->post('', function ($request, $response, $args) { });
  $this->group('/{user_id:[a-z0-9]{13}}', function () {
    $this->get('', function ($request, $response, $args) {
      $user_id = $args['user_id'];
      $reqsBool = $request->getParam('requirements') !== null && $request->getParam('requirements')==true ? true:false;
      $user = FrcPortal\User::with('school')->find($user_id);
      if($reqsBool) {
        $user->seasons = FrcPortal\Season::with(['annual_requirements' => function ($query) use ($user_id) {
              		$query->where('user_id','=',$user_id); // fields from comments table,
              	}])->get();
      }
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->group('/annualRequirements', function () {
      $this->get('', function ($request, $response, $args) {
        $user_id = $args['user_id'];
        $user = FrcPortal\Season::with(['annual_requirements' => function ($query) use ($user_id) {
                  $query->where('user_id','=',$user_id); // fields from comments table,
                }])->get();
        $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user);
        $response = $response->withJson($responseArr);
        return $response;
      });
      $this->get('/{season_id:[a-z0-9]{13}}', function ($request, $response, $args) {
        $user_id = $args['user_id'];
        $season_id = $args['season_id'];
        $user = FrcPortal\Season::with(['annual_requirements' => function ($query) use ($user_id) {
                  $query->where('user_id','=',$user_id); // fields from comments table,
                }])->where('season_id','=',$season_id)->get();
        $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user);
        $response = $response->withJson($responseArr);
        return $response;
      });
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
      });
      $this->get('/{event_id:[a-z0-9]{13}}', function ($request, $response, $args) {
        $user_id = $args['user_id'];
        $event_id = $args['event_id'];
        $user = FrcPortal\Event::with(['event_requirements' => function ($query) use ($event_id) {
                  $query->where('user_id',$user_id); // fields from comments table,
                }])->where('event_id',$event_id)->first();
        $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user);
        $response = $response->withJson($responseArr);
        return $response;
      });
    });
    $this->put('/pin', function ($request, $response, $args) {
      $authToken = $request->getAttribute("token");
      $userId = $authToken['data']->user_id;
      $user_id = $args['user_id'];
      $formData = $request->getParsedBody();
      $responseArr = array(
    		'status' => false,
    		'msg' => 'Something went wrong',
    		'data' => null
    	);
      if($user_id != $userId && !checkAdmin($userId)) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }
      if(!isset($formData['pin']) || $formData['pin'] == '') {
        $responseArr = array('status'=>false, 'msg'=>'PIN cannot be blank');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if(!is_numeric($formData['pin'])) {
        $responseArr = array('status'=>false, 'msg'=>'PIN must bee numbers only 0-9');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if(strlen($formData['pin']) < 4 || strlen($formData['pin']) > 8) {
        $responseArr = array('status'=>false, 'msg'=>'PIN must be between 4 to 8 numbers');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      $user = FrcPortal\User::find($user_id);
      if($user) {
        $currentPIN = $user->signin_pin;
        if($currentPIN != hash('SHA256', $formData['pin'])) {
          $user->signin_pin = hash('SHA256', $formData['pin']);
          if($user->save()) {
            $user->load('schools');
            $responseArr = array('status'=>true, 'msg'=>'PIN has been changed', 'data' => $user);
          }
        } else {
          $responseArr['msg'] = 'PIN must be changed to a different number';
        }
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->get('/linkedAccounts', function ($request, $response, $args) {
      $user_id = $args['user_id'];
      $user = FrcPortal\Oauth::where('user_id',$user_id)->get();
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->get('/notificationPreferences', function ($request, $response, $args) {
      $user_id = $args['user_id'];
      $user = getNotificationPreferencesByUser($user_id);
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('/notificationPreferences', function ($request, $response, $args) {
      $authToken = $request->getAttribute("token");
      $userId = $authToken['data']->user_id;
      $user_id = $args['user_id'];
      $formData = $request->getParsedBody();
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if($user_id != $userId && !checkAdmin($userId)) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }
      if(!isset($formData['method']) || $formData['method'] == '') {
        $responseArr = array('status'=>false, 'msg'=>'Notification method is required');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if(!isset($formData['type']) || $formData['type'] == '') {
        $responseArr = array('status'=>false, 'msg'=>'Notification type is required');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if(!isset($formData['value']) || $formData['value'] == '') {
        $responseArr = array('status'=>false, 'msg'=>'Value is required');
        $response = $response->withJson($responseArr,400);
        return $response;
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
        $pref = FrcPortal\NotificationPreference::where('user_id',$user_id)->where('method',$formData['method'])->where('type',$formData['type'])->delete();
        if($pref) {
          $responseArr['status'] = true;
          $responseArr['msg'] ='Notification Preferences updated';
        }
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('', function ($request, $response, $args) {
      $authToken = $request->getAttribute("token");
      $userId = $authToken['data']->user_id;
      $user_id = $args['user_id'];
      $formData = $request->getParsedBody();
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if($user_id != $userId && !checkAdmin($userId)) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }

      $user = FrcPortal\User::find($user_id);
      $user->fname = $formData['fname'];
      $user->lname = $formData['lname'];
      $user->email = $formData['email'];
      $user->team_email = $formData['team_email'];
      $user->phone = $formData['phone'];
      $user->user_type = $formData['user_type'];
      $user->gender = $formData['gender'];
      if($formData['user_type'] == 'Student') {
        $user->school_id = $formData['school_id'];
        $user->grad_year = $formData['grad_year'];
      }
      $user->admin = $formData['admin'];
      $user->status = $formData['status'];
      if($user->save()) {
        $responseArr = array('status'=>true, 'msg'=>'User Information Saved', 'data' => $user);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong', 'data' => $user);
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->delete('', function ($request, $response, $args) {
      $authToken = $request->getAttribute("token");
      $userId = $authToken['data']->user_id;
      $user_id = $args['user_id'];
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
      $user = FrcPortal\User::destroy($user_id);
      if($user) {
        $responseArr = array('status'=>true, 'msg'=>'User Deleted', 'data' => $user);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong', 'data' => $user);
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
  });
});

















?>

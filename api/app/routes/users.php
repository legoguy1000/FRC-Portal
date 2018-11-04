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

    $queryArr = array();
    $queryArr2 = array();
    if(isset($search['user_type']) && $search['user_type'] != '') {
      $queryArr2[] = array('user_type', '=', $search['user_type']);
    }
    if(isset($search['status']) && $search['status'] != '') {
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
        });
  /*      $this->put('/rooms', function ($request, $response, $args) {
          $authToken = $request->getAttribute("token");
          $userId = $authToken['data']->user_id;
          $formData = $request->getParsedBody();
          $responseArr = array(
            'status' => false,
            'msg' => 'Something went wrong',
            'data' => null
          );
          $user_id = $args['user_id'];
          $event_id = $args['event_id'];
          if($user_id != $userId && !checkAdmin($userId)) {
            $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
            $response = $response->withJson($responseArr,403);
            return $response;
          }
          if(!isset($formData['room_id']) || $formData['room_id'] == '') {
            $responseArr = array('status'=>false, 'msg'=>'Room ID cannot be blank');
            $response = $response->withJson($responseArr,400);
            return $response;
          }
          $room_id = $formData['room_id'];
          $user = FrcPortal\User::find($user_id);
          $room = FrcPortal\EventRoom::where('room_id',$room_id)->where('event_id',$event_id)->first();
          if(is_null($room)) {
            $responseArr['msg'] = 'Invalid Room ID';
            $response = $response->withJson($responseArr);
            return $response;
          }
          if($room->user_type != $user->user_type) {
            $responseArr['msg'] = 'Room User Type does not match User Type';
            $response = $response->withJson($responseArr);
            return $response;
          }
          if($room->user_type != 'Mentor' && $room->gender != $user->gender) {
            $responseArr['msg'] = 'Room Gender does not match User Gender';
            $response = $response->withJson($responseArr);
            return $response;
          }
          $roomUpdate = FrcPortal\EventRequirement::updateOrCreate(['event_id' => $event_id, 'user_id' => $user_id],['room_id'=>$room_id]);
          $responseArr['status'] = true;
          $responseArr['msg'] = 'Room Selected';
          $rooms = getEventRoomList($event_id);
          $responseArr['data'] = $rooms['data'];
          $responseArr['myRoom'] = FrcPortal\EventRoom::with(['users'])->find($room_id);
          $response = $response->withJson($responseArr);
          return $response;
        });
      }); */
    });
  /*  $this->group('/eventTimeSlots/{time_slot_id:[a-z0-9]{13}}', function () {
        $this->put('', function ($request, $response, $args) {
          $authToken = $request->getAttribute("token");
          $userId = $authToken['data']->user_id;
          $formData = $request->getParsedBody();
          $responseArr = array(
            'status' => false,
            'msg' => 'Something went wrong',
            'data' => null
          );
          $user_id = $args['user_id'];
          $time_slot_id = $args['time_slot_id'];
          if($user_id != $userId && !checkAdmin($userId)) {
            $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
            $response = $response->withJson($responseArr,403);
            return $response;
          }

          $timeSlot = FrcPortal\EventTimeSlot::where('time_slot_id',$time_slot_id)->first();
          if(!is_null($timeSlot)) {
            $event_id = $timeSlot->event_id;
            $reqUpdate = FrcPortal\EventRequirement::firstOrNew(['event_id' => $event_id, 'user_id' => $user_id]);
            if($reqUpdate->save()) {
              $ereq_id = $reqUpdate->ereq_id;
              $timeSlot->registrations()->toggle($ereq_id);
              $slots = getEventTimeSlotList($event_id);
              $responseArr['status'] = true;
              $responseArr['msg'] = 'Time Slot Updated';
              $responseArr['data'] = $slots['data'];
            }
          }
          $response = $response->withJson($responseArr);
          return $response;
        });
      }); */
    });
    $this->put('/pin', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      $user_id = $args['user_id'];
      if($user_id != $userId && !FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
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
      $user = FrcPortal\User::find($user_id)->updateSignInPin($formData['pin']);
      $response = $response->withJson($user);
      return $response;
    });
    $this->get('/hoursByDate/{year:[0-9]{4}}', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

      $user_id = $args['user_id'];
      $year = $args['year'];
      if($user_id != $userId && !FrcPortal\Auth::isAdmin()) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }
      $data = array('sum'=>array());
      $labels = array();
      $series = array('Sum');

  /*    $query = 'SELECT year(a.time_in) as year, DATE(a.time_in) as date,  ROUND(SUM(time_to_sec(IFNULL(timediff(a.time_out, a.time_in),0)) / 3600),1) as hours FROM `meeting_hours` a
      WHERE user_id = :uid AND year(a.time_in) = :year
      GROUP BY date
      ORDER BY date ASC';
      $dates = DB::select( DB::raw($query), array(
          'uid' => $user_id,
          'year' => $year,
       )); */
      $dates =  DB::table('meeting_hours')
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
    });
    $this->get('/linkedAccounts', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

      $user_id = $args['user_id'];
      if($user_id != $userId && !FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      $user = FrcPortal\Oauth::where('user_id',$user_id)->get();
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->group('/notificationPreferences', function () {
      $this->get('', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

        $user_id = $args['user_id'];
        if($user_id != $userId && !FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
        }
        $user = FrcPortal\User::find($user_id);
        $preferences = $user->getNotificationPreferences();
        $responseArr = array('status'=>true, 'msg'=>'', 'data' => $preferences);
        $response = $response->withJson($responseArr);
        return $response;
      });
      $this->put('', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

        $user_id = $args['user_id'];
        if($user_id != $userId && !FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
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
        if(!array_key_exists('value',$formData)) {
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
    });
    $this->post('/requestMissingHours', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

      $user_id = $args['user_id'];
      if($user_id != $userId) {
        return unauthorizedResponse($response);
      }

      if(!isset($formData['start_time']) || $formData['start_time'] == '') {
        $responseArr = array('status'=>false, 'msg'=>'Start Time cannot be blank');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if(!isset($formData['end_time']) || $formData['end_time'] == '') {
        $responseArr = array('status'=>false, 'msg'=>'End Time cannot be blank');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if(!isset($formData['comment']) || $formData['comment'] == '') {
        $responseArr = array('status'=>false, 'msg'=>'Comment cannot be blank');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      $start_time = date('Y-m-d H:i:s',strtotime($formData['start_time']));
      $end_time = date('Y-m-d H:i:s',strtotime($formData['end_time']));;
      $request_date = date('Y-m-d H:i:s');

      $request = new FrcPortal\MissingHoursRequest();
      $request->user_id = $user_id;
      $request->time_in = $start_time;
      $request->time_out = $end_time;
      $request->comment = $formData['comment'];
      $request->request_date = $request_date;
      if($request->save()) {
        $responseArr['status'] = true;
        $responseArr['msg'] = 'Request submited';
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

      $user_id = $args['user_id'];
      $selfUpdate = $user_id == $userId;
      $admin = FrcPortal\Auth::isAdmin();
      if( !$selfUpdate && !$admin) {
        return unauthorizedResponse($response);
      }

      $user = FrcPortal\User::with('school')->find($user_id);
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
      if($selfUpdate && $user->first_login) {
        $user->first_login = false;
      }
      if($admin && isset($formData['admin'])) {
        $user->admin = $formData['admin'];
      }
      if($admin && isset($formData['status'])) {
        $user->status = $formData['status'];
      }
      if($user->save()) {
        $responseArr = array('status'=>true, 'msg'=>'User Information Saved', 'data' => $user);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong', 'data' => $user);
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->delete('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

      $user_id = $args['user_id'];
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
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

<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/events', function () {
  $this->get('', function ($request, $response, $args) {
    $events = array();
  	$data = array();

    $filter = $request->getParam('filter') !== null ? $request->getParam('filter'):'';
    $limit = $request->getParam('limit') !== null ? $request->getParam('limit'):10;
    $order = $request->getParam('order') !== null ? $request->getParam('order'):'-year';
    $page = $request->getParam('page') !== null ? $request->getParam('page'):1;
    $listOnly = $request->getParam('listOnly') !== null && $request->getParam('listOnly')==true ? true:false;


    $totalNum = 0;
    $queryArr = array();
  	$queryStr = '';
  	if($filter != '') {
      $queryArr[] = '(events.name LIKE "%'.$filter.'%")';
      $queryArr[] = '(events.type LIKE "%'.$filter.'%")';
      $queryArr[] = '(events.event_start LIKE "%'.$filter.'%")';
      $queryArr[] = '(events.event_end LIKE "%'.$filter.'%")';
      //$queryArr[] = '(seasons.game_name LIKE "%'.$filter.'%")';
      //$queryArr[] = '(seasons.year LIKE "%'.$filter.'%")';
      //Date Filters
      $queryArr[] = '(YEAR(events.event_start) LIKE "%'.$filter.'%")';
      $queryArr[] = '(MONTHNAME(events.event_start) LIKE "%'.$filter.'%")';
      $queryArr[] = '(MONTHNAME(events.event_end) LIKE "%'.$filter.'%")';
  	}

  	if(count($queryArr) > 0) {
  		$queryStr = implode(' OR ',$queryArr);
      $events = FrcPortal\Event::havingRaw($queryStr)->get();
      $totalNum = count($events);
  	} else {
      $totalNum = FrcPortal\Event::count();
    }

    $orderBy = '';
  	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
  	if(in_array($orderCol,array('game_name','year','event_start','event_end', 'name', 'type'))) {
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
      $events = FrcPortal\Event::havingRaw($queryStr)->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();
    } else {
      $events = FrcPortal\Event::orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();
    }

    $data['data'] = $events;
    $data['total'] = $totalNum;
    $data['maxPage'] = $limit > 0 ? ceil($totalNum/$limit) : 0;
    $data['status'] = true;
    $data['msg'] = '';
    if($listOnly) {
      $data = $events;
    }
    $response = $response->withJson($data);
    return $response;
  });
  $this->get('/searchGoogleCalendar', function ($request, $response, $args) {
    $calendar = getIniProp('google_calendar_id');
    $api_key = getIniProp('google_api_key');
    $optParams = array();
    if($request->getParam('q') != null && $request->getParam('q') != '' && $request->getParam('q') != 'null' && $request->getParam('q') != 'undefined') {
    	$q = trim($request->getParam('q'));
    	$optParams['q'] = $q;
    }
    if($request->getParam('timeMax') != null && $request->getParam('timeMax') != '' && $request->getParam('timeMax') != 'null' && $request->getParam('timeMax') != 'undefined') {
    	$timeMax = date('c', strtotime($request->getParam('timeMax')));
    	if(is_numeric($request->getParam('timeMax'))) {
    		$timeMax = date('c',$request->getParam('timeMax')/1000);
    	}
    	$optParams['timeMax'] = $timeMax;
    }
    $optParams['timeMin'] = date('c',strtotime('-6 months'));
    if($request->getParam('timeMin') != null && $request->getParam('timeMin') != '' && $request->getParam('timeMin') != 'null' && $request->getParam('timeMin') != 'undefined') {
    	$timeMin = date('c', strtotime($request->getParam('timeMin')));
    	if(is_numeric($request->getParam('timeMin'))) {
    		$timeMin = date('c',$request->getParam('timeMin')/1000);
    	}
    	$optParams['timeMin'] = $timeMin;
    }
    $optParams['maxResults'] = 2500;
    $optParams['orderBy'] = 'startTime';
    $optParams['singleEvents'] = true;
    $allEvents = array();
    try {
      $client = new Google_Client();
      $client->setDeveloperKey($api_key);
      $service = new Google_Service_Calendar($client);
      $events = $service->events->listEvents($calendar, $optParams);
      while(true) {
        foreach ($events->getItems() as $event) {
      		if($event->status == 'confirmed') {
      			$temp = array(
      				'google_event' => $event,
      				'name' => $event->summary,
      				'location' => $event->location,
      				'google_cal_id' => $event->id,
      				'allDay' => false,
      				'event_start' => null,
      				'event_end' => null,
      				'event_start_unix' => null,
      				'event_end_unix' => null,
      				'event_start_iso' => null,
      				'event_end_iso' => null,
      				'details' => $event->description,
      			);
      			if(empty($event->start->dateTime)) {
      				$temp['allDay'] = true;
      				$temp['event_start'] = $event->start->date.' 00:00:00';
      				$temp['event_end'] = $event->end->date.' 23:59:59';
      			} else {
      				$temp['event_start'] = date('Y-m-d H:i:s', strtotime($event->start->dateTime));
      				$temp['event_end'] =date('Y-m-d H:i:s', strtotime($event->end->dateTime));
      			}
      			$temp['event_start_unix'] = strtotime($temp['event_start']);
      			$temp['event_end_unix'] = strtotime($temp['event_end']);
      			$temp['event_start_iso'] = date('c',strtotime($temp['event_start']));
      			$temp['event_end_iso'] = date('c',strtotime($temp['event_end']));
          	$allEvents[] = $temp;
      		}
        }
        $pageToken = $events->getNextPageToken();
        if ($pageToken) {
          $optParams = array('pageToken' => $pageToken);
          $events = $service->events->listEvents($calendar, $optParams);
        } else {
          break;
        }
      }
    } catch (Exception $e) {
      $result['msg'] = 'Something went wrong searching Google Calendar';
    }
    $data = array(
    	'results'=>$allEvents,
    	'count'=>count($allEvents)
    );
    $responseArr = array('status'=>true, 'msg'=>'', 'data' => $data);
    $response = $response->withJson($responseArr);
    return $response;
  });
  $this->group('/{event_id:[a-z0-9]{13}}', function () {
    $this->get('', function ($request, $response, $args) {
      $event_id = $args['event_id'];
      $reqsBool = $request->getParam('requirements') !== null && $request->getParam('requirements')==true ? true:false;
      $event = FrcPortal\Event::with('poc')->find($event_id);
      if($reqsBool) {
        $event->users = FrcPortal\User::with(['event_requirements' => function ($query) use ($event_id) {
                        		$query->where('event_id','=',$event_id);
                          }])->get();
      }
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $event);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->get('/eventRequirements', function ($request, $response, $args) {
      $event_id = $args['event_id'];
      $event = FrcPortal\User::with(['event_requirements' => function ($query) use ($event_id) {
                          $query->where('event_id','=',$event_id);
                        },'event_requirements.event_rooms','event_requirements.event_cars'])->get();
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $event);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->get('/cars', function ($request, $response, $args) {
      $event_id = $args['event_id'];
      $responseArr = getEventCarList($event_id);
      $response = $response->withJson($responseArr);
      return $response;
    });

    $this->put('/cars', function ($request, $response, $args) {
      //$authToken = checkToken(true,true);
      //$user_id = $authToken['data']['user_id'];
      //checkAdmin($user_id, $die = true);
      $event_id = $args['event_id'];
      $formData = $request->getParsedBody();
      if(!isset($formData['cars']) || !is_array($formData['cars']) || empty($formData['cars'])) {
        $responseArr = array('status'=>false, 'msg'=>'Invalid request');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      $cars = FrcPortal\EventCar::where('event_id',$event_id)->get();
      foreach($cars as $car) {
        $car_id = $car->car_id;
        $carArr = $formData['cars'][$car_id];
        $userArr = array_column($carArr, 'user_id');
        if(!empty($userArr) && count($userArr) <= $car['car_space']) {
          $users = FrcPortal\EventRequirement::where('event_id',$event_id)->whereIn('user_id', $userArr)->update(['car_id' => $car_id]);
      	}
      }
      //Not Assigned a car
      $carArr = $formData['cars']['non_select'];
      $userArr = array_column($carArr, 'user_id');
      if(!empty($userArr)) {
        $users = FrcPortal\EventRequirement::where('event_id',$event_id)->whereIn('user_id', $userArr)->update(['car_id' => null]);
      }
      $event = FrcPortal\User::with(['event_requirements' => function ($query) use ($event_id) {
                          $query->where('event_id','=',$event_id);
                        },'event_requirements.event_rooms','event_requirements.event_cars'])->get();
      $responseArr = array('status'=>true, 'msg'=>'Event car list updated', 'data'=>$event);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->get('/rooms', function ($request, $response, $args) {
      $event_id = $args['event_id'];
      $responseArr = getEventRoomList($event_id);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->post('/rooms', function ($request, $response, $args) {
      $event_id = $args['event_id'];
      $formData = $request->getParsedBody();
      if(!isset($formData['event_id']) || $formData['event_id'] == '') {
      	//die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Event ID cannot be blank!')));
      }
      if(!isset($formData['user_type']) || $formData['user_type'] == '') {
      	//die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'User type cannot be blank!')));
      }
      if(!isset($formData['gender']) || $formData['gender'] == '' && $formData['user_type'] != 'Mentor') {
      	//die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Gender cannot be blank!')));
      }
      $room = new FrcPortal\EventRoom();
      $room->event_id = $formData['event_id'];
      $room->user_type = $formData['user_type'];
      $room->gender = $formData['gender'];
      if($room->save()) {
        $responseArr = getEventRoomList($event_id);
        $responseArr['msg'] = 'New room added';
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Event went wrong', 'data' => null);
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('/rooms', function ($request, $response, $args) {
      //$authToken = checkToken(true,true);
      //$user_id = $authToken['data']['user_id'];
      //checkAdmin($user_id, $die = true);
      $event_id = $args['event_id'];
      $formData = $request->getParsedBody();
      if(!isset($formData['rooms']) || !is_array($formData['rooms']) || empty($formData['rooms'])) {
        $responseArr = array('status'=>false, 'msg'=>'Invalid request');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      $rooms = FrcPortal\EventRoom::where('event_id',$event_id)->get();
      foreach($rooms as $room) {
        $room_id = $room->room_id;
        $roomArr = $formData['rooms'][$room_id];
        $userArr = array_column($roomArr, 'user_id');
        if(!empty($userArr) && count($userArr) <= 4) {
          $users = FrcPortal\EventRequirement::where('event_id',$event_id)->whereIn('user_id', $userArr)->update(['room_id' => $room_id]);
        }
      }
      //Not Assigned a car
      $roomArr = $formData['rooms']['non_select'];
      $userArr = array_column($roomArr, 'user_id');
      if(!empty($userArr)) {
        $users = FrcPortal\EventRequirement::where('event_id',$event_id)->whereIn('user_id', $userArr)->update(['room_id' => null]);
      }
      $event = FrcPortal\User::with(['event_requirements' => function ($query) use ($event_id) {
                          $query->where('event_id','=',$event_id);
                        },'event_requirements.event_rooms','event_requirements.event_cars'])->get();
      $responseArr = array('status'=>true, 'msg'=>'Event room list updated', 'data'=>$event);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->delete('/rooms/{room_id:[a-z0-9]{13}}', function ($request, $response, $args) {
      //$authToken = checkToken(true,true);
      //$user_id = $authToken['data']['user_id'];
      //checkAdmin($user_id, $die = true);
      $event_id = $args['event_id'];
      $room_id = $args['room_id'];
      $event = FrcPortal\EventRoom::where('event_id',$event_id)->where('room_id',$room_id)->delete();
      if($event) {
        $rooms = getEventRoomList($event_id);
        if($rooms['status'] != false) {
          $responseArr = array('status'=>true, 'msg'=>'Room Deleted', 'data' => $rooms['data']);
        } else {
          $responseArr = $rooms;
        }
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong', 'data' => $event);
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('', function ($request, $response, $args) {
      //$authToken = checkToken(true,true);
      //$user_id = $authToken['data']['user_id'];
      //checkAdmin($user_id, $die = true);
      $event_id = $args['event_id'];
      $formData = $request->getParsedBody();
      $event = FrcPortal\Event::find($event_id);

      $event->type = $formData['type'];
      $event->poc = isset($formData['poc']['user_id']) && $formData['poc']['user_id'] != '' ? $formData['poc']['user_id']:null;

      if($event->save()) {
        $event->load('poc');
        $responseArr = array('status'=>true, 'msg'=>'Event Information Saved', 'data' => $event);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Event went wrong', 'data' => $event);
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('/syncGoogleCalEvent', function ($request, $response, $args) {
      //$authToken = checkToken(true,true);
      //$user_id = $authToken['data']['user_id'];
      //checkAdmin($user_id, $die = true);
      $event_id = $args['event_id'];
      $responseArr = syncGoogleCalendarEvent($event_id);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('/toggleEventReqs', function ($request, $response, $args) {
      //$authToken = checkToken(true,true);
      //$user_id = $authToken['data']['user_id'];
      //checkAdmin($user_id, $die = true);
      $event_id = $args['event_id'];
      $formData = $request->getParsedBody();
      if(!isset($formData['users']) || !is_array($formData['users']) || empty($formData['users'])) {
        $responseArr = array('status'=>false, 'msg'=>'Please select at least 1 user');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if(!isset($formData['requirement']) || $formData['requirement'] == '' || !in_array($formData['requirement'],array('registration','permission_slip','payment','food'))) {
        $responseArr = array('status'=>false, 'msg'=>'Invalid requirement');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      $event = FrcPortal\Event::find($event_id);
      $array = array();
      $req = $formData['requirement'];
      $users = $formData['users'];
      foreach($users as $user) {
        $user_id = $user['user_id'];
        $cur = isset($user['event_requirements'][$req]) ? $user['event_requirements'][$req] : false;
        $new = !$cur;
        $reqUpdate = FrcPortal\EventRequirement::updateOrCreate(['event_id' => $event_id, 'user_id' => $user_id], [$req => $new]);
      }
      $season = FrcPortal\User::with(['event_requirements' => function ($query) use ($event_id) {
                          $query->where('event_id',$event_id);
                        }])->get();
      $responseArr = array('status'=>true, 'msg'=>'Event Requirements Updated', 'data' => $season);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->post('/register', function ($request, $response, $args) {
      //$authToken = checkToken(true,true);
      //$loggedInUser = $authToken['data']['user_id'];
      //$userFullName = $authToken['data']['full_name'];
      //checkAdmin($user_id, $die = true);
      $responseArr = array(
    		'status' => false,
    		'msg' => '',
    		'data' => null
    	);

      $event_id = $args['event_id'];
      $formData = $request->getParsedBody();
      if(!is_bool($formData['registration'])) {
        $responseArr = array('status'=>false, 'msg'=>'Invalid Request, no registration option.');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      //$user_id = $loggedInUser;
      //if(isset($formData['user_id']) && checkAdmin($loggedInUser, $die = false)) {
      	$user_id = $formData['user_id'];
      //}
      $user =  FrcPortal\User::find($user_id);
      $user_type = $user->user_type;

      $registrationBool = (bool) $formData['registration'];
      $event = FrcPortal\Event::find($event_id);
      if($registrationBool) {
        if(time() > $event->event_start_unix) {
          $responseArr = array('status'=>false, 'msg'=>'Registration is closed. Event has already started.');
          $response = $response->withJson($responseArr,400);
          return $response;
      	} elseif($event->registration_date_unix != null && (time() > $event->registration_date_unix)) {
            $responseArr = array('status'=>false, 'msg'=>'Registration is closed. Registration deadline was '.date('F j, Y g:m A',$event->registration_date_unix).'.');
            $response = $response->withJson($responseArr,400);
            return $response;
      	}
        $reqUpdate = FrcPortal\EventRequirement::updateOrCreate(['event_id' => $event_id, 'user_id' => $user_id], ['registration' => true, 'comments' => $formData['comments']]);
        $can_drive = (bool) $formData['can_drive'];
        $drivers_req = (bool) $event->drivers_required;
      	if($user_type == 'Mentor' && $can_drive && $drivers_req) {
          $eventCarUpdate = FrcPortal\EventCar::updateOrCreate(['event_id' => $event_id, 'user_id' => $user_id], ['car_space' => $formData['car_space']]);
          $reqUpdate->can_drive = true;
          $reqUpdate->car_id = $eventCarUpdate->car_id;
          $reqUpdate->save();
        } else {
          $eventCarUpdate = FrcPortal\EventCar::where('event_id',$event_id)->where('user_id',$user_id)->delete();
          $reqUpdate->can_drive = false;
          $reqUpdate->car_id = null;
          $reqUpdate->save();
        }
        $msg = ($user_id != $loggedInUser ? $user->full_name.' ':'').'Registered';
        //notify event POC
        if(!is_null($event->poc_id)){
          $responseArr['msg'] = $user->full_name.' registered for '.$event->name;
          if($user_id != $loggedInUser) {
            $responseArr['msg'] = $userFullName.' registered '.$user->full_name.' for '.$event->name;
          }
          slackMessageToUser($event->poc_id, $msg);
          $eventRequirements = array();
        }
      } else {
        $reqUpdate = FrcPortal\EventRequirement::where('event_id',$event_id)->where('user_id',$user_id)->delete();
        $eventCarUpdate = FrcPortal\EventCar::where('event_id',$event_id)->where('user_id',$user_id)->delete();
        $responseArr['msg'] = ($user_id != $loggedInUser ? $user->full_name.' ':'').'Unregistered';
      }
      $eventReqs = FrcPortal\User::with(['event_requirements' => function ($query) use ($event_id) {
                          $query->where('event_id','=',$event_id);
                        },'event_requirements.event_rooms','event_requirements.event_cars'])->get();
      $responseArr = array('status'=>true, 'type'=>'success', 'msg'=>$msg, 'data'=>$eventReqs);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->delete('', function ($request, $response, $args) {
      //$authToken = checkToken(true,true);
      //$user_id = $authToken['data']['user_id'];
      //checkAdmin($user_id, $die = true);
      $event_id = $args['event_id'];
      $event = FrcPortal\Event::destroy($event_id);
      if($event) {
        $responseArr = array('status'=>true, 'msg'=>'Event Deleted', 'data' => $event);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong', 'data' => $event);
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
  });
  $this->post('', function ($request, $response, $args) {
    //$authToken = checkToken(true,true);
    //$user_id = $authToken['data']['user_id'];
    //checkAdmin($user_id, $die = true);
    $formData = $request->getParsedBody();
    $responseArr = array(
      'status'=>false,
      'msg'=> '',
      'data' => null
    );
    if(!isset($formData['name']) || $formData['name'] == '') {
      $responseArr['msg'] = 'Name cannot be blank';
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(!isset($formData['type']) || $formData['type'] == '') {
      $responseArr['msg'] = 'Event type cannot be blank';
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(!isset($formData['event_start']) || $formData['event_start'] == '') {
      $responseArr['msg'] = 'Start Date cannot be blank';
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(!isset($formData['event_end']) || $formData['event_end'] == '') {
      $responseArr['msg'] = 'End Date cannot be blank';
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(strtotime($formData['event_start']) >= strtotime($formData['event_end'])) {
      $$responseArr['msg'] = 'Start Date must be before End Date';
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(!isset($formData['google_cal_id']) || $formData['google_cal_id'] == '') {
      $responseArr['msg'] = 'Invalid Google calendar ID';
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    $events = FrcPortal\Event::where('google_cal_id', $formData['google_cal_id'])->count();
    if($events == 0) {
      $event = new FrcPortal\Event();
      $event->google_cal_id = $formData['google_cal_id'];
      $event->name = $formData['name'];
      $event->type = $formData['type'];
      $event->event_start = $formData['event_start'];
      $event->event_end = $formData['event_end'];
      $event->details = isset($formData['details']) && !is_null($formData['details']) ? $formData['details']:'';
      $event->location = isset($formData['location']) && !is_null($formData['location']) ? $formData['location']:'';
      $event->payment_required = isset($formData['requirements']['payment']) && $formData['requirements']['payment'] ? true:false;
      $event->permission_slip_required = isset($formData['requirements']['permission_slip']) && $formData['requirements']['permission_slip'] ? true:false;
      $event->food_required = isset($formData['requirements']['food']) && $formData['requirements']['food'] ? true:false;
      $event->room_required = isset($formData['requirements']['room']) && $formData['requirements']['room'] ? true:false;
      $event->drivers_required = isset($formData['requirements']['drivers']) && $formData['requirements']['drivers'] ? true:false;
      if($event->save()) {
        $limit = 10;
        $totalNum = FrcPortal\Event::count();
        $events = FrcPortal\Event::orderBy('event_start','DESC')->limit($limit)->get();
        $data = array();
        $data['results'] = $events;
        $data['total'] = $totalNum;
        $data['maxPage'] = ceil($totalNum/$limit);
        $responseArr = array('status'=>true, 'msg'=>$event->name.' created', 'data'=>$data);
      } else {
        $responseArr['msg'] = 'Something went wrong';
      }
    } else {
      $responseArr['msg'] = $event->name.' already exists';
    }
    $response = $response->withJson($responseArr);
    return $response;
  });
});

















?>

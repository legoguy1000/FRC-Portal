<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/events', function () {
  //Get all events
  $this->get('', function ($request, $response, $args) {
    $events = array();
  	$data = array();

    $defaults = array(
      'filter' => '',
      'limit' => 10,
      'order' => '-year',
      'page' => 1,
    );
    $inputs = checkSearchInputs($request, $defaults);
    $filter = $inputs['filter'];
    $limit = $inputs['limit'];
    $order = $inputs['order'];
    $page = $inputs['page'];
    $listOnly = $request->getParam('listOnly') !== null && $request->getParam('listOnly')==true ? true:false;

    $totalNum = 0;
    $events = new FrcPortal\Event();
  	if($filter != '') {
      $events = $events->orHavingRaw('name LIKE ?',array('%'.$filter.'%'));
      $events = $events->orHavingRaw('type LIKE ?',array('%'.$filter.'%'));
      $events = $events->orHavingRaw('event_start LIKE ?',array('%'.$filter.'%'));
      $events = $events->orHavingRaw('event_end LIKE ?',array('%'.$filter.'%'));
      $events = $events->orHavingRaw('YEAR(events.event_start) LIKE ?',array('%'.$filter.'%'));
      $events = $events->orHavingRaw('MONTHNAME(events.event_start) LIKE ?',array('%'.$filter.'%'));
      $events = $events->orHavingRaw('MONTHNAME(events.event_end) LIKE ?',array('%'.$filter.'%'));
    }
    $totalNum = count($events->get());

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
    $events = $events->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();

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
  //Search Google Calendar for events
  $this->get('/searchGoogleCalendar', function ($request, $response, $args) {
    $calendar = getSettingsProp('google_calendar_id');
    $api_key = getSettingsProp('google_api_key');
    if(!isset($api_key) || $api_key == '') {
      $responseArr = array('status'=>false, 'msg'=>'Google API Key cannot be blank.  Please got to Site Settings '.html_entity_decode('&#8594;').' Other Settings to set the API Key.');
      $response = $response->withJson($responseArr);
      return $response;
    }
    if(!isset($calendar) || $calendar == '') {
      $responseArr = array('status'=>false, 'msg'=>'Google Calendar ID cannot be blank.  Please got to Site Settings '.html_entity_decode('&#8594;').' Other Settings to set the Google Calendar ID.');
      $response = $response->withJson($responseArr);
      return $response;
    }
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
            $temp = formatGoogleCalendarEventData($event);
          	$temp['poc'] = FrcPortal\User::where('email',$event->creator->email)->orWhere('team_email',$event->creator->email)->first();
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
    //Get Event
    $this->get('', function ($request, $response, $args) {
      $event_id = $args['event_id'];
      $reqsBool = $request->getParam('requirements') !== null && $request->getParam('requirements')==true ? true:false;
      $withArr = array('poc');
      if($request->getParam('event_rooms') !== null && $request->getParam('event_rooms')==true) {
        $withArr[] = 'event_rooms.users';
      }
      if($request->getParam('event_cars') !== null && $request->getParam('event_cars')==true) {
        $withArr[] = 'event_cars';
      }
      if($request->getParam('event_time_slots') !== null && $request->getParam('event_time_slots')==true) {
        $withArr[] = 'event_time_slots.registrations.user';
      }
      if($request->getParam('users') !== null && $request->getParam('users')==true) {
        $withArr['registered_users'] = function ($query) use ($event_id) {
          $query->where('registration',true);
        };
      }
      $event = FrcPortal\Event::with($withArr)->find($event_id);
      if($reqsBool) {
        $event->users = getUsersEventRequirements($event_id);
      }
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $event);
      $response = $response->withJson($responseArr);
      return $response;
    });
    //Get Event Requirements
    $this->get('/eventRequirements', function ($request, $response, $args) {
      $event_id = $args['event_id'];
      $event = getUsersEventRequirements($event_id);
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $event);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->group('/cars', function () {
      //Get Event Cars
      $this->get('', function ($request, $response, $args) {
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        $event_id = $args['event_id'];
        try {
          $responseArr['data'] = getEventCarList($event_id);
          $responseArr['status'] = true;
          $responseArr['msg'] = '';
        } catch (Exception $e) {
      		$result['msg'] = handleExceptionMessage($e);
      	}
        $response = $response->withJson($responseArr);
        return $response;
      });
      //Update Event Car passengers
      $this->put('', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
        }


        $event_id = $args['event_id'];
        $formData = $request->getParsedBody();
        if(!isset($formData['cars']) || !is_array($formData['cars']) || empty($formData['cars'])) {
          return badRequestResponse($response);
        }
        $cars = FrcPortal\EventCar::where('event_id',$event_id)->get();
        foreach($cars as $car) {
          $car_id = $car->car_id;
          $carArr = $formData['cars'][$car_id];
          $userArr = array_column($carArr, 'user_id');
          if(!empty($userArr) && count($userArr) <= $car['car_space']) {
            $events = FrcPortal\EventRequirement::where('event_id',$event_id)->whereIn('user_id', $userArr)->update(['car_id' => $car_id]);
        	}
        }
        //Not Assigned a car
        $carArr = $formData['cars']['non_select'];
        $userArr = array_column($carArr, 'user_id');
        if(!empty($userArr)) {
          $events = FrcPortal\EventRequirement::where('event_id',$event_id)->whereIn('user_id', $userArr)->update(['car_id' => null]);
        }
        $event = FrcPortal\User::with(['event_requirements' => function ($query) use ($event_id) {
                            $query->where('event_id','=',$event_id);
                          },'event_requirements.event_rooms','event_requirements.event_cars'])->get();
        $responseArr = array('status'=>true, 'msg'=>'Event car list updated', 'data'=>$event);
        $response = $response->withJson($responseArr);
        return $response;
      });
    });
    $this->group('/rooms', function () {
      //Get Event Rooms
      $this->get('', function ($request, $response, $args) {
        $event_id = $args['event_id'];
        $responseArr = array(
          'status' => false,
          'msg' => '',
          'data' => null
        );
        $responseArr['data'] = FrcPortal\EventRoom::with('users')->where('event_id',$event_id)->get();
        $responseArr['status'] = true;
        $response = $response->withJson($responseArr);
        return $response;
      });
      //Get Event Rooms
      $this->get('/adminList', function ($request, $response, $args) {
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        $event_id = $args['event_id'];
        try {
          $responseArr['data'] = getEventRoomList($event_id);
          $responseArr['status'] = true;
          $responseArr['msg'] = '';
        } catch (Exception $e) {
      		$result['msg'] = handleExceptionMessage($e);
      	}
        $response = $response->withJson($responseArr);
        return $response;
      });
      //Add New Event Room
      $this->post('', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
        }

        if(!isset($formData['event_id']) || $formData['event_id'] == '') {
          return badRequestResponse($response, $msg = 'Event ID cannot be blank');
        }
        if(!isset($formData['user_type']) || $formData['user_type'] == '') {
          return badRequestResponse($response, $msg = 'User Type cannot be blank');
        }
        if(!isset($formData['gender']) || ($formData['gender'] == '' && $formData['user_type'] != 'Mentor')) {
          return badRequestResponse($response, $msg = 'Gender cannot be blank');
        }
        $room = new FrcPortal\EventRoom();
        $room->event_id = $formData['event_id'];
        $room->user_type = $formData['user_type'];
        $room->gender = $formData['gender'];
        if($room->save()) {
          try {
            $responseArr['data'] = getEventRoomList($event_id);
            $responseArr['status'] = true;
            $responseArr['msg'] = 'New room added';
          } catch (Exception $e) {
        		$result['msg'] = handleExceptionMessage($e);
        	}
        } else {
          $responseArr = array('status'=>false, 'msg'=>'Event went wrong', 'data' => null);
        }
        $response = $response->withJson($responseArr);
        return $response;
      });
      //Update Room lists
      $this->put('', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
        }

        $event_id = $args['event_id'];
        $formData = $request->getParsedBody();
        if(!isset($formData['rooms']) || !is_array($formData['rooms']) || empty($formData['rooms'])) {
          return badRequestResponse($response);
        }
        $rooms = FrcPortal\EventRoom::where('event_id',$event_id)->get();
        foreach($rooms as $room) {
          $room_id = $room->room_id;
          $roomArr = $formData['rooms'][$room_id];
          $userArr = array_column($roomArr, 'user_id');
          if(!empty($userArr) && count($userArr) <= 4) {
            $events = FrcPortal\EventRequirement::where('event_id',$event_id)->whereIn('user_id', $userArr)->update(['room_id' => $room_id]);
          }
        }
        //Not Assigned a car
        $roomArr = $formData['rooms']['non_select'];
        $userArr = array_column($roomArr, 'user_id');
        if(!empty($userArr)) {
          $events = FrcPortal\EventRequirement::where('event_id',$event_id)->whereIn('user_id', $userArr)->update(['room_id' => null]);
        }
        $event = FrcPortal\User::with(['event_requirements' => function ($query) use ($event_id) {
                            $query->where('event_id','=',$event_id);
                          },'event_requirements.event_rooms','event_requirements.event_cars'])->get();
        $responseArr = array('status'=>true, 'msg'=>'Event room list updated', 'data'=>$event);
        $response = $response->withJson($responseArr);
        return $response;
      });
      //Delete event room
      $this->delete('/{room_id:[a-z0-9]{13}}', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
        }

        $event_id = $args['event_id'];
        $room_id = $args['room_id'];
        try {
          deleteEventRoom($event_id, $room_id);
          $rooms = getEventRoomList($event_id);
          $responseArr = array('status'=>true, 'msg'=>'Room Deleted', 'data' => $rooms);
          $response = $response->withJson($responseArr);
          return $response;
        } catch (Exception $e) {
          return exceptionResponse($response, $msg = handleExceptionMessage($e), $code = 200);
        }
      });
    });
    $this->group('/timeSlots', function () {
      //Get event time slots
      $this->get('', function ($request, $response, $args) {
        $event_id = $args['event_id'];
        $responseArr = getEventTimeSlotList($event_id);
        $response = $response->withJson($responseArr);
        return $response;
      });
      //Update event time slot
      $this->put('/{time_slot_id:[a-z0-9]{13}}', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
        }

        $event_id = $args['event_id'];
        $time_slot_id = $args['time_slot_id'];
        $timeSlot = FrcPortal\EventTimeSlot::where('event_id',$event_id)->where('time_slot_id',$time_slot_id)->first();
        if($timeSlot) {
          $responseArr = updateTimeSlot($timeSlot, $formData);
        }
        $response = $response->withJson($responseArr);
        return $response;
      });
      //Delete event time slot
      $this->delete('/{time_slot_id:[a-z0-9]{13}}', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
        }

        $event_id = $args['event_id'];
        $time_slot_id = $args['time_slot_id'];
        $timeSlot = FrcPortal\EventTimeSlot::where('event_id',$event_id)->where('time_slot_id',$time_slot_id)->delete();
        if($timeSlot) {
          $slots = getEventTimeSlotList($event_id);
          if($slots['status'] != false) {
            $responseArr = array('status'=>true, 'msg'=>'Time Slot Deleted', 'data' => $slots['data']);
          } else {
            $responseArr = $rooms;
          }
        } else {
          $responseArr = array('status'=>false, 'msg'=>'Something went wrong', 'data' => $event);
        }

        $response = $response->withJson($responseArr);
        return $response;
      });
      //Add new event time slot
      $this->post('', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
        }

        $event_id = $args['event_id'];
        $responseArr = AddTimeSlot($event_id, $formData);

        $response = $response->withJson($responseArr);
        return $response;
      });
    });
    $this->group('/food', function () {
      //Get Food  Options
      $this->get('', function ($request, $response, $args) {
        $event_id = $args['event_id'];
        $responseArr = array(
          'status' => false,
          'msg' => '',
          'data' => null
        );
        $responseArr['data'] = FrcPortal\EventFood::where('event_id',$event_id)->get();
        $responseArr['status'] = true;
        $response = $response->withJson($responseArr);
        return $response;
      });
      //Get Food  Options
      $this->get('/list', function ($request, $response, $args) {
        $event_id = $args['event_id'];
        $responseArr = array(
          'status' => false,
          'msg' => '',
          'data' => null
        );
        $data = array();
        $foods = FrcPortal\EventFood::where('event_id',$event_id)->get();
        foreach($foods as $food) {
          $group = $food['group'];
          $data[$group][] = $food;
        }
        $responseArr['data'] = $data;
        $responseArr['status'] = true;
        $response = $response->withJson($responseArr);
        return $response;
      });
      //Edit Food  Option
      $this->put('/{food_id:[a-z0-9]{13}}', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
        }

        $event_id = $args['event_id'];
        $food_id = $args['food_id'];
        $food = FrcPortal\EventFood::where('event_id',$event_id)->where('food_id',$food_id)->first();
        if($food) {
          $food->group = isset($formData['group']) ? $formData['group']:'';
          $food->description = isset($formData['description']) ? $formData['description']:'';
          if($food->save()) {
            $responseArr['status'] = true;
            $responseArr['msg'] = 'Food option Updated';
            $responseArr['data'] = FrcPortal\EventFood::where('event_id',$event_id)->get();
          }
        }
        $response = $response->withJson($responseArr);
        return $response;
      });
      //Delete Food  Option
      $this->delete('/{food_id:[a-z0-9]{13}}', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
        }

        $event_id = $args['event_id'];
        $food_id = $args['food_id'];
        $food = FrcPortal\EventFood::where('event_id',$event_id)->where('food_id',$food_id)->delete();
        if($food) {
          $responseArr['status'] = true;
          $responseArr['msg'] = 'Food option deleted';
          $responseArr['data'] = FrcPortal\EventFood::where('event_id',$event_id)->get();
        }
        $response = $response->withJson($responseArr);
        return $response;
      });
      //Add Food  Option
      $this->post('', function ($request, $response, $args) {
        $userId = FrcPortal\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Auth::isAdmin()) {
          return unauthorizedResponse($response);
        }

        $event_id = $args['event_id'];
        $food = new FrcPortal\EventFood();
        $food->event_id = $event_id;
        $food->group = isset($formData['group']) ? $formData['group']:'';
        $food->description = isset($formData['description']) ? $formData['description']:'';
        if($food->save()) {
          $responseArr['status'] = true;
          $responseArr['msg'] = 'Food option created';
          $responseArr['data'] = FrcPortal\EventFood::where('event_id',$event_id)->get();
        }
        $response = $response->withJson($responseArr);
        return $response;
      });
    });
    //Edit Event
    $this->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      $event_id = $args['event_id'];
      $event = FrcPortal\Event::find($event_id);

      $event->type = isset($formData['type']) && $formData['type'] != '' ? $formData['type'] : null;
      $event->poc_id = isset($formData['poc']['user_id']) && $formData['poc']['user_id'] != '' ? $formData['poc']['user_id']:null;
      if($formData['registration_deadline'] != null && $formData['registration_deadline'] != '') {
        $registration_deadline = new DateTime($formData['registration_deadline']);
        $event->registration_deadline = $registration_deadline->format('Y-m-d').' 23:59:59';
      } else {
        $event->registration_deadline = null;
      }
      $event->registration_deadline_gcalid = isset($formData['registration_deadline_gcalid']) && $formData['registration_deadline_gcalid'] != '' ? $formData['registration_deadline_gcalid']:null;

      $eventReqs = isset($formData['requirements']) ? $formData['requirements'] : null;
      if(!is_null($eventReqs)) {
        foreach($eventReqs as $req=>$val) {
          $event->{$req} = $val;
        }
      }
      if($event->save()) {
        $event->load('poc');
        $responseArr = array('status'=>true, 'msg'=>'Event Information Saved', 'data' => $event);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Event went wrong', 'data' => $event);
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    //Sync Google Calendar Event
    $this->put('/syncGoogleCalEvent', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      $event_id = $args['event_id'];
      try {
				$event = syncGoogleCalendarEvent($event_id);
        $responseArr = array(
      		'status' => true,
      		'msg' => $event->name.' synced with Google Calendar',
      		'data' => $event
      	);
			} catch (Exception $e) {
        $responseArr = array(
      		'status' => false,
      		'msg' => handleExceptionMessage($e),
      		'data' => null
      	);
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    //Toggle Event Requirements per User
    $this->put('/toggleEventReqs', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      $event_id = $args['event_id'];
      if(!isset($formData['users']) || !is_array($formData['users']) || empty($formData['users'])) {
        return badRequestResponse($response, $msg = 'Please select at least 1 user');
      }
      if(!isset($formData['requirement']) || $formData['requirement'] == '' || !in_array($formData['requirement'],array('registration','permission_slip','payment'))) {
        return badRequestResponse($response, $msg = 'Invalid event requirement');
      }
      $event = FrcPortal\Event::find($event_id);
      $array = array();
      $req = $formData['requirement'];
      $events = $formData['users'];
      foreach($events as $user) {
        //$user_id = $user['user_id'];
        $reqArr = FrcPortal\EventRequirement::firstOrNew(['event_id' => $event_id, 'user_id' => $user]);
        //$reqArr = FrcPortal\AnnualRequirement::where('season_id',$season_id)->where('user_id',$user)->first();
        $cur = isset($reqArr->$req) ? $reqArr->$req : false;
        $new = !$cur;
        if($req == 'registration' && $new == false) {
          $reqUpdate = FrcPortal\EventRequirement::where('event_id',$event_id)->where('user_id',$user)->delete();
          $eventCarUpdate = FrcPortal\EventCar::where('event_id',$event_id)->where('user_id',$user)->delete();
        } else {
          $reqArr->$req = $new;
          $reqArr->save();
        }
      }
      $event = getUsersEventRequirements($event_id);
      $responseArr = array('status'=>true, 'msg'=>'Event Requirements Updated', 'data' => $event);
      $response = $response->withJson($responseArr);
      return $response;
    });
    //Toggle Attendance Confirm per User
    $this->put('/toggleConfirmAttendance', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      $event_id = $args['event_id'];
      if(!isset($formData['users']) || !is_array($formData['users']) || empty($formData['users'])) {
        return badRequestResponse($response, $msg = 'Please select at least 1 user');
      }
      /* if(!isset($formData['requirement']) || $formData['requirement'] == '' || !in_array($formData['requirement'],array('registration','permission_slip','payment'))) {
        return badRequestResponse($response, $msg = 'Invalid event requirement');
      } */
      $event = FrcPortal\Event::find($event_id);
      $array = array();
      $users = $formData['users'];
      $user_ids = array_column($users, 'user_id');
      foreach($users as $user) {
        $user_id = $user['user_id'];
        $cur = isset($user['event_requirements']['attendance_confirmed']) ? $user['event_requirements']['attendance_confirmed'] : false;
        $new = !$cur;
        $ereq = FrcPortal\EventRequirement::where('event_id', $event_id)->where('user_id', $user_id)->first();
        if(!is_null($ereq) && isset($ereq->registration) && $ereq->registration==true) {
          $ereq->attendance_confirmed = $new;
        } else {
          return badRequestResponse($response, $msg = 'User must be registered prior to receiving time credit');
        }
      }
      $event = getUsersEventRequirements($event_id);
      $responseArr = array('status'=>true, 'msg'=>'Event Requirements Updated', 'data' => $event);
      $response = $response->withJson($responseArr);
      return $response;
    });
    //Register for Event
    $this->post('/register', function ($request, $response, $args) {
      $loggedInUser = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

      $event_id = $args['event_id'];
      $user_id = $loggedInUser;

      if(isset($formData['user_id']) && $formData['user_id'] != $loggedInUser && !FrcPortal\Auth::isAdmin()) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      } else if(isset($formData['user_id']) && FrcPortal\Auth::isAdmin()) {
      	$user_id = $formData['user_id'];
      }
      $userFullName = FrcPortal\Auth::user()->full_name;

      if(!is_bool($formData['registration'])) {
        return badRequestResponse($response, $msg = 'Invalid Request, no registration option.');
      }

      $user =  FrcPortal\User::find($user_id);
      $user_type = $user->user_type;
      $gender = $user->gender;

      $registrationBool = (bool) $formData['registration'];
      $event = FrcPortal\Event::find($event_id);
      if($registrationBool) {
        if(time() > $event->date['start']['unix']) {
          return badRequestResponse($response, $msg = 'Registration is closed. Event has already started.');
      	} elseif(($event->registration_deadline_date['unix'] != null && time() > $event->registration_deadline_date['unix']) && !FrcPortal\Auth::isAdmin()) {
          return badRequestResponse($response, $msg = 'Registration is closed. Registration deadline was '.date('F j, Y g:m A',$event->registration_deadline_unix).'.');
      	}
        $reqUpdate = FrcPortal\EventRequirement::updateOrCreate(['event_id' => $event_id, 'user_id' => $user_id], ['registration' => true, 'comments' => $formData['comments']]);
        $ereq_id = $reqUpdate->ereq_id;
        $can_drive = (bool) $formData['can_drive'];
        $drivers_req = (bool) $event->drivers_required;
      	if($drivers_req && $user_type == 'Mentor') {
          $car = FrcPortal\EventCar::find($reqUpdate->car_id);
          if($can_drive) {
            $eventCarUpdate = FrcPortal\EventCar::updateOrCreate(['event_id' => $event_id, 'user_id' => $user_id], ['car_space' => $formData['event_cars']['car_space']]);
            $reqUpdate->can_drive = true;
            $reqUpdate->car_id = $eventCarUpdate->car_id;
            $reqUpdate->save();
          } else {
            if(!is_null($car) && $car->user_id == $user_id) {
              $eventCarUpdate = FrcPortal\EventCar::where('event_id',$event_id)->where('user_id',$user_id)->delete();
              $reqUpdate->can_drive = false;
              $reqUpdate->car_id = null;
              $reqUpdate->save();
            }
          }
        }
        $room_required = (bool) $event->room_required;
        if($room_required && $user_type == 'Student') {
          $room_id = $formData['room_id'];
          $room = FrcPortal\EventRoom::where('room_id',$room_id)->where('event_id',$event_id)->first();
          if(is_null($room)) {
            $responseArr['msg'] = 'Invalid Room Selection';
            $response = $response->withJson($responseArr);
            return $response;
          }
          if($room->user_type != $user_type) {
            $responseArr['msg'] = 'Room User Type does not match User Type';
            $response = $response->withJson($responseArr);
            return $response;
          }
          if($room->user_type != 'Mentor' && $room->gender != $gender) {
            $responseArr['msg'] = 'Room Gender does not match User Gender';
            $response = $response->withJson($responseArr);
            return $response;
          }
          $reqUpdate->room_id = isset($formData['room_id']) && $formData['room_id'] != '' ? $formData['room_id']:null;
          $reqUpdate->save();
        }
        $time_slots_required = (bool) $event->time_slots_required;
        if($time_slots_required) {
          if(isset($formData['event_time_slots']) && count($formData['event_time_slots']) > 0) {
            $ts_ids = array_column($formData['event_time_slots'], 'time_slot_id');
            $reqUpdate->event_time_slots()->sync($ts_ids);
          } else {
            $reqUpdate->event_time_slots()->detach();
            $responseArr['msg'] = 'Please select at least 1 time slot';
            $response = $response->withJson($responseArr);
            return $response;
          }
        }
        $food_required = (bool) $event->food_required;
        if($food_required) {
          $event_food_count = FrcPortal\EventFood::distinct('group')->where('event_id',$event_id)->count('group');
          if(isset($formData['event_food']) && count($formData['event_food']) == $event_food_count) {
            $food_ids = array_values($formData['event_food']);
            $reqUpdate->event_food()->sync($food_ids);
          } else {
            $reqUpdate->event_food()->detach();
            $responseArr['msg'] = 'Please select 1 option for each section';
            $response = $response->withJson($responseArr);
            return $response;
          }
        }
      } else {
        $reqUpdate = FrcPortal\EventRequirement::where('event_id',$event_id)->where('user_id',$user_id)->delete();
        $eventCarUpdate = FrcPortal\EventCar::where('event_id',$event_id)->where('user_id',$user_id)->delete();
      }
      //notify User
      $reg = $registrationBool ? 'registered':'unregistered';
      $slackMsg = 'You successfully '.$reg.' for '.$event->name.'.';
      $slackMsgPoc = $user->full_name.' '.$reg.' for '.$event->name.'.';
      $msg = $slackMsg;
      if($user_id != $loggedInUser) {
        $slackMsg = $userFullName.' '.$reg.'  you for '.$event->name.'.';
        $slackMsgPoc = $userFullName.' '.$reg.'  '.$user->full_name.' for '.$event->name.'.';
        $msg = 'You successfully '.$reg.' '.$user->full_name.' for '.$event->name.'.';
      }
      //Send notifications
      $host = getSettingsProp('env_url');
      $msgData = array(
        'slack' => array(
          'title' => 'Event Registration',
          'body' => $slackMsg.' Please go to '.$host.'/events/'.$event->event_id.' for more information.'
        ),
        'email' => array(
          'subject' => 'Event Registration',
          'content' =>  $slackMsg.' Please go to '.$host.'/events/'.$event->event_id.' for more information.'
        )
      );
      $user->sendUserNotification('event_registration', $msgData);
      //slackMessageToUser($user_id, $slackMsg);
      //notify event POC
      if(!is_null($event->poc_id) && $user_id != $event->poc_id && $loggedInUser != $event->poc_id) {
        slackMessageToUser($event->poc_id, $slackMsgPoc);
      }


      $eventReqs = FrcPortal\User::with(['event_requirements' => function ($query) use ($event_id) {
                          $query->where('event_id','=',$event_id);
                        },'event_requirements.event_rooms','event_requirements.event_cars'])->where('user_id',$user_id)->first();
      $responseArr = array('status'=>true, 'type'=>'success', 'msg'=>$msg, 'data'=>$eventReqs);
      $response = $response->withJson($responseArr);
      return $response;
    });
    //Delete Event
    $this->delete('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

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
  //Add New Event
  $this->post('', function ($request, $response, $args) {
    $userId = FrcPortal\Auth::user()->user_id;
    $formData = $request->getParsedBody();
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    if(!FrcPortal\Auth::isAdmin()) {
      return unauthorizedResponse($response);
    }

    if(!isset($formData['name']) || $formData['name'] == '') {
      return badRequestResponse($response, $msg = 'Name cannot be blank');
    }
    if(!isset($formData['type']) || $formData['type'] == '') {
      return badRequestResponse($response, $msg = 'Event type cannot be blank');
    }
    if(!isset($formData['event_start']) || $formData['event_start'] == '') {
      return badRequestResponse($response, $msg = 'Start Date cannot be blank');
    }
    if(!isset($formData['event_end']) || $formData['event_end'] == '') {
      return badRequestResponse($response, $msg = 'End Date cannot be blank');
    }
    if(strtotime($formData['event_start']) >= strtotime($formData['event_end'])) {
      return badRequestResponse($response, $msg = 'Start Date must be before End Date');
    }
    if(!isset($formData['google_cal_id']) || $formData['google_cal_id'] == '') {
      return badRequestResponse($response, $msg = 'Invalid Google calendar ID');
    }
    $event = FrcPortal\Event::where('google_cal_id', $formData['google_cal_id'])->first();
    if(is_null($event)) {
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
      $event->food_required = isset($formData['requirements']['food']) && $formData['requirements']['food'] ? true:false;
      $event->time_slots_required = isset($formData['requirements']['time_slots']) && $formData['requirements']['time_slots'] ? true:false;
      if($event->save()) {
        $limit = 10;
        $totalNum = FrcPortal\Event::count();
        $events = FrcPortal\Event::orderBy('event_start','DESC')->limit($limit)->get();
        $data = array();
        $data['results'] = $events;
        $data['total'] = $totalNum;
        $data['maxPage'] = ceil($totalNum/$limit);
        $responseArr = array('status'=>true, 'msg'=>$event->name.' created', 'data'=>$data);
         //Send notifications
        $host = getSettingsProp('env_url');
        $msgData = array(
          'slack' => array(
            'title' => 'New Event Created',
            'body' => 'Event '.$event->name.' has been created in the Team Portal.  Please go to '.$host.'/events/'.$event->event_id.' for more information and registration.'
          ),
          'email' => array(
            'subject' => 'New Event Created',
            'content' =>  'Event '.$event->name.' has been created in the Team Portal.  Please go to '.$host.'/events/'.$event->event_id.' for more information and registration.'
          )
        );
        sendMassNotifications($type = 'new_event', $msgData);
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

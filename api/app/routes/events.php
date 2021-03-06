<?php
use Illuminate\Database\Capsule\Manager as DB;
use GuzzleHttp\Client;
use FrcPortal\Utilities\IniConfig;

$app->group('/events', function () {
  //Get all events
  $this->get('', function ($request, $response, $args) {
    $events = array();
  	$data = array();

    $searchProperties = array(
      'name' => '',
      'type' => '',
      'event_start' => '',
      'event_end' => '',
    );
    $defaults = array(
      'filter' => '',
      'limit' => 10,
      'order' => '-event_start',
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
    if(!empty($search['name'])) {
      $queryArr[] = array('name', 'LIKE', '%'.$search['name'].'%');
    }
    if(!empty($search['type'])) {
      $queryArr[] = array('type', '=', $search['type']);
    }
    if(!empty($search['event_start'])) {
      $es = new DateTime($search['event_start']);
      $es = $es->format('Y-m-d');
      $queryArr[] = array('event_start', '>=', $es);
    }
    if(!empty($search['event_end'])) {
      $ee = new DateTime($search['event_end']);
      $ee = $ee->format('Y-m-d').' 23:59:59';
      $queryArr[] = array('event_end', '<=', $ee);
    }
    $totalNum = 0;
    $events = FrcPortal\Event::where($queryArr);
  	if($filter != '') {
      $events = $events->orHavingRaw('name LIKE ?',array('%'.$filter.'%'));
      $events = $events->orHavingRaw('type LIKE ?',array('%'.$filter.'%'));
      $events = $events->orHavingRaw('event_start LIKE ?',array('%'.$filter.'%'));
      $events = $events->orHavingRaw('event_end LIKE ?',array('%'.$filter.'%'));
      $events = $events->orHavingRaw('year LIKE ?',array('%'.$filter.'%'));
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
  })->setName('Get Events');
  //Search Google Calendar for events
  $this->get('/searchGoogleCalendar', function ($request, $response, $args) {
    $calendar = getSettingsProp('google_calendar_id');
    $api_key = decryptItems(getSettingsProp('google_api_key'));
    if(empty($api_key)) {
      $responseArr = array('status'=>false, 'msg'=>'Google API Key cannot be blank.  Please got to Site Settings '.html_entity_decode('&#8594;').' Other Settings to set the API Key.');
      insertLogs($level = 'Warning', $message = 'Cannot search Google calendar.  Google API Key is blank');
      $response = $response->withJson($responseArr);
      return $response;
    }
    if(empty($calendar)) {
      $responseArr = array('status'=>false, 'msg'=>'Google Calendar ID cannot be blank.  Please got to Site Settings '.html_entity_decode('&#8594;').' Other Settings to set the Google Calendar ID.');
      insertLogs($level = 'Warning', $message = 'Cannot search Google calendar.  Google Calendar ID is blank');
      $response = $response->withJson($responseArr);
      return $response;
    }
    $optParams = array();
    if(!empty($request->getParam('q')) && $request->getParam('q') != 'null' && $request->getParam('q') != 'undefined') {
    	$q = trim($request->getParam('q'));
    	$optParams['q'] = $q;
    }
    $optParams['timeMax'] = date('c',strtotime('+3 Months'));
    if(!empty($request->getParam('timeMax')) && $request->getParam('timeMax') != 'null' && $request->getParam('timeMax') != 'undefined') {
    	$timeMax = date('c', strtotime($request->getParam('timeMax')));
    	if(is_numeric($request->getParam('timeMax'))) {
    		$timeMax = date('c',$request->getParam('timeMax')/1000);
    	}
    	$optParams['timeMax'] = $timeMax;
    }
    $optParams['timeMin'] = date('c',strtotime('-7 Days'));
    if(!empty($request->getParam('timeMin')) && $request->getParam('timeMin') != 'null' && $request->getParam('timeMin') != 'undefined') {
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
      $error = handleGoogleAPIException($e, 'Google Calendar');
      insertLogs($level = 'Warning', $error);
      return exceptionResponse($response, $msg = 'Something went wrong searching Google Calendar', $code = 200, $error);
    }
    $data = array(
    	'results'=>$allEvents,
    	'count'=>count($allEvents)
    );
    $responseArr = array('status'=>true, 'msg'=>'', 'data' => $data);
    $response = $response->withJson($responseArr);
    insertLogs($level = 'Information', $message = 'Successfully searched Google Calendar');
    return $response;
  })->setName('Search Google Calendar');
  //Browse FIRST Portal for events
  // $this->get('/browseFirstPortalEvents', function ($request, $response, $args) {
  //   $creds = getSettingsProp('firstportal_credential_data');
  //   $creds_arr = explode(',',$creds->value);
  //   $enc_cookie = $creds_arr[1];
  //   $cookie = decryptItems($enc_cookie);
  //   $client = new Client([
  //       'base_uri' => 'https://my.firstinspires.org/Dashboard/Dashboard',
  //       'timeout'  => 2.0,
  //   ]);
  //   $response = $client->request('POST', '/GetMoreTeams', [
  //     'headers' => [
  //         'Accept' => 'application/json',
  //         'Cookie' => $cookie
  //     ]]);
  //   $data = array(
  //   	'results'=>$allEvents,
  //   	'count'=>count($allEvents)
  //   );
  //   if($response->getStatusCode() == 200) {
  //     $data = $response->getBody();
  //     $json = json_validate($data);
  //     if($json['status']) {
  //       $data = $json['data'];
  //       $team_num = getSettingsProp('team_number');
  //       $team_array = array_filter($data['Teams'], function($obj){
  //           if (isset($obj->TeamCode)) {
  //             return $obj->TeamCode == $team_num;
  //           }
  //       });
  //       $eventList = $team_array['EventsList'];
  //     }
  //   }
  //   $responseArr = array('status'=>true, 'msg'=>'', 'data' => $data);
  //   $response = $response->withJson($responseArr);
  //   insertLogs($level = 'Information', $message = 'Successfully browsed FIRST Portal Events');
  //   return $response;
  // })->setName('Browse FIRST Portal Events');
  //Add New Event
  $this->post('', function ($request, $response, $args) {
    $userId = FrcPortal\Utilities\Auth::user()->user_id;
    $formData = $request->getParsedBody();
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    if(!FrcPortal\Utilities\Auth::isAdmin()) {
      insertLogs($level = 'Warning', $message = 'Unauthorized attempt to add Event.');
      return unauthorizedResponse($response);
    }

    if(empty($formData['name'])) {
      return badRequestResponse($response, $msg = 'Name cannot be blank');
    }
    if(empty($formData['type'])) {
      return badRequestResponse($response, $msg = 'Event type cannot be blank');
    }
    if(empty($formData['event_start'])) {
      return badRequestResponse($response, $msg = 'Start Date cannot be blank');
    }
    if(empty($formData['event_end'])) {
      return badRequestResponse($response, $msg = 'End Date cannot be blank');
    }
    if(strtotime($formData['event_start']) >= strtotime($formData['event_end'])) {
      return badRequestResponse($response, $msg = 'Start Date must be before End Date');
    }
    if(empty($formData['google_cal_id'])) {
      return badRequestResponse($response, $msg = 'Invalid Google calendar ID');
    }
    $event = FrcPortal\Event::where('google_cal_id', $formData['google_cal_id'])->first();
    if(empty($event)) {
      $event = new FrcPortal\Event();
      $event->google_cal_id = $formData['google_cal_id'];
      $event->name = $formData['name'];
      $event->type = $formData['type'];
      $event->event_start = $formData['event_start'];
      $event->event_end = $formData['event_end'];
      $event->details = !empty($formData['details']) ? $formData['details']:'';
      $event->location = !empty($formData['location']) ? $formData['location']:'';
      $event->payment_required = !empty($formData['requirements']['payment']);
      $event->permission_slip_required = !empty($formData['requirements']['permission_slip']);
      $event->food_required = !empty($formData['requirements']['food']);
      $event->room_required = !empty($formData['requirements']['room']);
      $event->drivers_required = !empty($formData['requirements']['drivers']);
      $event->food_required = !empty($formData['requirements']['food']);
      $event->time_slots_required = !empty($formData['requirements']['time_slots']);
      if($userId != IniConfig::iniDataProperty('admin_user')) {
        $event->poc_id = $userId;
      }
      //$event->hotel_info = '';
      if($event->save()) {
        if($event->room_required && !empty($formData['rooms'])) {
          $roomTypes = array('boys','girls','adults');
          $roomKey = array(
            'boys' => array('user_type'=>'Student','gender'=>'Male'),
            'girls' => array('user_type'=>'Student','gender'=>'Female'),
            'adults' => array('user_type'=>'Adult')
          );
          $rooms = array();
          $filter_options = array(
              'options' => array( 'min_range' => 0)
          );
          foreach($roomTypes as $room) {
            if(!empty($formData['rooms'][$room]) && filter_var($formData['rooms'][$room], FILTER_VALIDATE_INT, $filter_options ) !== FALSE) {
              $num = $formData['rooms'][$room];
              for($i=0;$i<$num;$i++) {
                $rm = new FrcPortal\EventRoom($roomKey[$room]);
                $event->event_rooms()->save($rm);
              }
            }
          }

        }
        $responseArr = array('status'=>true, 'msg'=>$event->name.' created', 'data'=>$event);
        insertLogs($level = 'Information', $message = $event->name.' created');
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
      insertLogs($level = 'Information', $message = 'Attempted to create duplicate event "'.$event->name.'"');
    }
    $response = $response->withJson($responseArr);
    return $response;
  })->setName('Add Event');
  $this->group('/{event_id:[a-z0-9]{13}}', function () {
    //Get Event
    $this->get('', function ($request, $response, $args) {
      $authed = FrcPortal\Utilities\Auth::isAuthenticated();
      $event_id = $args['event_id'];
      //Event passed from middleware
      $event = $request->getAttribute('event');
      $reqsBool = $request->getParam('requirements') !== null && $request->getParam('requirements')==true ? true:false;
      $withArr = array('poc');
      $withCountArr = array();
      if($request->getParam('event_rooms') !== null && $request->getParam('event_rooms')==true) {
        if($authed) {
          $withArr[] = 'event_rooms.users';
        } else {
          $withArr[] = 'event_rooms';
        }
      }
      if($request->getParam('event_cars') !== null && $request->getParam('event_cars')==true) {
        if($authed) {
          $withArr[] = 'event_cars';
        }
      }
      if($request->getParam('event_time_slots') !== null && $request->getParam('event_time_slots')==true) {
        if($authed) {
          $withArr[] = 'event_time_slots.registrations.user';
        } else {
          $withArr['event_time_slots'] = function ($query) use ($event_id) {
            $query->withCount('registrations');
          };
        }
      }
      if($request->getParam('users') !== null && $request->getParam('users')==true) {
        if($authed) {
          $withArr['registered_users'] = function ($query) use ($event_id) {
            $query->where('registration',true);
          };
        } else {
          $event->registered_users_count = $event->registered_users()->count();
        }

      }
      $event = $event->load($withArr);
      if($reqsBool) {
        $event->users = $event->getUsersEventRequirements();
      }
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $event);
      $response = $response->withJson($responseArr);
      insertLogs($level = 'Information', $message = 'Successfully returned event "'.$event->name.'"');
      return $response;
    })->setName('Get Event');
    //Get Event Requirements
    $this->get('/eventRequirements', function ($request, $response, $args) {
      $event_id = $args['event_id'];
      //Event passed from middleware
      $event = $request->getAttribute('event');
      $eventReqs = $event->getUsersEventRequirements();
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $eventReqs);
      $response = $response->withJson($responseArr);
      insertLogs($level = 'Information', $message = 'Successfully returned event "'.$event->name.'" Requirements');
      return $response;
    })->setName('Get Event Requirements');
    $this->group('/cars', function () {
      //Get Event Cars
      $this->get('', function ($request, $response, $args) {
        //Event passed from middleware
        $event = $request->getAttribute('event');
        $responseArr = standardResponse($status = true, $msg = '', $data = $event->getEventCarList());
        $response = $response->withJson($responseArr);
        insertLogs($level = 'Information', $message = 'Successfully returned event "'.$event->name.'" Cars');
        return $response;
      })->setName('Get Event Cars');
      //Update Event Car passengers
      $this->put('', function ($request, $response, $args) {
        //Event passed from middleware
        $event = $request->getAttribute('event');
        $userId = FrcPortal\Utilities\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Utilities\Auth::isAdmin()) {
          insertLogs($level = 'Warning', $message = 'Unauthorized attempt to update Event Car passengers');
          return unauthorizedResponse($response);
        }
        $formData = $request->getParsedBody();
        if(empty($formData['cars']) || !is_array($formData['cars'])) {
          return badRequestResponse($response);
        }
        $cars = $event->event_cars()->get();
        foreach($cars as $car) {
          $car_id = $car->car_id;
          $carArr = $formData['cars'][$car_id];
          $userArr = array_column($carArr, 'user_id');
          if(!empty($userArr) && count($userArr) <= $car['car_space']) {
            $events = $event->event_requirements()->whereIn('user_id', $userArr)->update(['car_id' => $car_id]);
        	}
        }
        //Not Assigned a car
        $carArr = $formData['cars']['non_select'];
        $userArr = array_column($carArr, 'user_id');
        if(!empty($userArr)) {
          $events = $event->event_requirements()->whereIn('user_id', $userArr)->update(['car_id' => null]);
        }
        //$event = getUsersEventRequirements($event_id);
        $responseArr = array('status'=>true, 'msg'=>'Event car list updated', 'data'=>null);
        insertLogs($level = 'Information', $message = 'Event car list updated');
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Update Event Car Passengers');
    });
    $this->group('/rooms', function () {
      //Get Event Rooms
      $this->get('', function ($request, $response, $args) {
        //Event passed from middleware
        $event = $request->getAttribute('event');
        $data = $event->event_rooms()->with('users')->get();
        $responseArr = standardResponse($status = true, $msg = '', $data);
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Get Event Rooms');
      //Get Event Rooms
      $this->get('/adminList', function ($request, $response, $args) {
        //Event passed from middleware
        $event = $request->getAttribute('event');
        $responseArr = standardResponse($status = true, $msg = '', $data = $event->getEventRoomList());
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Get Event Rooms Admin List');
      //Add New Event Room
      $this->post('', function ($request, $response, $args) {
        $userId = FrcPortal\Utilities\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Utilities\Auth::isAdmin()) {
          insertLogs($level = 'Warning', $message = 'Unauthorized attempt to add an Event Room');
          return unauthorizedResponse($response);
        }
        //Event passed from middleware
        $event = $request->getAttribute('event');
        if(!$event->room_required) {
          return badRequestResponse($response, $msg = 'Hotel rooms not needed for this event');
        }
        if(empty($formData['user_type'])) {
          return badRequestResponse($response, $msg = 'User Type cannot be blank');
        }
        if(empty($formData['gender']) || ($formData['gender'] == '' && $formData['user_type'] != 'Adult')) {
          return badRequestResponse($response, $msg = 'Gender cannot be blank');
        }
        $room = new FrcPortal\EventRoom();
        $room->event_id = $event->event_id;
        $room->user_type = $formData['user_type'];
        $room->gender = $formData['gender'];
        if($room->save()) {
          $responseArr['data'] = $event->getEventRoomList();
          $responseArr['status'] = true;
          $responseArr['msg'] = 'New room added';
          insertLogs($level = 'Information', $message = 'Event Room added for '.$event->name);
        }
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Add Event Room');
      //Add New Event Room for a user
      $this->post('/user', function ($request, $response, $args) {
        $user = FrcPortal\Utilities\Auth::user();
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        //Event passed from middleware
        $event = $request->getAttribute('event');
        if(!$event->room_required) {
          return badRequestResponse($response, $msg = 'Hotel rooms not needed for this event');
        }
        if($user->user_type == '') {
          return badRequestResponse($response, $msg = 'User type cannot be blank.  Please update your profile.');
        }
        if($user->gender == '') {
          return badRequestResponse($response, $msg = 'Gender cannot be blank.  Please update your profile.');
        }
        $room = new FrcPortal\EventRoom();
        $room->event_id = $event->event_id;
        $room->user_type = $user->adult ? 'Adult':'Student';
        $room->gender = $user->adult ? '':$user->gender;
        if($room->save()) {
          $responseArr['data'] = $event->event_rooms()->with('users')->get();
          $responseArr['status'] = true;
          $responseArr['msg'] = 'New room added';
        }
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Add Event Room');
      //Update Room lists
      $this->put('', function ($request, $response, $args) {
        $userId = FrcPortal\Utilities\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Utilities\Auth::isAdmin()) {
          insertLogs($level = 'Warning', $message = 'Unauthorized attempt to update Event Room list');
          return unauthorizedResponse($response);
        }
        //Event passed from middleware
        $event = $request->getAttribute('event');
        $formData = $request->getParsedBody();
        if(empty($formData['rooms']) || !is_array($formData['rooms'])) {
          return badRequestResponse($response);
        }
        $rooms = $event->event_rooms()->get();
        foreach($rooms as $room) {
          $room_id = $room->room_id;
          $roomArr = $formData['rooms'][$room_id];
          $userArr = array_column($roomArr, 'user_id');
          if(!empty($userArr) && count($userArr) <= 4) {
            $events = $event->event_requirements()->whereIn('user_id', $userArr)->update(['room_id' => $room_id]);
          }
        }
        //Not Assigned a room
        $roomArr = $formData['rooms']['non_select'];
        $userArr = array_column($roomArr, 'user_id');
        if(!empty($userArr)) {
          $events = $event->event_requirements()->whereIn('user_id', $userArr)->update(['room_id' => null]);
        }
        //$event = getUsersEventRequirements($event_id);
        insertLogs($level = 'Information', $message = 'Event Room List updated');
        $responseArr = array('status'=>true, 'msg'=>'Event room list updated', 'data'=>null);
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Update Event Room List');
      //Delete event room
      $this->delete('/{room_id:[a-z0-9]{13}}', function ($request, $response, $args) {
        $userId = FrcPortal\Utilities\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Utilities\Auth::isAdmin()) {
          insertLogs($level = 'Warning', $message = 'Unauthorized attempt to delete Event Room');
          return unauthorizedResponse($response);
        }
        //Event passed from middleware
        $event = $request->getAttribute('event');
        $room_id = $args['room_id'];
        //deleteEventRoom($event_id, $room_id);
        if($event->event_rooms()->find($room_id)->delete()) {
      		$rooms = $event->getEventRoomList();
          $responseArr = array('status'=>true, 'msg'=>'Room Deleted', 'data' => $rooms);
      	}
        $response = $response->withJson($responseArr);
      })->setName('Delete Event Room');
    });
    $this->group('/timeSlots', function () {
      //Get event time slots
      $this->get('', function ($request, $response, $args) {
        $event_id = $args['event_id'];
        try {
          $responseArr['data'] = getEventTimeSlotList($event_id);
          $responseArr['status'] = true;
          $responseArr['msg'] = '';
        } catch (Exception $e) {
      		$result['msg'] = handleExceptionMessage($e);
      	}
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Get Event Time Slots');
      $this->group('/{time_slot_id:[a-z0-9]{13}}', function () {
        //Update event time slot
        $this->put('', function ($request, $response, $args) {
          $userId = FrcPortal\Utilities\Auth::user()->user_id;
          $formData = $request->getParsedBody();
          $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
          if(!FrcPortal\Utilities\Auth::isAdmin()) {
            insertLogs($level = 'Warning', $message = 'Unauthorized attempt to update Event Time slot');
            return unauthorizedResponse($response);
          }

          $event_id = $args['event_id'];
          $time_slot_id = $args['time_slot_id'];
          try {
            $update = updateTimeSlot($event_id, $time_slot_id, $formData);
            $slots = getEventTimeSlotList($event_id);
            $responseArr = array('status'=>true, 'msg'=>'Time Slot Updated', 'data' => $slots);
          } catch (Exception $e) {
            return exceptionResponse($response, $msg = handleExceptionMessage($e), $code = 200);
          }
          return $response->withJson($responseArr);
        })->setName('Update Event Time Slot');
        //Delete event time slot
        $this->delete('', function ($request, $response, $args) {
          $userId = FrcPortal\Utilities\Auth::user()->user_id;
          $formData = $request->getParsedBody();
          $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
          if(!FrcPortal\Utilities\Auth::isAdmin()) {
            insertLogs($level = 'Warning', $message = 'Unauthorized attempt to delete Event Time slot');
            return unauthorizedResponse($response);
          }

          $event_id = $args['event_id'];
          $time_slot_id = $args['time_slot_id'];
          $timeSlot = FrcPortal\EventTimeSlot::where('event_id',$event_id)->where('time_slot_id',$time_slot_id)->delete();
          if($timeSlot) {
            $slots = getEventTimeSlotList($event_id);
            $responseArr = array('status'=>true, 'msg'=>'Time Slot Deleted', 'data' => $slots['data']);
          } else {
            $responseArr = array('status'=>false, 'msg'=>'Something went wrong', 'data' => null);
          }
          return $response->withJson($responseArr);
        })->setName('Delete Event Time Slot');
      });
      //Add new event time slot
      $this->post('', function ($request, $response, $args) {
        $userId = FrcPortal\Utilities\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Utilities\Auth::isAdmin()) {
          insertLogs($level = 'Warning', $message = 'Unauthorized attempt to add Event Time slot');
          return unauthorizedResponse($response);
        }
        $event_id = $args['event_id'];
        try {
          $add = addTimeSlot($event_id, $formData);
          $slots = getEventTimeSlotList($event_id);
          $responseArr = array('status'=>true, 'msg'=>'Time Slot Added', 'data' => $slots);
        } catch (Exception $e) {
          return exceptionResponse($response, $msg = handleExceptionMessage($e), $code = 200);
        }
        return $response->withJson($responseArr);
      })->setName('Add Event Time Slot');
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
      })->setName('Get Event Food Options');
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
      })->setName('Get Event Food Options List');
      $this->group('/{food_id:[a-z0-9]{13}}', function () {
        //Edit Food  Option
        $this->put('', function ($request, $response, $args) {
          $userId = FrcPortal\Utilities\Auth::user()->user_id;
          $formData = $request->getParsedBody();
          $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
          if(!FrcPortal\Utilities\Auth::isAdmin()) {
            insertLogs($level = 'Warning', $message = 'Unauthorized attempt to update Event Food option');
            return unauthorizedResponse($response);
          }

          $event_id = $args['event_id'];
          $food_id = $args['food_id'];
          $food = FrcPortal\EventFood::where('event_id',$event_id)->where('food_id',$food_id)->first();
          if($food) {
            $food->group = !empty($formData['group']) ? $formData['group']:'';
            $food->description = !empty($formData['description']) ? $formData['description']:'';
            if($food->save()) {
              $responseArr['status'] = true;
              $responseArr['msg'] = 'Food option Updated';
              $responseArr['data'] = FrcPortal\EventFood::where('event_id',$event_id)->get();
            }
          }
          $response = $response->withJson($responseArr);
          return $response;
        })->setName('Update Event Food Option');
        //Delete Food  Option
        $this->delete('', function ($request, $response, $args) {
          $userId = FrcPortal\Utilities\Auth::user()->user_id;
          $formData = $request->getParsedBody();
          $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
          if(!FrcPortal\Utilities\Auth::isAdmin()) {
            insertLogs($level = 'Warning', $message = 'Unauthorized attempt to delete Event Food option');
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
        })->setName('Delete Event Food Option');
      });
      //Add Food  Option
      $this->post('', function ($request, $response, $args) {
        $userId = FrcPortal\Utilities\Auth::user()->user_id;
        $formData = $request->getParsedBody();
        $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
        if(!FrcPortal\Utilities\Auth::isAdmin()) {
          insertLogs($level = 'Warning', $message = 'Unauthorized attempt to add Event Food option');
          return unauthorizedResponse($response);
        }

        $event_id = $args['event_id'];
        $food = new FrcPortal\EventFood();
        $food->event_id = $event_id;
        $food->group = !empty($formData['group']) ? $formData['group']:'';
        $food->description = !empty($formData['description']) ? $formData['description']:'';
        if($food->save()) {
          $responseArr['status'] = true;
          $responseArr['msg'] = 'Food option created';
          $responseArr['data'] = FrcPortal\EventFood::where('event_id',$event_id)->get();
        }
        $response = $response->withJson($responseArr);
        return $response;
      })->setName('Add Event Food Option');
    });
    //Edit Event
    $this->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        insertLogs($level = 'Warning', $message = 'Unauthorized attempt to update Event');
        return unauthorizedResponse($response);
      }
      $event_id = $args['event_id'];
      //Event passed from middleware
      $event = $request->getAttribute('event');
      //$event = FrcPortal\Event::find($event_id);
      $event->type = !empty($formData['type']) ? $formData['type'] : null;
      $event->poc_id = !empty($formData['poc']['user_id']) ? $formData['poc']['user_id']:null;
      if(!empty($formData['registration_deadline'])) {
        $registration_deadline = new DateTime($formData['registration_deadline']);
        $event->registration_deadline = $registration_deadline->format('Y-m-d').' 23:59:59';
      } else {
        $event->registration_deadline = null;
      }
      $event->registration_deadline_gcalid = !empty($formData['registration_deadline_gcalid']) ? $formData['registration_deadline_gcalid']:null;

      $eventReqs = !empty($formData['requirements']) ? $formData['requirements'] : null;
      if(!empty($eventReqs)) {
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
    })->setName('Update Event');
    //Sync Google Calendar Event
    $this->put('/syncGoogleCalEvent', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        insertLogs($level = 'Warning', $message = 'Unauthorized attempt to sync event with Google Calendar');
        return unauthorizedResponse($response);
      }
      $api_key = decryptItems(getSettingsProp('google_api_key'));
      if(empty($api_key)) {
        $responseArr = array('status'=>false, 'msg'=>'Google API Key cannot be blank.  Please got to Site Settings '.html_entity_decode('&#8594;').' Other Settings to set the API Key.');
        insertLogs($level = 'Warning', $message = 'Cannot search Google calendar.  Google API Key is blank');
        $response = $response->withJson($responseArr);
        return $response;
      }
      //Event passed from middleware
      $event = $request->getAttribute('event');
      $event_id = $args['event_id'];
      try {
				$event = $event->syncGoogleCalendarEvent();
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
    })->setName('Sync Event with Google Calendar');
    //Toggle Event Requirements per User
    $this->put('/toggleEventReqs', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        insertLogs($level = 'Warning', $message = 'Unauthorized attempt to toggle event requirements.');
        return unauthorizedResponse($response);
      }

      $event_id = $args['event_id'];
      if(empty($formData['users']) || !is_array($formData['users'])) {
        return badRequestResponse($response, $msg = 'Please select at least 1 user');
      }
      if(empty($formData['requirement']) || !in_array($formData['requirement'],array('registration','permission_slip','payment'))) {
        return badRequestResponse($response, $msg = 'Invalid event requirement');
      }
      //Event passed from middleware
      $event = $request->getAttribute('event');
      //$event = FrcPortal\Event::find($event_id);
      $array = array();
      $req = $formData['requirement'];
      $events = $formData['users'];
      foreach($events as $user) {
        //$user_id = $user['user_id'];
        $reqArr = FrcPortal\EventRequirement::firstOrNew(['event_id' => $event_id, 'user_id' => $user]);
        //$reqArr = FrcPortal\AnnualRequirement::where('season_id',$season_id)->where('user_id',$user)->first();
        $cur = !empty($reqArr->$req) ? $reqArr->$req : false;
        $new = !$cur;
        if($req == 'registration' && $new == false) {
          $reqUpdate = FrcPortal\EventRequirement::where('event_id',$event_id)->where('user_id',$user)->delete();
          $eventCarUpdate = FrcPortal\EventCar::where('event_id',$event_id)->where('user_id',$user)->delete();
        } else {
          $reqArr->$req = $new;
          $reqArr->save();
        }
      }
      $event = $event->getUsersEventRequirements();
      $responseArr = array('status'=>true, 'msg'=>'Event Requirements Updated', 'data' => $event);
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Toggle Event Requirements');
    //Toggle Attendance Confirm per User
    $this->put('/toggleConfirmAttendance', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      $event_id = $args['event_id'];
      if(empty($formData['users']) || !is_array($formData['users'])) {
        return badRequestResponse($response, $msg = 'Please select at least 1 user');
      }
      /* if(!isset($formData['requirement']) || $formData['requirement'] == '' || !in_array($formData['requirement'],array('registration','permission_slip','payment'))) {
        return badRequestResponse($response, $msg = 'Invalid event requirement');
      } */
      //Event passed from middleware
      $event = $request->getAttribute('event');
      //$event = FrcPortal\Event::find($event_id);
      $array = array();
      $users = $formData['users'];
      $user_ids = array_column($users, 'user_id');
      foreach($users as $user) {
        $user_id = $user['user_id'];
        $cur = !empty($user['event_requirements']['attendance_confirmed']) ? $user['event_requirements']['attendance_confirmed'] : false;
        $new = !$cur;
        $ereq = FrcPortal\EventRequirement::where('event_id', $event_id)->where('user_id', $user_id)->first();
        if(!empty($ereq) && !empty($ereq->registration)) {
          $ereq->attendance_confirmed = $new;
        } else {
          return badRequestResponse($response, $msg = 'User must be registered prior to receiving time credit');
        }
      }
      $event = $event->getUsersEventRequirements();
      $responseArr = array('status'=>true, 'msg'=>'Event Requirements Updated', 'data' => $event);
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Confirm Event Attendance');
    //Register for Event
    $this->post('/register', function ($request, $response, $args) {
      $loggedInUser = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);

      $event_id = $args['event_id'];
      $user_id = $loggedInUser;

      if(!empty($formData['user_id']) && $formData['user_id'] != $loggedInUser && !FrcPortal\Utilities\Auth::isAdmin()) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      } else if(!empty($formData['user_id']) && FrcPortal\Utilities\Auth::isAdmin()) {
      	$user_id = $formData['user_id'];
      }
      $userFullName = FrcPortal\Utilities\Auth::user()->full_name;

      if(!is_bool($formData['registration'])) {
        return badRequestResponse($response, $msg = 'Invalid Request, no registration option.');
      }
      //Event passed from middleware
      $event = $request->getAttribute('event');
      //$event = FrcPortal\Event::find($event_id);

      $user =  FrcPortal\User::find($user_id);
      $user_type = $user->user_type;
      $adult = $user->adult;
      $gender = $user->gender;
      $roomType = $user->room_type;

      $registrationBool = (bool) $formData['registration'];
      if(time() > $event->date['start']['unix']) {
        return badRequestResponse($response, $msg = 'Registration is closed. Event has already started.');
      } elseif(($event->registration_deadline_date['unix'] != null && time() > $event->registration_deadline_date['unix']) && !FrcPortal\Utilities\Auth::isAdmin()) {
        return badRequestResponse($response, $msg = 'Registration is closed. Registration deadline was '.date('F j, Y g:m A',$event->registration_deadline_unix).'.');
      }
      if($registrationBool) {
        $reqUpdate = FrcPortal\EventRequirement::updateOrCreate(['event_id' => $event_id, 'user_id' => $user_id], ['registration' => true, 'comments' => $formData['comments']]);
        $ereq_id = $reqUpdate->ereq_id;
        $can_drive = (bool) $formData['can_drive'];
        $drivers_req = (bool) $event->drivers_required;
      	if($drivers_req && $adult) {
          $car = FrcPortal\EventCar::find($reqUpdate->car_id);
          if($can_drive) {
            $eventCarUpdate = FrcPortal\EventCar::updateOrCreate(['event_id' => $event_id, 'user_id' => $user_id], ['car_space' => $formData['event_cars']['car_space']]);
            $reqUpdate->can_drive = true;
            $reqUpdate->car_id = $eventCarUpdate->car_id;
            $reqUpdate->save();
          } else {
            if(!empty($car) && $car->user_id == $user_id) {
              $eventCarUpdate = FrcPortal\EventCar::where('event_id',$event_id)->where('user_id',$user_id)->delete();
              $reqUpdate->can_drive = false;
              $reqUpdate->car_id = null;
              $reqUpdate->save();
            }
          }
        }
        $room_required = (bool) $event->room_required;
        if($room_required && $user_type == 'Student' && !empty($formData['room_id'])) {
          $room_id = $formData['room_id'];
          $room = FrcPortal\EventRoom::where('room_id',$room_id)->where('event_id',$event_id)->first();
          if(empty($room)) {
            $responseArr['msg'] = 'Invalid Room Selection';
            $response = $response->withJson($responseArr);
            return $response;
          }
          if(!in_array($roomType,$room->user_type)) {
            $responseArr['msg'] = 'Invalid Room Selection.  Please select a room that matches your user type and gender.';
            $response = $response->withJson($responseArr);
            return $response;
          }
          $reqUpdate->room_id = !empty($formData['room_id']) ? $formData['room_id']:null;
          $reqUpdate->save();
        }
        $time_slots_required = (bool) $event->time_slots_required;
        if($time_slots_required) {
          if(!empty($formData['event_time_slots'])) {
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
          if(!empty($formData['event_food']) && count($formData['event_food']) == $event_food_count) {
            $food_ids = array_values($formData['event_food']);
            $reqUpdate->event_food()->sync($food_ids);
          } else {
            $reqUpdate->event_food()->detach();
            $responseArr['msg'] = 'Please select 1 option for each section';
            $response = $response->withJson($responseArr);
            return $response;
          }
        }
        insertLogs($level = 'Information', $message = $user->full_name.' registered for '.$event->name);
      } else {
        $reqUpdate = FrcPortal\EventRequirement::where('event_id',$event_id)->where('user_id',$user_id)->delete();
        $eventCarUpdate = FrcPortal\EventCar::where('event_id',$event_id)->where('user_id',$user_id)->delete();
        insertLogs($level = 'Information', $message = $user->full_name.' unregistered for '.$event->name);
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
      //notify event POC
      if(!empty($event->poc_id) && $user_id != $event->poc_id && $loggedInUser != $event->poc_id) {
        $poc = FrcPortal\User::find($event->poc_id);
        if(!empty($poc)) {
          $poc->slackMessage($slackMsgPoc);
        }
      }


      $eventReqs = FrcPortal\User::with(['event_requirements' => function ($query) use ($event_id) {
                          $query->where('event_id','=',$event_id);
                        },'event_requirements.event_rooms','event_requirements.event_cars'])->where('user_id',$user_id)->first();
      $responseArr = array('status'=>true, 'type'=>'success', 'msg'=>$msg, 'data'=>$eventReqs);
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Register for Event');
    //Delete Event
    $this->delete('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        insertLogs($level = 'Warning', $message = 'Unauthorized attempt to delete Event.');
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
    })->setName('Delete Event');
  })->add(function ($request, $response, $next) {
    //Event Midddleware to pull event data
    // get the route from the request
    $route = FrcPortal\Utilities\Auth::getRoute();
    if (!$route) {
        // no route matched
        return $next($request, $response);
    }
    $args = $route->getArguments();
    $event_id = $args['event_id'];
    $event = FrcPortal\Event::find($event_id);
    if(!empty($event)) {
      $request = $request->withAttribute('event', $event);
      $response = $next($request, $response);
    } else {
      $response = notFoundResponse($response, $msg = 'Event not found');
    }
  	return $response;
  });
});

?>

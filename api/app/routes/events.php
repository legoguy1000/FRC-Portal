<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/events', function () {
  $this->get('', function ($request, $response, $args) {
    $seasons = array();
  	$data = array();

    $filter = $request->getParam('filter') !== null ? $request->getParam('filter'):'';
    $limit = $request->getParam('limit') !== null ? $request->getParam('limit'):10;
    $order = $request->getParam('order') !== null ? $request->getParam('order'):'-year';
    $page = $request->getParam('page') !== null ? $request->getParam('page'):1;
    $listOnly = $request->getParam('listOnly') !== null && $request->getParam('listOnly')==true ? true:false;

    $totalNum = 0;
  	if($filter != '') {
      $seasons = FrcPortal\Season::where('game_name','LIKE','%'.$filter.'%')->orWhere('year','LIKE','%'.$filter.'%')->count();
  	} else {
      $totalNum = FrcPortal\Season::count();
    }

    $orderBy = '';
  	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
  	if(in_array($orderCol,array('game_name','year','start_date','bag_day','end_date'))) {
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
      $seasons = FrcPortal\Season::where('game_name','LIKE','%'.$filter.'%')->orWhere('year','LIKE','%'.$filter.'%')->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();
    } else {
      $seasons = FrcPortal\Season::orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();
    }


    $data['data'] = $seasons;
    $data['total'] = $totalNum;
    $data['maxPage'] = $limit > 0 ? ceil($totalNum/$limit) : 0;
    $data['status'] =true;
    $data['msg'] = '';
    if($listOnly) {
      $data = $seasons;
    }

    $response = $response->withJson($data);
    return $response;
  });
  $this->group('/{event_id:[a-z0-9]{13}}', function () {
    $this->get('', function ($request, $response, $args) {
      $event_id = $args['event_id'];
      $reqsBool = $request->getParam('requirements') !== null && $request->getParam('requirements')==true ? true:false;
      $season = FrcPortal\Season::find($season_id);
      if($reqsBool) {
        $season->users = FrcPortal\User::with(['annual_requirements' => function ($query) use ($season_id) {
                        		$query->where('season_id','=',$season_id);
                          }])->get();
      }
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $season);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->get('/eventRequirements', function ($request, $response, $args) {
      $event_id = $args['event_id'];
      $season = FrcPortal\User::with(['annual_requirements' => function ($query) use ($season_id) {
                          $query->where('season_id','=',$season_id);
                        }])->get();
    $responseArr = array('status'=>true, 'msg'=>'', 'data' => $season);
    $response = $response->withJson($responseArr);
    return $response;
    });
    $this->put('', function ($request, $response, $args) {
      //$authToken = checkToken(true,true);
      //$user_id = $authToken['data']['user_id'];
      //checkAdmin($user_id, $die = true);
      $event_id = $args['event_id'];
      $formData = $request->getParsedBody();
      $season = FrcPortal\Season::find($season_id);
      $start_date = new DateTime($formData['start_date']);
      $bag_day = new DateTime($formData['bag_day']);
      $end_date = new DateTime($formData['end_date']);

      $season->start_date = $start_date->format('Y-m-d');
      $season->bag_day = $bag_day->format('Y-m-d');
      $season->end_date = $end_date->format('Y-m-d');
      $season->game_logo = $formData['game_logo'];
      $season->game_name = $formData['game_name'];
      $season->hour_requirement = $formData['hour_requirement'];
      $season->game_logo = $formData['game_logo'];
      if($season->save()) {
        $responseArr = array('status'=>true, 'msg'=>'Season Information Saved', 'data' => $season);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong', 'data' => $season);
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('/syncGoogleCalEvent', function ($request, $response, $args) {
      //$authToken = checkToken(true,true);
      //$user_id = $authToken['data']['user_id'];
      //checkAdmin($user_id, $die = true);
      $event_id = $args['event_id'];
      $responseArr = syncGoogleCalendarEvent($cal_id, $event_id);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('/toggleEventReqs', function ($request, $response, $args) {
      //$authToken = checkToken(true,true);
      //$user_id = $authToken['data']['user_id'];
      //checkAdmin($user_id, $die = true);
      $season_id = $args['season_id'];
      $formData = $request->getParsedBody();
      if(!isset($formData['users']) || !is_array($formData['users']) || empty($formData['users'])) {
        $responseArr = array('status'=>false, 'msg'=>'Please select at least 1 user');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if(!isset($formData['requirement']) || $formData['requirement'] == '' || !in_array($formData['requirement'],array('join_team','stims','dues'))) {
        $responseArr = array('status'=>false, 'msg'=>'Invalid requirement');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      $season = FrcPortal\Season::find($season_id);
      $array = array();
      $req = $formData['requirement'];
      $users = $formData['users'];
      foreach($users as $user) {
        $user_id = $user['user_id'];
        $cur = isset($user['annual_requirements'][0][$req]) ? $user['annual_requirements'][0][$req] : false;
        $new = !$cur;
        $reqUpdate = FrcPortal\AnnualRequirement::updateOrCreate(['season_id' => $season_id, 'user_id' => $user_id], [$req => $new]);
      }
      $season = FrcPortal\User::with(['annual_requirements' => function ($query) use ($season_id) {
                          $query->where('season_id','=',$season_id);
                        }])->get();
      $responseArr = array('status'=>true, 'msg'=>'Annual Requirements Updated', 'data' => $season);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->delete('', function ($request, $response, $args) {
      //$authToken = checkToken(true,true);
      //$user_id = $authToken['data']['user_id'];
      //checkAdmin($user_id, $die = true);
      $season_id = $args['season_id'];
      $season = FrcPortal\Season::destroy($season_id);
      if($season) {
        $responseArr = array('status'=>true, 'msg'=>'Season Deleted', 'data' => $season);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong', 'data' => $season);
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
    if(!isset($formData['year']) || $formData['year'] == '') {
      $responseArr = array('status'=>false, 'msg'=>'Year cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(!isset($formData['game_name']) || $formData['game_name'] == '') {
      $responseArr = array('status'=>false, 'msg'=>'Name cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(!isset($formData['start_date']) || $formData['start_date'] == '') {
      $responseArr = array('status'=>false, 'msg'=>'Start Date cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(!isset($formData['bag_day']) || $formData['bag_day'] == '') {
      $responseArr = array('status'=>false, 'msg'=>'Bag Date cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(!isset($formData['end_date']) || $formData['end_date'] == '') {
      $responseArr = array('status'=>false, 'msg'=>'End Date cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    $spreadsheetId = getSeasonMembershipForm($formData['year']);
    $start_date = new DateTime($formData['start_date']);
    $bag_day = new DateTime($formData['bag_day']);
    $end_date = new DateTime($formData['end_date']);

    $season = FrcPortal\Season::where('year', $formData['year'])->count();
    if($season == 0) {
      $newSeason = new FrcPortal\Season();
      $newSeason->year = $formData['year'];
      $newSeason->game_name = $formData['game_name'];
      $newSeason->start_date = $start_date->format('Y-m-d');
      $newSeason->bag_day = $bag_day->format('Y-m-d'." 23:59:59");
      $newSeason->end_date = $end_date->format('Y-m-d'." 23:59:59");
      $newSeason->join_spreadsheet = $spreadsheetId==false ? '':$spreadsheetId;
      $newSeason->game_logo = !is_null($formData['game_logo']) ? $formData['game_logo']:'';
      if($newSeason->save()) {
        $limit = 10;
        $totalNum = FrcPortal\Season::count();
        $seasons = FrcPortal\Season::orderBy('year','DESC')->limit($limit)->get();
        $data = array();
        $data['data'] = $seasons;
        $data['total'] = $totalNum;
        $data['maxPage'] = ceil($totalNum/$limit);
        $responseArr = array('status'=>true, 'msg'=>$formData['year'].' season created', 'data'=>$data);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong');
      }
    } else {
      $responseArr = array('status'=>false, 'msg'=>'Season for '.$formData['year'].' already exists');
    }
    $response = $response->withJson($responseArr);
    return $response;
  });
});

















?>

<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/seasons', function () {
  $this->get('', function ($request, $response, $args) {
    $seasons = array();
  	$data = array();

    $filter = $request->getParam('filter') !== null ? $request->getParam('filter'):'';
    $limit = $request->getParam('limit') !== null ? $request->getParam('limit'):10;
    $order = $request->getParam('order') !== null ? $request->getParam('order'):'-year';
    $page = $request->getParam('page') !== null ? $request->getParam('page'):1;
    $listOnly = $request->getParam('listOnly') !== null && $request->getParam('listOnly')==true ? true:false;

    $totalNum = 0;
    $seasons = FrcPortal\Season::where('game_name','LIKE','%'.$filter.'%')->orWhere('year','LIKE','%'.$filter.'%');
    $totalNum = count($seasons->get());

    $orderBy = '';
  	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
  	if(in_array($orderCol,array('game_name','year','start_date','bag_day','end_date'))) {
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
    $seasons = $seasons->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();

    $data['data'] = $seasons;
    $data['total'] = $totalNum;
    $data['maxPage'] = $limit > 0 ? ceil($totalNum/$limit) : 0;
    $data['status'] = true;
    $data['msg'] = '';
    if($listOnly) {
      $data = $seasons;
    }

    $response = $response->withJson($data);
    return $response;
  });
  $this->group('/{season_id:[a-z0-9]{13}}', function () {
    $this->get('', function ($request, $response, $args) {
      $season_id = $args['season_id'];
      $reqsBool = $request->getParam('requirements') !== null && $request->getParam('requirements')==true ? true:false;
      $season = FrcPortal\Season::find($season_id);
      if($reqsBool) {
        $season->users = getUsersAnnualRequirements($season_id);
      }
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $season);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->get('/annualRequirements', function ($request, $response, $args) {
      $season_id = $args['season_id'];
      $season = getUsersAnnualRequirements($season_id);
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $season);
    $response = $response->withJson($responseArr);
    return $response;
    });
    $this->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if(!FrcPortal\Auth::isAdmin()) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }
      $season_id = $args['season_id'];

      $season = FrcPortal\Season::find($season_id);
      if(!is_null($formData['start_date'])) {
        $start_date = new DateTime($formData['start_date']);
        $season->start_date = $start_date->format('Y-m-d');
      }
      if(!is_null($formData['bag_day'])) {
        $bag_day = new DateTime($formData['bag_day']);
        $season->bag_day = $bag_day->format('Y-m-d'." 23:59:59");
      }
      if(!is_null($formData['end_date'])) {
        $end_date = new DateTime($formData['end_date']);
        $season->end_date = $end_date->format('Y-m-d'." 23:59:59");
      }
      $season->game_logo = $formData['game_logo'];
      $season->game_name = $formData['game_name'];
      $season->hour_requirement = $formData['hour_requirement'];
      $season->hour_requirement_week = $formData['hour_requirement_week'];
      $season->game_logo = $formData['game_logo'];
      if($season->save()) {
        $responseArr = array('status'=>true, 'msg'=>'Season Information Saved', 'data' => $season);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong', 'data' => $season);
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('/updateMembershipForm', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if(!FrcPortal\Auth::isAdmin()) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }
      $season_id = $args['season_id'];

      $responseArr = updateSeasonMembershipForm($season_id);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('/pollMembershipForm', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if(!FrcPortal\Auth::isAdmin()) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }
      $season_id = $args['season_id'];

      $poll = updateSeasonRegistrationFromForm($season_id);
      $season = getUsersAnnualRequirements($season_id);
      $responseArr = array('status'=>true, 'msg'=>'Latest data dowwnloaded from Google form', 'data' => $season);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('/toggleAnnualReqs', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if(!FrcPortal\Auth::isAdmin()) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }
      $season_id = $args['season_id'];

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
        $cur = isset($user['annual_requirements'][$req]) ? $user['annual_requirements'][$req] : false;
        $new = !$cur;
        $reqUpdate = FrcPortal\AnnualRequirement::updateOrCreate(['season_id' => $season_id, 'user_id' => $user_id], [$req => $new]);
      }
      $season = getUsersAnnualRequirements($season_id);
      $responseArr = array('status'=>true, 'msg'=>'Annual Requirements Updated', 'data' => $season);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->delete('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = array(
        'status' => false,
        'msg' => 'Something went wrong',
        'data' => null
      );
      if(!FrcPortal\Auth::isAdmin()) {
        $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
        $response = $response->withJson($responseArr,403);
        return $response;
      }
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
    $userId = FrcPortal\Auth::user()->user_id;
    $formData = $request->getParsedBody();
    $responseArr = array(
      'status' => false,
      'msg' => 'Something went wrong',
      'data' => null
    );
    if(!FrcPortal\Auth::isAdmin()) {
      $responseArr = array('status'=>false, 'msg'=>'Unauthorized');
      $response = $response->withJson($responseArr,403);
      return $response;
    }

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
    $spreadsheetId = $spreadsheetId['status'] != false ? $spreadsheetId['data']['join_spreadsheet']:'';
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
      $newSeason->join_spreadsheet = $spreadsheetId;
      $newSeason->game_logo = !isset($formData['game_logo']) && !is_null($formData['game_logo']) ? $formData['game_logo']:'';
      if($newSeason->save()) {
        $limit = 10;
        $totalNum = FrcPortal\Season::count();
        $seasons = FrcPortal\Season::orderBy('year','DESC')->limit($limit)->get();
        $data = array();
        $data['results'] = $seasons;
        $data['total'] = $totalNum;
        $data['maxPage'] = ceil($totalNum/$limit);
        $responseArr = array('status'=>true, 'msg'=>$formData['year'].' season created', 'data'=>$data);
        //Send notifications
        $msgData = array(
          'slack' => array(
            'title' => 'New Season Created',
            'body' => 'The '.$newSeason->year.' FRC Season '.$newSeason->game_name.' has been created in the Team Portal.'
          ),
          'email' => array(
            'subject' => 'New Season Created',
            'content' =>  'The '.$newSeason->year.' FRC Season '.$newSeason->game_name.' has been created in the Team Portal.'
          )
        );
        sendMassNotifications($type = 'new_season', $msgData);
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

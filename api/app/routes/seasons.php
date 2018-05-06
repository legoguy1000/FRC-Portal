<?php
use \DateTime;
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
  $this->get('/{season_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $season_id = $args['season_id'];
    $season = FrcPortal\Season::find($season_id);
    $response = $response->withJson($season);
    return $response;
  });
  $this->group('/{year:[0-9]{4}}', function () {
    $this->get('/topHourUsers', function ($request, $response, $args) {
      $year = $args['year'];
      $season = FrcPortal\Season::where('year',$year)->get();
      $seasons = FrcPortal\AnnualRequirement::with('users')->where('season_id',$season[0]->season_id)->get();
      $seasons = $seasons->sortByDesc('total_hours')->values()->slice(0,5);
      $response = $response->withJson($seasons);
      return $response;
    });
  });
  $this->post('', function ($request, $response, $args) {
    //$authToken = checkToken(true,true);
    //$user_id = $authToken['data']['user_id'];
    //checkAdmin($user_id, $die = true);
    $formData = $request->getParsedBody();
    if(!isset($formData['year']) || $formData['year'] == '') {
    	//die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Year cannot be blank!')));
    }
    if(!isset($formData['game_name']) || $formData['game_name'] == '') {
    //	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Name cannot be blank!')));
    }
    if(!isset($formData['start_date']) || $formData['start_date'] == '') {
    //	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Start Date cannot be blank!')));
    }
    if(!isset($formData['bag_day']) || $formData['bag_day'] == '') {
    //	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'Bag Date cannot be blank!')));
    }
    if(!isset($formData['end_date']) || $formData['end_date'] == '') {
    //	die(json_encode(array('status'=>false, 'type'=>'warning', 'msg'=>'End Date cannot be blank!')));
    }
    $spreadsheetId = getSeasonMembershipForm($formData['year']);
  	$spreadsheetId = $spreadsheetId==false ? '':$spreadsheetId;

    $start_date = new DateTime($formData['start_date']);
    $bag_day = new DateTime($formData['bag_day']);
    $end_date = new DateTime($formData['end_date']);

    $season = FrcPortal\Season::firstOrCreate(
      ['year' => $formData['year']],
      ['season_id' => uniqid(), 'game_name' => $formData['game_name'], 'start_date' => $start_date->format('Y-m-d'),
       'bag_day' => $bag_day->format('Y-m-d'." 23:59:59"), 'end_date' => $end_date->format('Y-m-d'." 23:59:59"),
       'join_spreadsheet' => $spreadsheetId, 'game_logo' => $formData['game_logo']]
    );
    if($season) {
      $seasons = FrcPortal\Season::all();
      $responseArr = array('status'=>true, 'msg'=>$formData['year'].' season created', 'data'=>$season);
    }
    $response = $response->withJson($responseArr);
    return $response;
  });
  $this->put('/{season_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $season_id = $args['season_id'];

    return $response;
  });
  $this->delete('/{season_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $season_id = $args['season_id'];

    return $response;
  });
});

















?>

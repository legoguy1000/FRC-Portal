<?php
use Illuminate\Database\Capsule\Manager as DB;
use Slim\Routing\RouteCollectorProxy;

$app->group('/seasons', function(RouteCollectorProxy $group) {
  $group->get('', function ($request, $response, $args) {
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
  })->setName('Get Seasons');
  $group->post('', function ($request, $response, $args) {
    $userId = FrcPortal\Utilities\Auth::user()->user_id;
    $formData = $request->getParsedBody();
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    if(!FrcPortal\Utilities\Auth::isAdmin()) {
      return unauthorizedResponse($response);
    }
    if(empty($formData['year'])) {
      $responseArr = array('status'=>false, 'msg'=>'Year cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    $no_bagday = $formData['year'] > 2019 ? true:false;
    if(empty($formData['game_name'])) {
      $responseArr = array('status'=>false, 'msg'=>'Name cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(empty($formData['start_date'])) {
      $responseArr = array('status'=>false, 'msg'=>'Start Date cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(!$no_bagday && empty($formData['bag_day'])) {
      $responseArr = array('status'=>false, 'msg'=>'Bag Date cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(empty($formData['end_date'])) {
      $responseArr = array('status'=>false, 'msg'=>'End Date cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    try {
      $spreadsheetId = getSeasonMembershipForm($formData['year']);
    } catch (Exception $e) {
      $spreadsheetId = false;
    }
    $spreadsheetId = $spreadsheetId != false ? $spreadsheetId['join_spreadsheet']:'';
    $start_date = new DateTime($formData['start_date']);
    $end_date = new DateTime($formData['end_date']);
    $bag_day = $no_bagday ? null:new DateTime($formData['bag_day']);
    $season = FrcPortal\Season::where('year', $formData['year'])->count();
    if($season == 0) {
      $newSeason = new FrcPortal\Season();
      $newSeason->year = $formData['year'];
      $newSeason->game_name = $formData['game_name'];
      $newSeason->start_date = $start_date->format('Y-m-d');
      $newSeason->bag_day = $no_bagday ? null:$bag_day->format('Y-m-d'." 23:59:59");
      $newSeason->end_date = $end_date->format('Y-m-d'." 23:59:59");
      $newSeason->join_spreadsheet = $spreadsheetId;
      $newSeason->membership_form_map = array(
        'email' => 'email address',
        'fname' => 'first name',
        'lname' => 'last name',
        'user_type' => 'member type',
        'grad_year' => 'year of graduation',
        'school' => 'school',
        'pin_number' => 'student id',
        'phone' => 'phone'
      );
      $newSeason->membership_form_sheet = 'Form Responses 1';
      $newSeason->game_logo = !empty($formData['game_logo']) ? $formData['game_logo']:'';
      if($newSeason->save()) {
        $responseArr = array('status'=>true, 'msg'=>$formData['year'].' season created', 'data'=>$newSeason);
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
  })->setName('Add Season');
  $group->group('/{season_id:[a-z0-9]{13}}', function(RouteCollectorProxy $group) {
    $group->get('', function ($request, $response, $args) {
      $season_id = $args['season_id'];
      //Season passed from middleware
      $season = $request->getAttribute('season');
      $reqsBool = $request->getParam('requirements') !== null && $request->getParam('requirements')==true ? true:false;
      //$season = FrcPortal\Season::find($season_id);
      if($reqsBool) {
        $season->users = getUsersAnnualRequirements($season_id);
      }
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $season);
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Get Season');
    $group->get('/annualRequirements', function ($request, $response, $args) {
      $season_id = $args['season_id'];
      $season = getUsersAnnualRequirements($season_id);
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $season);
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Get Season Annual Requirements');
    $group->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $season_id = $args['season_id'];
      //Season passed from middleware
      $season = $request->getAttribute('season');
      //$season = FrcPortal\Season::find($season_id);
      $no_bagday = $season->year > 2019 ? true:false;
      if(!empty($formData['start_date'])) {
        $start_date = new DateTime($formData['start_date']);
        $season->start_date = $start_date->format('Y-m-d');
      }
      if(!$no_bagday && !empty($formData['bag_day'])) {
        $bag_day = new DateTime($formData['bag_day']);
        $season->bag_day = $bag_day->format('Y-m-d'." 23:59:59");
      }
      if(!empty($formData['end_date'])) {
        $end_date = new DateTime($formData['end_date']);
        $season->end_date = $end_date->format('Y-m-d'." 23:59:59");
      }
      $season->game_logo = $formData['game_logo'];
      $season->game_name = $formData['game_name'];
      $season->hour_requirement = $formData['hour_requirement'];
      $season->hour_requirement_week = $formData['hour_requirement_week'];
      $season->game_logo = $formData['game_logo'];
      $season->join_spreadsheet = $formData['join_spreadsheet'];
      $season->membership_form_map = $formData['membership_form_map'];
      $season->membership_form_sheet = $formData['membership_form_sheet'];

      if($season->save()) {
        $responseArr = array('status'=>true, 'msg'=>'Season Information Saved', 'data' => $season);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong', 'data' => $season);
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update Season');
    $group->put('/updateMembershipForm', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong updating season membership form', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      //Season passed from middleware
      $season = $request->getAttribute('season');
      try {
        $result = getSeasonMembershipForm($season->year);
        if(is_array($result) && array_key_exists('join_spreadsheet',$result) && $result['join_spreadsheet'] != '') {
          $season->join_spreadsheet = $result['join_spreadsheet'];
          if($season->save()) {
            $responseArr = standardResponse($status = true, $msg = $season->year.' membership form added', $data = $season);
          }
        } elseif ($result['join_spreadsheet'] == '') {
          $responseArr['msg'] = 'No membership form found for '.$season->year;
        }
      } catch (Exception $e) {
        insertLogs('Warning', 'Something went wrong updating season membership form');
        $responseArr['error'] = handleExceptionMessage($e);
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update Season Membership Form');
    $group->put('/pollMembershipForm', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      //Season passed from middleware
      $season = $request->getAttribute('season');
      if($season->updateSeasonRegistrationFromForm()) {
        $responseArr['status'] = true;
        $responseArr['msg'] = 'Latest data downloaded from Google form';
        $responseArr['data'] = getUsersAnnualRequirements($season->season_id);
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Poll Season Membership Form');
    $group->put('/toggleAnnualReqs', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $season_id = $args['season_id'];
      //Season passed from middleware
      $season = $request->getAttribute('season');
      //$season = FrcPortal\Season::find($season_id);
      if(empty($formData['users']) || !is_array($formData['users'])) {
        $responseArr = array('status'=>false, 'msg'=>'Please select at least 1 user');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if(empty($formData['requirement']) || !in_array($formData['requirement'],array('join_team','stims','dues'))) {
        $responseArr = array('status'=>false, 'msg'=>'Invalid requirement');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      $array = array();
      $req = $formData['requirement'];
      $users = $formData['users'];
      foreach($users as $user) {
        //$user_id = $user['user_id'];
        $reqArr = FrcPortal\AnnualRequirement::firstOrNew(['season_id' => $season_id, 'user_id' => $user]);
        //$reqArr = FrcPortal\AnnualRequirement::where('season_id',$season_id)->where('user_id',$user)->first();
        $cur = !empty($reqArr->$req) ? $reqArr->$req : false;
        $reqArr->$req = !$cur;
        if($req == 'stims' || $req == 'dues') {
          $reqArr->{$req.'_date'} = date('Y-m-d H:i:s');
        }
        $reqArr->save();
      }
      $season = getUsersAnnualRequirements($season_id);
      $responseArr = array('status'=>true, 'msg'=>'Annual Requirements Updated', 'data' => $season);
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Toggle Annual Requirements');
    $group->delete('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
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
    })->setName('Delete Season');
  })->add(function ($request, $response, $next) {
    //Season Midddleware to pull season data
    // get the route from the request
    $route = FrcPortal\Utilities\Auth::getRoute();
    if (!$route) {
        // no route matched
        return $next($request, $response);
    }
    $args = $route->getArguments();
    $season_id = $args['season_id'];
    $season = FrcPortal\Season::find($season_id);
    if(!empty($season)) {
      $request = $request->withAttribute('season', $season);
      $response = $next($request, $response);
    } else {
      $response = notFoundResponse($response, $msg = 'Season not found');
    }
  	return $response;
  });
});

















?>

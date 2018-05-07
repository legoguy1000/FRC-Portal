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
        $user = FrcPortal\Event::with(['event_requirements' => function ($query) use ($user_id) {
                  $query->where('user_id','=',$user_id); // fields from comments table,
                }])->where('event_id','=',$event_id)->get();
        $responseArr = array('status'=>true, 'msg'=>'', 'data' => $user);
        $response = $response->withJson($responseArr);
        return $response;
      });
    });
    $this->put('', function ($request, $response, $args) {
      $user_id = $args['user_id'];
      $formData = $request->getParsedBody();
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
      $user_id = $args['user_id'];
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

<?php
$app->group('/users', function () {
  $this->get('', function ($request, $response, $args) {
    $users = array();
  	$data = array();

    $filter = $request->getParam('filter') !== null ? $request->getParam('filter'):'';
    $limit = $request->getParam('limit') !== null ? $request->getParam('limit'):10;
    $order = $request->getParam('order') !== null ? $request->getParam('order'):'full_name';
    $page = $request->getParam('page') !== null ? $request->getParam('page'):1;
    $listOnly = $request->getParam('listOnly') !== null && $request->getParam('listOnly')==true ? true:false;

    $whereArr = array();
    if($filter != '') {
      if($filter == strtolower('active')) {
        $totalNum = FrcPortal\User::where('users.status','=','1');
      } elseif($filter == strtolower('inactive')) {
        $totalNum = FrcPortal\User::where('users.status','=','0');
      } else {
        $totalNum = FrcPortal\User::where('users.email','like','%'.$filter.'%');
        $totalNum->orWhere('users.user_type','like','%'.$filter.'%');
        $totalNum->orWhere('users.gender','like','%'.$filter.'%');
        $totalNum->orHaving('full_name','like','%'.$filter.'%');
        $totalNum->orHaving('student_grade','like','%'.$filter.'%');
  //      $queryArr[] = '(school_name LIKE '.db_quote('%'.$filter.'%').')';
    //    $queryArr[] = '(abv LIKE '.db_quote('%'.$filter.'%').')';
      }
      $totalNum = count($totalNum->get());
    } else {
      $totalNum = FrcPortal\User::count();
    }


    $orderBy = '';
  	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
  	if(in_array($orderCol,array('full_name','fname','lname','email','user_type','gender','schoool_name'))) {
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

    if(count($whereArr) > 0) {
      $users = FrcPortal\User::with('school')->orHaving($whereArr)->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();
    } else {
      $users = FrcPortal\User::with('school')->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();
    }
    $data['data'] = $users;
    $data['total'] = $totalNum;
    $data['maxPage'] = $limit > 0 ? ceil($totalNum/$limit) : 0;

    if($listOnly) {
      $data = $users;
    }

    $response = $response->withJson($data);
    return $response;
  });
  $this->post('', function ($request, $response, $args) {
    //$user = FrcPortal\User::Create(['user_id'=>uniqid(),'fname' => "Ahmed", 'lname' => "Ahmed", 'email' => "ahmed.khan@lbs.com"]);
    //$response->getBody()->write(json_encode('Add new user'));
    //return $response;
  });
  $this->get('/{user_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $user_id = $args['user_id'];
    $user = FrcPortal\User::with('schools')->find($user_id);
    $response = $response->withJson($user);
    return $response;
  });
  $this->put('/{user_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $user_id = $args['user_id'];
    $response->getBody()->write(json_encode('Update User '.$user_id));
    return $response;
  });
  $this->delete('/{user_id:[a-z0-9]{13}}', function ($request, $response, $args) {
    $user_id = $args['user_id'];
    $response->getBody()->write(json_encode('Delete User '.$user_id));
    return $response;
  });
});

















?>

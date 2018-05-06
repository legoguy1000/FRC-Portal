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
  			$queryArr[] = '(users.gender LIKE "%'.$filter.'%")';
  			$queryArr[] = '(full_name LIKE "%'.$filter.'%")';
  			//$queryArr[] = '(school_name LIKE '.db_quote('%'.$filter.'%').')';
  			//$queryArr[] = '(abv LIKE '.db_quote('%'.$filter.'%').')';
  			$queryArr[] = '(student_grade LIKE "%'.$filter.'%")';
  		}
  	}

  	if(count($queryArr) > 0) {
  		$queryStr = implode(' OR ',$queryArr);
  	}

    $users = FrcPortal\User::havingRaw($queryStr)->get();
    $totalNum = count($users);


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

    $users = FrcPortal\User::with('school')->havingRaw($queryStr)->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();


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

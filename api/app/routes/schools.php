<?php
use Illuminate\Database\Capsule\Manager as DB;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;


$app->group('/schools', function(RouteCollectorProxy $group) {
  $group->get('', function ($request, $response, $args) {
    $schools = array();
  	$data = array();

    $filter = $request->getParam('filter') !== null ? $request->getParam('filter'):'';
    $limit = $request->getParam('limit') !== null ? $request->getParam('limit'):10;
    $order = $request->getParam('order') !== null ? $request->getParam('order'):'-student_count';
    $page = $request->getParam('page') !== null ? $request->getParam('page'):1;
    $listOnly = $request->getParam('listOnly') !== null && $request->getParam('listOnly')==true ? true:false;

    $totalNum = 0;
    $schools = FrcPortal\School::select()->leftJoin(DB::raw('(SELECT school_id as sid, COUNT(*) as student_count FROM users GROUP BY sid) sc'), 'sc.sid', '=', 'schools.school_id')->addSelect(DB::raw('IFNULL(sc.student_count,0) as student_count'));
    if($filter != '') {
      $schools = $schools->orHavingRaw('school_name LIKE ?',array('%'.$filter.'%'));
      $schools = $schools->orHavingRaw('abv LIKE ?',array('%'.$filter.'%'));
    }
    $totalNum = count($schools->get());

    $orderBy = '';
  	$orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
    if(in_array($orderCol,array('school_name','abv','student_count'))) {
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
    $schools = $schools->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();


    $data['data'] = $schools;
    $data['total'] = $totalNum;
    $data['maxPage'] = $limit > 0 ? ceil($totalNum/$limit) : 0;
    $data['status'] = true;
    $data['msg'] = '';
    if($listOnly) {
      $data = $schools;
    }

    $response = $response->withJson($data);
    return $response;
  })->setName('Get Schools');
  $group->post('', function ($request, $response, $args) {
    $userId = FrcPortal\Utilities\Auth::user()->user_id;
    $formData = $request->getParsedBody();
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    if(!FrcPortal\Utilities\Auth::isAdmin()) {
      return unauthorizedResponse($response);
    }

    if(empty($formData['school_name'])) {
      $responseArr = array('status'=>false, 'msg'=>'School name cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(empty($formData['abv'])) {
      $responseArr = array('status'=>false, 'msg'=>'Abbreviation cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }

    $school = FrcPortal\School::where('school_name', $formData['school_name'])->count();
    if($school == 0) {
      $newSchool = new FrcPortal\School();
      $newSchool->school_name = $formData['school_name'];
      $newSchool->abv = $formData['abv'];
      $newSchool->logo_url = !empty($formData['logo_url']) ? $formData['logo_url']:'';
      if($newSchool->save()) {
        $responseArr = array('status'=>true, 'msg'=>$formData['school_name'].' created', 'data'=>$newSchool);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong');
      }
    } else {
      $responseArr = array('status'=>false, 'msg'=>$formData['school_name'].' already exists');
    }
    $response = $response->withJson($responseArr);
    return $response;
  })->setName('Add School');
  $group->group('/{school_id:[a-z0-9]{13}}', function(RouteCollectorProxy $group) {
    $group->get('', function ($request, $response, $args) {
      $school_id = $args['school_id'];
      //School passed from middleware
      $school = $request->getAttribute('school');
      //$school = FrcPortal\School::find($school_id);
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $school);
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Get School');
    $group->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      if(empty($formData['school_name'])) {
        $responseArr = array('status'=>false, 'msg'=>'School name cannot be blank!');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if(empty($formData['abv'])) {
        $responseArr = array('status'=>false, 'msg'=>'Abbreviation cannot be blank!');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      $school_id = $args['school_id'];
      //School passed from middleware
      $school = $request->getAttribute('school');
      //$school = FrcPortal\School::find($school_id);
      if($school->school_name != $formData['school_name']) {
        $school_count = FrcPortal\School::where('school_name', $formData['school_name'])->count();
        if($school_count == 0) {
          $school->school_name = $formData['school_name'];
        } else {
          $responseArr = array('status'=>false, 'msg'=>$formData['school_name'].' already exists');
          $response = $response->withJson($responseArr,400);
        }
      }
      $school->abv = $formData['abv'];
      $school->logo_url = !empty($formData['logo_url']) ? $formData['logo_url']:'';
      if($school->save()) {
        $responseArr = array('status'=>true, 'msg'=>$formData['school_name'].' updated', 'data'=>$school);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong');
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Update School');
    $group->delete('', function ($request, $response, $args) {
      $userId = FrcPortal\Utilities\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Utilities\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $school_id = $args['school_id'];
      $school = FrcPortal\School::destroy($school_id);
      if($school) {
        $responseArr = array('status'=>true, 'msg'=>'School Deleted', 'data' => null);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong', 'data' => $school);
      }
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Delete School');
  })->add(function(Request $request, RequestHandler $handler) {
    //Event Midddleware to pull event data
    // get the route from the request
    $route = FrcPortal\Utilities\Auth::getRoute();
    if (!$route) {
        // no route matched
        return $handler->handle($request);
    }
    $args = $route->getArguments();
    $school_id = $args['school_id'];
    $school = FrcPortal\School::find($school_id);
    if(!is_null($school)) {
      $request = $request->withAttribute('school', $school);
      return $handler->handle($request);
    } else {
      $response = $handler->handle($request);
      $response = notFoundResponse($response, $msg = 'School not found');
    }
    return $handler->handle($request);
  });
});

















?>

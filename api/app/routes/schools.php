<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/schools', function () {
  $this->get('', function ($request, $response, $args) {
    $schools = array();
  	$data = array();

    $filter = $request->getParam('filter') !== null ? $request->getParam('filter'):'';
    $limit = $request->getParam('limit') !== null ? $request->getParam('limit'):10;
    $order = $request->getParam('order') !== null ? $request->getParam('order'):'-student_count';
    $page = $request->getParam('page') !== null ? $request->getParam('page'):1;
    $listOnly = $request->getParam('listOnly') !== null && $request->getParam('listOnly')==true ? true:false;

    $totalNum = 0;
    $schools = FrcPortal\School::leftJoin(DB::raw('(SELECT school_id, COUNT(*) as student_count FROM users GROUP BY school_id) sc'), 'sc.school_id', '=', 'schools.school_id')->addSelect('schools.*',DB::raw('IFNULL(sc.student_count,0) as student_count'));
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
  });
  $this->group('/{school_id:[a-z0-9]{13}}', function () {
    $this->get('', function ($request, $response, $args) {
      $school_id = $args['school_id'];
      $school = FrcPortal\School::find($school_id);
      $responseArr = array('status'=>true, 'msg'=>'', 'data' => $school);
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->put('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }

      if(!isset($formData['school_name']) || $formData['school_name'] == '') {
        $responseArr = array('status'=>false, 'msg'=>'School name cannot be blank!');
        $response = $response->withJson($responseArr,400);
        return $response;
      }
      if(!isset($formData['abv']) || $formData['abv'] == '') {
        $responseArr = array('status'=>false, 'msg'=>'Abbreviation cannot be blank!');
        $response = $response->withJson($responseArr,400);
        return $response;
      }

      $school_id = $args['school_id'];
      $school = FrcPortal\School::find($school_id);
      if($school->school_name != $formData['school_name']) {
        $school_count = FrcPortal\School::where('school_name', $formData['school_name'])->count();
        if($school == 0) {
          $school->school_name = $formData['school_name'];
        } else {
          $responseArr = array('status'=>false, 'msg'=>$formData['school_name'].' already exists');
          $response = $response->withJson($responseArr,400);
        }
      }
      $school->abv = $formData['abv'];
      $school->logo_url = !isset($formData['logo_url']) && !is_null($formData['logo_url']) ? $formData['logo_url']:'';
      if($school->save()) {
        $responseArr = array('status'=>true, 'msg'=>$formData['school_name'].' updated', 'data'=>$school);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong');
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
    $this->delete('', function ($request, $response, $args) {
      $userId = FrcPortal\Auth::user()->user_id;
      $formData = $request->getParsedBody();
      $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
      if(!FrcPortal\Auth::isAdmin()) {
        return unauthorizedResponse($response);
      }
      $school_id = $args['school_id'];
      $school = FrcPortal\School::destroy($school_id);
      if($season) {
        $limit = 10;
        $totalNum = FrcPortal\School::count();
        $schools = FrcPortal\School::orderBy('year','DESC')->limit($limit)->get();
        $data = array();
        $data['results'] = $schools;
        $data['total'] = $totalNum;
        $data['maxPage'] = ceil($totalNum/$limit);
        $responseArr = array('status'=>true, 'msg'=>'School Deleted', 'data' => $data);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong', 'data' => $school);
      }
      $response = $response->withJson($responseArr);
      return $response;
    });
  });
  $this->post('', function ($request, $response, $args) {
    $userId = FrcPortal\Auth::user()->user_id;
    $formData = $request->getParsedBody();
    $responseArr = standardResponse($status = false, $msg = 'Something went wrong', $data = null);
    if(!FrcPortal\Auth::isAdmin()) {
      return unauthorizedResponse($response);
    }

    if(!isset($formData['school_name']) || $formData['school_name'] == '') {
      $responseArr = array('status'=>false, 'msg'=>'School name cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }
    if(!isset($formData['abv']) || $formData['abv'] == '') {
      $responseArr = array('status'=>false, 'msg'=>'Abbreviation cannot be blank!');
      $response = $response->withJson($responseArr,400);
      return $response;
    }

    $school = FrcPortal\School::where('school_name', $formData['school_name'])->count();
    if($school == 0) {
      $newSchool = new FrcPortal\School();
      $newSchool->school_name = $formData['school_name'];
      $newSchool->abv = $formData['abv'];
      $newSchool->logo_url = !isset($formData['logo_url']) && !is_null($formData['logo_url']) ? $formData['logo_url']:'';
      if($newSchool->save()) {
        $limit = 10;
        $totalNum = FrcPortal\School::count();
        $schools = FrcPortal\School::orderBy('year','DESC')->limit($limit)->get();
        $data = array();
        $data['results'] = $schools;
        $data['total'] = $totalNum;
        $data['maxPage'] = ceil($totalNum/$limit);
        $responseArr = array('status'=>true, 'msg'=>$formData['school_name'].' created', 'data'=>$data);
      } else {
        $responseArr = array('status'=>false, 'msg'=>'Something went wrong');
      }
    } else {
      $responseArr = array('status'=>false, 'msg'=>$formData['school_name'].' already exists');
    }
    $response = $response->withJson($responseArr);
    return $response;
  });
});

















?>

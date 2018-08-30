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
    $totalNum = count($events->get());

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
  $this->post('', function ($request, $response, $args) {

    $response = $response->withJson($responseArr);
    return $response;
  });
});

















?>

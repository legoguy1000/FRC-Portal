<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/logs', function () {
  $this->get('', function ($request, $response, $args) {
    if(!FrcPortal\Auth::isAdmin()) {
      return unauthorizedResponse($response);
    }
    $logs = array();
    $data = array();
    $searchProperties = array(
      'level' => '',
      'user_id' => '',
    );
    $defaults = array(
      'filter' => '',
      'limit' => 10,
      'order' => '-created_at',
      'page' => 1,
    );
    $inputs = checkSearchInputs($request, $defaults);
    $filter = $inputs['filter'];
    $limit = $inputs['limit'];
    $order = $inputs['order'];
    $page = $inputs['page'];
    $listOnly = $request->getParam('listOnly') !== null && $request->getParam('listOnly')==true ? true:false;
    $search = $request->getParam('search') !== null ? $request->getParam('search'):$searchProperties;

    $queryArr = array();
    $queryArr2 = array();
    if(isset($search['level']) && $search['level'] != '') {
      $queryArr2[] = array('level', '=', $search['level']);
    }
    if(isset($search['user_id']) && $search['user_id'] != '') {
      $queryArr2[] = array('user_id', '=', $search['user_id']);
    //  die($bool );
    }
    $totalNum = 0;
    $logs = FrcPortal\Log::with('user')->where($queryArr2);
    if($filter != '') {
      $logs = $logs->orHavingRaw('level LIKE ?',array('%'.$filter.'%'));
      $logs = $logs->orHavingRaw('user_id LIKE ?',array('%'.$filter.'%'));
    }
    $totalNum = count($logs->get());
    $orderBy = '';
    $orderCol = $order[0] == '-' ? str_replace('-','',$order) : $order;
    if(in_array($orderCol,array('level','user_id','create_at'))) {
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

    $logs = $logs->orderBy($orderCol,$orderBy)->offset($offset)->limit($limit)->get();

    $data['data'] = $logs;
    $data['total'] = $totalNum;
    $data['maxPage'] = $limit > 0 ? ceil($totalNum/$limit) : 0;
    $data['status'] =true;
    $data['msg'] = $queryArr;
    if($listOnly) {
      $data = $logs;
    }
    //sendToLogs('information', 'Loaded /logs endpoint');
    $response = $response->withJson($data);
    return $response;
  });
});


















?>

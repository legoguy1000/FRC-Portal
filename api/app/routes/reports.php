<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Facades\Input as Input;
$app->group('/reports', function () {
  $this->get('/avgHrsPerPersonPerYear', function ($request, $response, $args) {

    if($request->getParam('start_date') == null|| $request->getParam('start_date') == '' || !is_numeric($request->getParam('start_date'))) {
        $responseArr = array('status'=>false, 'msg'=>'Invalid Start Date.');
        $response = $response->withJson($responseArr,400);
        return $response;
    }
    if($request->getParam('end_date') == null || $request->getParam('end_date') == '' || !is_numeric($request->getParam('end_date'))) {
        $responseArr = array('status'=>false, 'msg'=>'Invalid End Date.');
        $response = $response->withJson($responseArr,400);
        return $response;
    }
    $start_date = $request->getParam('start_date');
    $end_date = $request->getParam('end_date');

    $years = array();
    for($i = $start_date; $i <= $end_date; $i++) {
    	$years[] = (integer) $i;
    }
    $series = array('Sum','Avg');
    $data = array();
    foreach($series as $se) {
    	$data[strtolower($se)] = array_fill_keys($years,0);
    }
    $query = 'SELECT SUM(d.hours) as sum, AVG(d.hours) as avg, d.year
              from (SELECT a.user_id, SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600) as hours, year(a.time_in) as year from meeting_hours a WHERE year(a.time_in) BETWEEN :sd AND :ed GROUP BY user_id,year) d
              GROUP BY year';
    $sd = Input::get($start_date);
    $ed = Input::get($end_date);
    $result = DB::select( DB::raw($query), array(
        'sd' => $sd,
        'ed' => $ed,
     ));

    foreach($result as $re) {
    	$year = (integer) $re['year'];
    	$sum = (double) $re['sum'];
    	$avg = (double) $re['avg'];

    	$data['sum'][$year] = $sum;
    	$data['avg'][$year] = $avg;
    }
    $data['sum'] = array_values($data['sum']);
    $data['avg'] = array_values($data['avg']);

    $allData = array(
    	'labels' => $years,
    	'series' => $series,
    	'data' => array_values($data),
    	//'csvData' => metricsCreateCsvData($data, $years, $series)
    );
    $response = $response->withJson($allData);
    return $response;
  });

});

















?>

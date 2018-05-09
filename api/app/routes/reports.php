<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Facades\Input as Input;
$app->group('/reports', function () {
  /**
  * Average Hours per Person per Year
  **/
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
    //$sd = Input::get($start_date);
    //$ed = Input::get($end_date);
    $result = DB::select( DB::raw($query), array(
        'sd' => $start_date,
        'ed' => $end_date,
     ));

    foreach($result as $re) {
    	$year = (integer) $re->year;
    	$sum = (double) $re->sum;
    	$avg = (double) $re->avg;

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
  /**
  * Active Users per Year
  **/
  $this->get('/activeUsersPerYear', function ($request, $response, $args) {

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
    $series = array('Students','Mentors','Males','Females','Senior','Junior','Sophmore','Freshman','Pre-Freshman'); //,'Total'
    $data = array();
    foreach($series as $se) {
    	$data[$se] = array_fill_keys($years,0);
    }
    $query = 'SELECT COUNT(DISTINCT(m.user_id)) as user_count, YEAR(m.time_in) as year, u.user_type
              FROM meeting_hours m
              LEFT JOIN users u USING(user_id)
              WHERE u.user_type <> "" AND YEAR(m.time_in)
              BETWEEN :sd AND :ed
              GROUP BY year,u.user_type';
    $result = DB::select( DB::raw($query), array(
        'sd' => $start_date,
        'ed' => $end_date,
     ));

    foreach($result as $re) {
      $year = (integer) $re->year;
      $user_type = $re->user_type;
      $uc = (integer) $re->user_count;
      $data[$user_type.'s'][$year] = $uc;
    }

    $query = 'SELECT COUNT(DISTINCT(m.user_id)) as user_count, YEAR(m.time_in) as year, u.gender
              FROM meeting_hours m
              LEFT JOIN users u USING(user_id)
              WHERE u.gender <> "" AND YEAR(m.time_in)
              BETWEEN :sd AND :ed
              GROUP BY year,u.gender';
    $result = DB::select( DB::raw($query), array(
        'sd' => $start_date,
        'ed' => $end_date,
     ));
    foreach($result as $re) {
      $year = (integer) $re->year;
      $gender = $re->gender;
      $uc = (integer) $re->user_count;
      $data[$gender.'s'][$year] = $uc;
    }
    $query = 'SELECT CASE
               WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,m.time_in,CONCAT(u.grad_year,"-07-01")) <=12 THEN "Senior"
               WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,m.time_in,CONCAT(u.grad_year,"-07-01")) <=24 THEN "Junior"
               WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,m.time_in,CONCAT(u.grad_year,"-07-01")) <=36 THEN "Sophmore"
               WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,m.time_in,CONCAT(u.grad_year,"-07-01")) <=48 THEN "Freshman"
               WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,m.time_in,CONCAT(u.grad_year,"-07-01")) >48 THEN "Pre-Freshman"
               ELSE ""
              END AS student_grade, COUNT(DISTINCT(m.user_id)) as user_count, YEAR(m.time_in) as year
              FROM meeting_hours m
              LEFT JOIN users u USING(user_id)
              WHERE u.user_type="student" AND YEAR(m.time_in)
              BETWEEN :sd AND :ed
              GROUP BY year,student_grade';
    $result = DB::select( DB::raw($query), array(
        'sd' => $start_date,
        'ed' => $end_date,
     ));
    foreach($result as $re) {
      $year = (integer) $re->year;
      $grade = $re->student_grade;
      $uc = (integer) $re->user_count;
      $data[$grade][$year] = $uc;
    }
    foreach($series as $se) {
    	$data[$se] = array_values($data[$se]);
    }

    $allData = array(
    	'labels' => $years,
    	'series' => $series,
    	'data' => array_values($data),
    	//'csvData' => metricsCreateCsvData($data, $years, $series)
    );
    $response = $response->withJson($allData);
    return $response;
  });
    /**
    * Total Hours per Grade per Year
    **/
    $this->get('/totalHrsPerGradePerYear', function ($request, $response, $args) {

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

      $series = array('Senior','Junior','Sophmore','Freshman','Pre-Freshman','Mentor');
      $data = array();
      foreach($series as $se) {
      	$data[$se] = array_fill_keys($years,0);
      }
      $query = 'SELECT CASE
       WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=0  THEN "Graduated"
       WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=12 THEN "Senior"
       WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=24 THEN "Junior"
       WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=36 THEN "Sophmore"
       WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=48 THEN "Freshman"
       WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) >48 THEN "Pre-Freshman"
       ELSE ""
      END AS student_grade,
      IFNULL(SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600),0) as sum, year(a.time_in) as year
      FROM meeting_hours a
      LEFT JOIN users b USING (user_id)
      WHERE year(a.time_in)
      BETWEEN :sd AND :ed
      GROUP BY year,student_grade';

      $result = DB::select( DB::raw($query), array(
          'sd' => $start_date,
          'ed' => $end_date,
       ));

      foreach($result as $re) {
        $year = (integer) $re->year;
        $student_grade = $re->student_grade;
      	$sum = (double) $re->sum;
        if($student_grade == '') {
          $data['Mentor'][$year] = $sum;
        } else {
          $data[$student_grade][$year] = $sum;
        }
      }
      foreach($series as $se) {
      	$data[$se] = array_values($data[$se]);
      }

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

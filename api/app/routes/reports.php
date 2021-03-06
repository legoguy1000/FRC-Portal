<?php
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Facades\Input as Input;
$app->group('/reports', function () {
  /**
  * Top Hours per Year
  **/
  $this->get('/topHourUsers/{year:[0-9]{4}}', function ($request, $response, $args) {
    $year = $args['year'];
    //$season = FrcPortal\Season::where('year',$year)->first();
    $seasons = FrcPortal\AnnualRequirement::with('users')->whereExists(function ($query) use ($year) {
      $query->select(DB::raw(1))
            ->from('seasons')
            ->whereRaw('annual_requirements.season_id = seasons.season_id')
            ->where('seasons.year',$year);
    })->get();
    //->where('season_id',$season->season_id)->get();
    $seasons = $seasons->sortByDesc('total_hours')->filter(function ($value, $key) {
        return $value['total_hours'] > 0;
    })->values()->slice(0,5);
    $response = $response->withJson($seasons);
    return $response;
  })->setName('Top User Hours by Year Report');
  /**
  * Average Hours per Person per Year
  **/
  $this->get('/hoursPerPersonPerYear', function ($request, $response, $args) {

    $check = checkReportInputs($request, $response, $type = 'range');
    if($check !== true) {
      return $check;
    }
    $start_date = $request->getParam('start_date');
    $end_date = $request->getParam('end_date');

    $series = array('Sum','Avg');
    $init = initializeMultiYearData($start_date, $end_date, $series);
    $years = $init['years'];
    $data = $init['data'];

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

    	$data['Sum'][$year] = $sum;
    	$data['Avg'][$year] = $avg;
    }
    $data['Sum'] = array_values($data['Sum']);
    $data['Avg'] = array_values($data['Avg']);

    $allData = array(
    	'labels' => $years,
    	'series' => $series,
    	'data' => array_values($data),
    	//'csvData' => metricsCreateCsvData($data, $years, $series)
    );
    $response = $response->withJson($allData);
    return $response;
  })->setName('Hours per Person per Year Report');
  /**
  * Active Users per Year
  **/
  $this->get('/activeUsersPerYear', function ($request, $response, $args) {

    $check = checkReportInputs($request, $response, $type = 'range');
    if($check !== true) {
      return $check;
    }
    $start_date = $request->getParam('start_date');
    $end_date = $request->getParam('end_date');

    $series = array('Students','Mentors','Males','Females','Senior','Junior','Sophmore','Freshman','Pre-Freshman'); //,'Total'
    $init = initializeMultiYearData($start_date, $end_date, $series);
    $years = $init['years'];
    $data = $init['data'];

/*
    $query = 'SELECT COUNT(DISTINCT(m.user_id)) as user_count, YEAR(m.time_in) as year, u.user_type
              FROM meeting_hours m
              LEFT JOIN users u USING(user_id)
              WHERE u.user_type <> "" AND YEAR(m.time_in)
              BETWEEN :sd AND :ed
              GROUP BY year,u.user_type';
    $result = DB::select( DB::raw($query), array(
        'sd' => $start_date,
        'ed' => $end_date,
     )); */

   $result =  DB::table('meeting_hours AS a')
   ->leftJoin('seasons AS s', function ($join) {
     $join->on(DB::raw('year(a.time_in)'), 's.year');
   })
   ->leftJoin('users AS b', function ($join) {
     $join->on('a.user_id', 'b.user_id')->on('a.time_in','>=','s.start_date')->on('a.time_in','<=','s.end_date');
   })->where('b.user_type','<>','')
     ->whereBetween(DB::raw('year(a.time_in)'),[$start_date,$end_date])
     ->select(DB::raw('COUNT(DISTINCT(a.user_id)) as user_count, YEAR(a.time_in) as year, b.user_type'))
     ->groupBy('year','user_type')->get();

    foreach($result as $re) {
      $year = (integer) $re->year;
      $user_type = $re->user_type;
      $uc = (integer) $re->user_count;
      $data[$user_type.'s'][$year] = $uc;
    }
/*
    $query = 'SELECT COUNT(DISTINCT(m.user_id)) as user_count, YEAR(m.time_in) as year, u.gender
              FROM meeting_hours m
              LEFT JOIN users u USING(user_id)
              WHERE u.gender <> "" AND YEAR(m.time_in)
              BETWEEN :sd AND :ed
              GROUP BY year,u.gender';
    $result = DB::select( DB::raw($query), array(
        'sd' => $start_date,
        'ed' => $end_date,
     )); */

     $result =  DB::table('meeting_hours AS a')
     ->leftJoin('seasons AS s', function ($join) {
       $join->on(DB::raw('year(a.time_in)'), 's.year');
     })
     ->leftJoin('users AS b', function ($join) {
       $join->on('a.user_id', 'b.user_id')->on('a.time_in','>=','s.start_date')->on('a.time_in','<=','s.end_date');
     })->where('b.gender','<>','')
       ->whereBetween(DB::raw('year(a.time_in)'),[$start_date,$end_date])
       ->select(DB::raw('COUNT(DISTINCT(a.user_id)) as user_count, YEAR(a.time_in) as year, b.gender'))
       ->groupBy('year','gender')->get();

    foreach($result as $re) {
      $year = (integer) $re->year;
      $gender = $re->gender;
      $uc = (integer) $re->user_count;
      $data[$gender.'s'][$year] = $uc;
    }
    /*
    $query = 'SELECT CASE
               WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(u.grad_year,"-07-01")) <=12 THEN "Senior"
               WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(u.grad_year,"-07-01")) <=24 THEN "Junior"
               WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(u.grad_year,"-07-01")) <=36 THEN "Sophmore"
               WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(u.grad_year,"-07-01")) <=48 THEN "Freshman"
               WHEN u.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(u.grad_year,"-07-01")) >48 THEN "Pre-Freshman"
               ELSE ""
              END AS student_grade, COUNT(DISTINCT(a.user_id)) as user_count, YEAR(a.time_in) as year
              FROM meeting_hours a
              LEFT JOIN users u USING(user_id)
              WHERE u.user_type="student" AND YEAR(a.time_in)
              BETWEEN :sd AND :ed
              GROUP BY year,student_grade';
    $result = DB::select( DB::raw($query), array(
        'sd' => $start_date,
        'ed' => $end_date,
     )); */

   $result =  DB::table('meeting_hours AS a')
   ->leftJoin('seasons AS s', function ($join) {
     $join->on(DB::raw('year(a.time_in)'), 's.year');
   })
   ->leftJoin('users AS b', function ($join) {
     $join->on('a.user_id', 'b.user_id')->on('a.time_in','>=','s.start_date')->on('a.time_in','<=','s.end_date');
   })->where('b.user_type','student')
     ->whereBetween(DB::raw('year(a.time_in)'),[$start_date,$end_date])
     ->select(DB::raw('CASE
      WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=0  THEN "Graduated"
      WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=12 THEN "Senior"
      WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=24 THEN "Junior"
      WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=36 THEN "Sophmore"
      WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=48 THEN "Freshman"
      WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) >48 THEN "Pre-Freshman"
      ELSE ""
     END AS student_grade, COUNT(DISTINCT(a.user_id)) as user_count, YEAR(a.time_in) as year'))
     ->groupBy('year','student_grade')->get();

    foreach($result as $re) {
      $year = (integer) $re->year;
      $grade = $re->student_grade;
      $uc = (integer) $re->user_count;
      $data[$grade][$year] = $uc;
    }

    $allData = multiYearReportData($data, $series, $years);
    $response = $response->withJson($allData);
    return $response;
  })->setName('Active Users per Year Report');
  /**
  * Total Hours per Grade per Year
  **/
  $this->get('/hoursPerGradePerYear', function ($request, $response, $args) {

    $check = checkReportInputs($request, $response, $type = 'range');
    if($check !== true) {
      return $check;
    }
    $start_date = $request->getParam('start_date');
    $end_date = $request->getParam('end_date');

    $series = array('Senior','Junior','Sophmore','Freshman','Pre-Freshman','Mentor');
    $init = initializeMultiYearData($start_date, $end_date, $series);
    $years = $init['years'];
    $data = $init['data'];

/*
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
*/
     $result =  DB::table('meeting_hours AS a')
     ->leftJoin('users AS b', function ($join) {
       $join->on('a.user_id', 'b.user_id');
     })->whereBetween(DB::raw('year(a.time_in)'),[$start_date,$end_date])
       ->select(DB::raw('CASE
        WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=0  THEN "Graduated"
        WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=12 THEN "Senior"
        WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=24 THEN "Junior"
        WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=36 THEN "Sophmore"
        WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) <=48 THEN "Freshman"
        WHEN b.user_type="student" AND TIMESTAMPDIFF(MONTH,a.time_in,CONCAT(b.grad_year,"-07-01")) >48 THEN "Pre-Freshman"
        ELSE ""
       END AS student_grade,
       IFNULL(SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600),0) as sum, year(a.time_in) as year'))
       ->groupBy('year','student_grade')->get();

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
    $allData = multiYearReportData($data, $series, $years);
    $response = $response->withJson($allData);
    return $response;
  })->setName('Hours per Grade per Year Report');
  /**
  * Total & average Hours per School per Year
  **/
  $this->get('/hoursPerSchool', function ($request, $response, $args) {
    $check = checkReportInputs($request, $response, $type = 'range');
    if($check !== true) {
      return $check;
    }
    $start_date = $request->getParam('start_date');
    $end_date = $request->getParam('end_date');
    $schoolsCollection = FrcPortal\School::all();
    $schools = $schoolsCollection->mapWithKeys(function ($school) {
        return [$school['school_id'] => $school['school_name']];
    })->all();
    $series = array_values($schools); //,'Total'
    $init = initializeMultiYearData($start_date, $end_date, $series);
    $years = $init['years'];
    $data = $init['data'];

    $labels = array();
    $query = 'SELECT b.school_id, SUM(d.hours) as sum, AVG(d.hours) as avg, d.year FROM
    (SELECT a.user_id, IFNULL(SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600),0) as hours, year(a.time_in) as year FROM meeting_hours a WHERE year(a.time_in) BETWEEN :sd AND :ed GROUP BY user_id,year) d
    LEFT JOIN users b USING (user_id)
    WHERE school_id <> "" AND school_id IS NOT NULL
    GROUP BY year,school_id';

    $result = DB::select( DB::raw($query), array(
        'sd' => $start_date,
        'ed' => $end_date,
     ));

    foreach($result as $re) {
      $school = $schools[$re->school_id];
      $year = (integer) $re->year;
      $sum = (double) $re->sum;
      $data[$school][$year] = $sum;
    }
    $allData = multiYearReportData($data, $series, $years);
    $response = $response->withJson($allData);
    return $response;
  })->setName('Hours per School per Year Report');
  /**
  * Total & average Hours per Gender per Year
  **/
  $this->get('/hoursPerGenderPerYear', function ($request, $response, $args) {
    $check = checkReportInputs($request, $response, $type = 'range');
    if($check !== true) {
      return $check;
    }
    $start_date = $request->getParam('start_date');
    $end_date = $request->getParam('end_date');

    $series = array('Male - Sum','Male - Avg','Female - Sum','Female - Avg');
    $init = initializeMultiYearData($start_date, $end_date, $series);
    $years = $init['years'];
    $data = $init['data'];

    $query = 'SELECT b.gender, SUM(d.hours) as sum, AVG(d.hours) as avg, d.year FROM
    (SELECT a.user_id, IFNULL(SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600),0) as hours, year(a.time_in) as year FROM meeting_hours a WHERE year(a.time_in) BETWEEN :sd AND :ed GROUP BY user_id,year) d
    LEFT JOIN users b USING (user_id)
    WHERE gender <> ""
    GROUP BY year,gender';

    $result = DB::select( DB::raw($query), array(
        'sd' => $start_date,
        'ed' => $end_date,
     ));

    foreach($result as $re) {
      $gender =  $re->gender;
      $year = (integer) $re->year;
    	$sum = (double) $re->sum;
    	$avg = (double) $re->avg;

    	$data[$gender.' - Sum'][$year] = $sum;
    	$data[$gender.' - Avg'][$year] = $avg;
    }
    $allData = multiYearReportData($data, $series, $years);
    $response = $response->withJson($allData);
    return $response;
  });
  /**
  * Total & average Hours per Gender per Year
  **/
  $this->get('/hoursPerWeek', function ($request, $response, $args) {

    $check = checkReportInputs($request, $response, $type = 'single');
    if($check !== true) {
      return $check;
    }
    $year = $request->getParam('year');

    $series = array('Total Hours'); //,'Total'
    $data = array(array());
    $labels = array();
    $query = 'SELECT SUM(a.hours) as sum, AVG(a.hours) as avg, a.week
              FROM (SELECT IFNULL(SUM(time_to_sec(timediff(mh.time_out, mh.time_in)) / 3600),0) as hours, week(mh.time_in,1) as week from meeting_hours mh WHERE year(mh.time_in) = :year GROUP BY week) a
              GROUP BY week';

    $result = DB::select( DB::raw($query), array(
        'year' => $year
     ));

    foreach($result as $re) {
      $date = new DateTime();
    	$date->setISODate($year,$re->week);
      $labels[] = $date->format('m/d/Y');
      $data[0][] = (double) $re->sum;
    }
    $allData = array(
    	'labels' => $labels,
    	'series' => $series,
    	'data' => array_values($data),
    	//'csvData' => metricsCreateCsvData($data, $years, $series)
    );
    $response = $response->withJson($allData);
    return $response;
  })->setName('Hours per Week per Year Report');
  /**
  * Total & average Hours per Day of Week per Year
  **/
  $this->get('/hoursPerDayOfWeek', function ($request, $response, $args) {
    $check = checkReportInputs($request, $response, $type = 'range');
    if($check !== true) {
      return $check;
    }
    $start_date = $request->getParam('start_date');
    $end_date = $request->getParam('end_date');
    $weekdays = array(
      "1" => 'Sunday',
      "2" => 'Monday',
      "3" => 'Tuesday',
      "4" => 'Wednesday',
      "5" => 'Thursday',
      "6" => 'Friday',
      "7" => 'Saturday'
    );
    $series = array_values($weekdays); //,'Total'
    $init = initializeMultiYearData($start_date, $end_date, $series);
    $years = $init['years'];
    $data = $init['data'];

    $labels = array();
    $query = 'SELECT SUM(a.hours) as sum, AVG(a.hours) as avg, a.weekday, a.year
              FROM (SELECT IFNULL(SUM(time_to_sec(timediff(mh.time_out, mh.time_in)) / 3600),0) as hours, DAYOFWEEK(mh.time_in) as weekday, year(mh.time_in) as year from meeting_hours mh WHERE year(mh.time_in) BETWEEN :sd AND :ed GROUP BY weekday,year) a
              GROUP BY year,weekday';

    $result = DB::select( DB::raw($query), array(
      'sd' => $start_date,
      'ed' => $end_date,
    ));
    foreach($result as $re) {
      $weekday = $weekdays[$re->weekday];
      $year = (integer) $re->year;
      $sum = (double) $re->sum;
      $data[$weekday][$year] = $sum;
    }
    $allData = multiYearReportData($data, $series, $years);
    $response = $response->withJson($allData);
    return $response;
  })->setName('Hours per Day of Week per Year Report');
  /**
  * Hours per Event per Year
  **/
  $this->get('/hoursPerEventPerYear', function ($request, $response, $args) {
    $check = checkReportInputs($request, $response, $type = 'single');
    if($check !== true) {
      return $check;
    }
    $year = $request->getParam('year');

    $series = array('Total Hours'); //,'Total'
    $data = array(array());
    $labels = array();

    $result =  DB::table('event_requirements')
    ->leftJoin('events', function ($join) {
      $join->on('events.event_id', 'event_requirements.event_id');
    })->leftJoin('seasons', function ($join) {
      $join->on('seasons.year', '=', DB::raw('YEAR(events.event_start)'));
    })->where('seasons.season_id',$year)
      ->where('event_requirements.registration',true)
      ->where('event_requirements.attendance_confirmed',true)
      ->select(DB::raw('events.*, SUM(time_to_sec(IFNULL(timediff(events.event_end, events.event_start),0)) / 3600) as hours'))->groupBy('event_requirements.user_id')->get();

/*    $query = 'SELECT IFNULL(SUM(time_to_sec(timediff(mh.time_out, mh.time_in)) / 3600),0) as hours, year(e.event_start) as year, e.*
              FROM meeting_hours mh
              LEFT JOIN events e ON mh.time_in >=DATE_SUB(e.event_start, INTERVAL 1.5 HOUR)  AND mh.time_out < DATE_ADD(e.event_end, INTERVAL 1.5 HOUR)
              GROUP BY e.event_id HAVING year = :year
              ORDER BY e.event_start';

    $result = DB::select( DB::raw($query), array(
        'year' => $year
     )); */

     foreach($result as $re) {
     	$name = $re->name;
     	$sa = explode('-',$re->event_start);
     	$ea = explode('-',$re->event_end);
   		$event_start = new DateTime($re->event_start);
   		$event_end = new DateTime($re->event_end);

     	if($event_start->format('Y-m-d') == $event_end->format('Y-m-d')) {
     		$date = new DateTime($re->event_start);
     		$name .= ' ('.$date->format('M j').')';
     	} elseif($event_start->format('Y-m') == $event_end->format('Y-m')) {
     		$date = new DateTime($re->event_start);
     		$name .= ' ('.$date->format('M j');
     		$date = new DateTime($re->event_end);
     		$name .= '-'.$date->format('j').')';
     	} else {
     		$date = new DateTime($re->event_start);
     		$name .= ' ('.$date->format('M j');
     		$date = new DateTime($re->event_end);
     		$name .= '-'.$date->format('M j').')';
     	}
     	$labels[] = $name;
     	$data[0][] = (double) $re->hours;
     }

    $allData = array(
    	'labels' => $labels,
    	'series' => $series,
    	'data' => array_values($data),
    	//'csvData' => metricsCreateCsvData($data, $years, $series)
    );
    $response = $response->withJson($allData);
    return $response;
  })->setName('Hours per Event per Year Report');
  /**
  * Hours per Event Type per Year
  **/
  $this->get('/hoursPerEventTypePerYear', function ($request, $response, $args) {
    $check = checkReportInputs($request, $response, $type = 'single');
    if($check !== true) {
      return $check;
    }
    $year = $request->getParam('year');

    $series = array('Total Hours'); //,'Total'
    $data = array(array());
    $labels = array();

    $result = DB::table('event_requirements')
    ->leftJoin('events', function ($join) {
      $join->on('events.event_id', 'event_requirements.event_id');
    })->leftJoin('seasons', function ($join) {
      $join->on('seasons.year', '=', DB::raw('YEAR(events.event_start)'));
    })->where('seasons.year',$year)
      ->where('event_requirements.registration',true)
      ->where('event_requirements.attendance_confirmed',true)
      ->select(DB::raw('events.type, SUM(time_to_sec(IFNULL(timediff(events.event_end, events.event_start),0)) / 3600) as hours'))->groupBy('events.type')->get();

     foreach($result as $re) {
      $type = $re->type;
      $labels[] = $type;
      $data[0][] = (double) $re->hours;
     }

    $allData = array(
      'labels' => $labels,
      'series' => $series,
      'data' => array_values($data),
      //'csvData' => metricsCreateCsvData($data, $years, $series)
    );
    $response = $response->withJson($allData);
    return $response;
  })->setName('Hours per Event Type per Year Report');
  /**
  * Total & average Hours per User Type per Year
  **/
  $this->get('/hoursPerUserTypePerYear', function ($request, $response, $args) {
    $check = checkReportInputs($request, $response, $type = 'range');
    if($check !== true) {
      return $check;
    }
    $start_date = $request->getParam('start_date');
    $end_date = $request->getParam('end_date');

    $series = array('Mentor - Sum','Mentor - Avg','Student - Sum','Student - Avg');
    $init = initializeMultiYearData($start_date, $end_date, $series);
    $years = $init['years'];
    $data = $init['data'];

    $query = 'SELECT b.user_type, SUM(d.hours) as sum, AVG(d.hours) as avg, d.year
              FROM (SELECT a.user_id, IFNULL(SUM(time_to_sec(timediff(a.time_out, a.time_in)) / 3600),0) as hours, year(a.time_in) as year from meeting_hours a WHERE year(a.time_in) BETWEEN :sd AND :ed GROUP BY user_id,year) d
              LEFT JOIN users b USING (user_id)
              GROUP BY year,user_type';


    $result = DB::select( DB::raw($query), array(
        'sd' => $start_date,
        'ed' => $end_date,
     ));

    foreach($result as $re) {
      $user_type =  $re->user_type;
      $year = (integer) $re->year;
    	$sum = (double) $re->sum;
    	$avg = (double) $re->avg;

    	$data[$user_type.' - Sum'][$year] = $sum;
    	$data[$user_type.' - Avg'][$year] = $avg;
    }
    $allData = multiYearReportData($data, $series, $years);
    $response = $response->withJson($allData);
    return $response;
  })->setName('Hours per User Type per Year Report');
});

















?>

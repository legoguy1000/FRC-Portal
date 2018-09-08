<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class AnnualRequirement extends Eloquent {
  //table name
  protected $table = 'annual_requirements';
  //Use Custom Primary Key
  protected $primaryKey = 'req_id'; // or null
  public $incrementing = false;
  protected $keyType = 'string';
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'user_id', 'season_id', 'join_team', 'stims','dues'
  ];


  protected $appends = ['off_season_hours','build_season_hours','weekly_build_season_hours','competition_season_hours','event_hours','total_hours','min_hours','reqs_complete'];

  protected $attributes = [
    'join_team' => false,
    'stims' => false,
    'dues' => false,
//    'off_season_hours' => 0,
//    'build_season_hours' => 0,
//    'competition_season_hours' => 0,
  ];

  //$data['requirements'] = array();
  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'join_team' => 'boolean',
    'stims' => 'boolean',
    'dues' => 'boolean',
    'off_season_hours' => 'float',
    'build_season_hours' => 'float',
    //'weekly_build_season_hours' => 'float',
    'competition_season_hours' => 'float',
    'total_hours' => 'float',
//    'min_hours' => 'boolean',
  ];

  public function save($options = array()) {
    if(is_null($this->req_id)) {
      $this->req_id = uniqid();
    }
    return parent::save();
  }
/*  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->req_id = (string) uniqid();
      die('adsfadsf');
      return true;
    });
  } */

  /**
   * Get the Season.
   */
  public function seasons() {
      return $this->belongsTo('FrcPortal\Season', 'season_id', 'season_id');
  }
  /**
   * Get the User.
   */
  public function users() {
      return $this->belongsTo('FrcPortal\User', 'user_id', 'user_id');
  }

  public function getBuildSeasonHoursAttribute() {
    //SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS build_season_hours, seasons.*,exempt_hours.exempt_id
    //FROM meeting_hours
    //LEFT JOIN exempt_hours ON meeting_hours.time_in >= DATE_SUB(exempt_hours.time_start, INTERVAL 1 HOUR) AND meeting_hours.time_out < DATE_ADD(exempt_hours.time_end, INTERVAL 1 HOUR)
    //LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in)
    //WHERE meeting_hours.time_in>=seasons.start_date AND meeting_hours.time_in<=seasons.bag_day  AND exempt_hours.exempt_id IS NULL GROUP BY meeting_hours.user_id,seasons.year
    $hours = null;
    if(isset($this->attributes['user_id']) && isset($this->attributes['season_id'])) {
      $hours = DB::table('meeting_hours')
              //->leftJoin('exempt_hours', function ($join) {
              //    $join->on('meeting_hours.time_in', '>=', DB::raw('DATE_SUB(exempt_hours.time_start, INTERVAL 1 HOUR)'))->on('meeting_hours.time_out', '<=', DB::raw('DATE_ADD(exempt_hours.time_end, INTERVAL 1 HOUR)'));
            //  })
              ->leftJoin('seasons', function ($join) {
                  $join->on('seasons.year', '=', DB::raw('YEAR(meeting_hours.time_in)'))->on('meeting_hours.time_in', '>=', 'seasons.start_date')->on('meeting_hours.time_in', '<=', 'seasons.bag_day');
              })
                //->whereNull('exempt_hours.exempt_id')
                ->whereRaw('seasons.season_id = "'.$this->attributes['season_id'].'"')
                ->whereRaw('meeting_hours.user_id = "'.$this->attributes['user_id'].'"')
                ->select(DB::raw('SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) as build_season_hours'))->groupBy('meeting_hours.user_id')->first();
    }
    return !is_null($hours) ? (float) $hours->build_season_hours : 0;
  }
  public function getWeeklyBuildSeasonHoursAttribute() {
    //SELECT user_id,IFNULL(SUM(time_to_sec(timediff(mh.time_out, mh.time_in)) / 3600),0) as hours, week(mh.time_in,1) as week from meeting_hours mh
    //LEFT JOIN seasons
    //ON seasons.year=YEAR(meeting_hours.time_in) AND mh.time_in >= seasons.start_date AND mh.time_in <= seasons.bag_day
    //WHERE week(mh.time_in) = (WEEK(CURDATE())-30)
    //GROUP BY user_id,week
    $data = array();
    $hours = null;
    if(isset($this->attributes['user_id']) && isset($this->attributes['season_id'])) {
      $q = DB::table('meeting_hours')
        ->leftJoin('seasons', function ($join) {
        $join->where('seasons.season_id', '=', "5a16f3faaebb8")
          ->on('seasons.year', '=', DB::raw('YEAR(meeting_hours.time_in)'))
          ->on('meeting_hours.time_in', '>=', 'seasons.start_date')
          ->on('meeting_hours.time_in', '<=', 'seasons.bag_day');
        })
        ->where('meeting_hours.user_id','5a11bd670484e')
        ->select(DB::raw('user_id, YEAR(meeting_hours.time_in) as year, SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) as week_hours, week(meeting_hours.time_in,1) as week'))->groupBy('meeting_hours.user_id', 'week');
      $hours = DB::table(DB::raw("(" . $q->toSql() . ") as subtable"))
        ->mergeBindings($q)
        ->select(DB::raw('seasons.hour_requirement_week, seasons.start_date, seasons.bag_day, subtable.*, (subtable.week_hours >= seasons.hour_requirement_week) as req_complete'))
        ->leftJoin('seasons', function ($join) {
          $join->on('subtable.year', 'seasons.year');
        })->get();
    }
    if(!is_null($hours) && !empty($hours)) {
		$start = $hours[0]->start_date;
		$end = $hours[0]->bag_day;
		$end_week = date('W',strtotime($end));
		if(time() < strtotime($end)) {
			$end_week = date('W');
		}
		$start_week = date('W',strtotime($start));
		$num_weeks = floor($end_week - $start_week);
    $hours_arr = (array) json_decode(json_encode($hours),true);
		$num_req_com = count(array_filter(array_column($hours_arr,'req_complete')));
		$all_complete = $num_weeks == $num_req_com;
		$data['hours'] = $hours;
		$data['reqs_complete'] = $all_complete;
    }
    return $data;
  }
  public function getCompetitionSeasonHoursAttribute() {
    //SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS competition_season_hours, seasons.*
    //FROM meeting_hours
    //LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in)
    //WHERE meeting_hours.time_in>seasons.bag_day AND meeting_hours.time_in<=seasons.end_date
    //GROUP BY meeting_hours.user_id,seasons.year
    $hours = null;
    if(isset($this->attributes['user_id']) && isset($this->attributes['season_id'])) {
    $hours = DB::table('meeting_hours')
            ->leftJoin('seasons', function ($join) {
                $join->on('seasons.year', '=', DB::raw('YEAR(time_in)'))->on('meeting_hours.time_in', '>', 'seasons.bag_day')->on('meeting_hours.time_in', '<=', 'seasons.end_date');
            })->whereRaw('seasons.season_id = "'.$this->attributes['season_id'].'"')
              ->whereRaw('meeting_hours.user_id = "'.$this->attributes['user_id'].'"')
              ->select(DB::raw('SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS competition_season_hours'))->groupBy('meeting_hours.user_id')->first();
    }
    return !is_null($hours) ? (float) $hours->competition_season_hours : 0;
  }
  public function getOffSeasonHoursAttribute() {
    //SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS off_season_hours, seasons.*
    //FROM meeting_hours
    //LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in)
    //WHERE meeting_hours.time_in>seasons.end_date
    //GROUP BY meeting_hours.user_id,seasons.year
    $hours = null;
    if(isset($this->attributes['user_id']) && isset($this->attributes['season_id'])) {
    $hours = DB::table('meeting_hours')
            ->leftJoin('seasons', function ($join) {
                $join->on('seasons.year', '=', DB::raw('YEAR(time_in)'))->on('meeting_hours.time_in', '>', 'seasons.end_date');
            })->whereRaw('seasons.season_id = "'.$this->attributes['season_id'].'"')
              ->whereRaw('meeting_hours.user_id = "'.$this->attributes['user_id'].'"')
              ->select(DB::raw('SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) as off_season_hours'))->groupBy('meeting_hours.user_id')->first();
    }
    return !is_null($hours) ? (float) $hours->off_season_hours : 0;
  }
  public function getEventHoursAttribute() {
    //SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS off_season_hours, seasons.*
    //FROM meeting_hours
    //LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in)
    //WHERE meeting_hours.time_in>seasons.end_date
    //GROUP BY meeting_hours.user_id,seasons.year
    $hours = null;
    if(isset($this->attributes['user_id']) && isset($this->attributes['season_id'])) {
    $hours =  DB::table('event_requirements')
		->leftJoin('events', function ($join) {
			$join->on('events.event_id', 'event_requirements.event_id');
		})->leftJoin('seasons', function ($join) {
			$join->on('seasons.year', '=', DB::raw('YEAR(events.event_start)'));
		})->whereRaw('seasons.season_id = "'.$this->attributes['season_id'].'"')
		  ->whereRaw('event_requirements.user_id = "'.$this->attributes['user_id'].'"')
		  ->whereRaw('event_requirements.registration = "1"')
		  ->whereRaw('event_requirements.attendance_confirmed = "1"')
		  ->select(DB::raw('SUM(time_to_sec(IFNULL(timediff(events.event_end, events.event_start),0)) / 3600) as event_hours'))->groupBy('event_requirements.user_id')->first();
    }
    return !is_null($hours) ? (float) $hours->off_season_hours : 0;
  }
  public function getTotalHoursAttribute() {
    //SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS total_hours, seasons.*
    //FROM meeting_hours
    //LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in)
    //GROUP BY meeting_hours.user_id,seasons.year
    $hours = null;
    if(isset($this->attributes['user_id']) && isset($this->attributes['season_id'])) {
    $hours = DB::table('meeting_hours')
            ->leftJoin('seasons', function ($join) {
                $join->on('seasons.year', '=', DB::raw('YEAR(time_in)'));
            })->whereRaw('seasons.season_id = "'.$this->attributes['season_id'].'"')
              ->whereRaw('meeting_hours.user_id = "'.$this->attributes['user_id'].'"')
              ->select(DB::raw('SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS total_hours'))->groupBy('meeting_hours.user_id')->first();
    }
    return !is_null($hours) ? (float) $hours->total_hours : 0;
  }
  public function getMinHoursAttribute() {
    $hours = $this->build_season_hours;
    if(isset($hours) && isset($this->attributes['season_id'])) {
      $sid = $this->attributes['season_id'];
      $season = Season::find($sid);
      $hours_req = $season->hour_requirement;
      $hours_req_week = $season->weekly_build_season_hours;
      return ($hours_req> 0 && $hours >= $hours_req) && $hours_req_week['reqs_complete'];
    } else {
      return false;
    }
  }
  //$temp['reqs_complete'] = $jt && $stims && (($stu && $dues) || $men) && $mh;
  public function getReqsCompleteAttribute() {
    $jt = $this->join_team;
    $stims = $this->stims;
    $dues = $this->dues;
    $mh = $this->min_hours;
    if(isset($this->attributes['user_id'])) {
      $userInfo = User::find($this->attributes['user_id']);
      $stu = (bool) $userInfo->user_type == 'Student';
      $men = (bool) $userInfo->user_type == 'Mentor';
      return $jt && $stims && (($stu && $dues) || $men) && $mh;
    } else {
      return false;
    }
  }
}

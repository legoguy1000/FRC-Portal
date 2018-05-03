<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class AnnualRequirements extends Eloquent {
  //table name
  protected $table = 'annual_requirements';
  //Use Custom Primary Key
  protected $primaryKey = 'req_id'; // or null
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'req_id', 'user_id', 'season_id', 'join_team', 'stims','dues'
  ];


  protected $appends = ['off_season_hours','build_season_hours'];

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
//    'min_hours' => 'boolean',
  ];

  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->req_id = (string) uniqid();
    });
  }

  /**
   * Get the Season.
   */
  public function season() {
      return $this->belongsTo('FrcPortal\Season', 'season_id', 'season_id');
  }
  /**
   * Get the User.
   */
  public function user() {
      return $this->belongsTo('FrcPortal\User', 'user_id', 'user_id');
  }

  public function getBuildSeasonHoursAttribute() {
    //SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS build_season_hours, seasons.*,exempt_hours.exempt_id
    //FROM meeting_hours
    //LEFT JOIN exempt_hours ON meeting_hours.time_in >= DATE_SUB(exempt_hours.time_start, INTERVAL 1 HOUR) AND meeting_hours.time_out < DATE_ADD(exempt_hours.time_end, INTERVAL 1 HOUR)
    //LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in)
    //WHERE meeting_hours.time_in>=seasons.start_date AND meeting_hours.time_in<=seasons.bag_day  AND exempt_hours.exempt_id IS NULL GROUP BY meeting_hours.user_id,seasons.year
    return DB::table('meeting_hours')
            ->leftJoin('exempt_hours', function ($join) {
                $join->on('meeting_hours.time_in', '>=', DB::raw('DATE_SUB(exempt_hours.time_start, INTERVAL 1 HOUR)'))->on('meeting_hours.time_out', '<=', DB::raw('DATE_ADD(exempt_hours.time_end, INTERVAL 1 HOUR)'));
            })
            ->leftJoin('seasons', function ($join) {
                $join->on('seasons.year', '=', DB::raw('YEAR(time_in)'));
            })->where('meeting_hours.time_in', '>=', 'seasons.start_date')
              ->where('meeting_hours.time_in', '<=', 'seasons.bag_day')
              ->whereNull('exempt_hours.exempt_id')
              ->where('seasons.season_id', '=', $this->attributes['season_id'])
              ->where('meeting_hours.user_id', '=', $this->attributes['user_id'])
              ->select(DB::raw('SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) as build_season_hours'))->groupBy('meeting_hours.user_id')->get()[0];
  }
  public function getOffSeasonHoursAttribute() {
    //SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS off_season_hours, seasons.*
    //FROM meeting_hours
    //LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in)
    //WHERE meeting_hours.time_in>seasons.end_date
    //GROUP BY meeting_hours.user_id,seasons.year
    return DB::table('meeting_hours')
            ->leftJoin('seasons', function ($join) {
                $join->on('seasons.year', '=', DB::raw('YEAR(time_in)'));
            })->where('meeting_hours.time_in', '>', 'seasons.end_date')
              ->where('seasons.season_id', '=', $this->attributes['season_id'])
              ->where('meeting_hours.user_id', '=', $this->attributes['user_id'])
              ->select(DB::raw('SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) as off_season_hours'))->groupBy('meeting_hours.user_id')->get()[0];
  }
}

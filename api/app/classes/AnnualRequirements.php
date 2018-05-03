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


  protected $appends = ['off_season_hours'];

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

  public function getOffSeasonHoursAttribute() {
    //SELECT meeting_hours.user_id, year(meeting_hours.time_in), SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600) AS off_season_hours, seasons.*
    //FROM meeting_hours
    //LEFT JOIN seasons ON seasons.year=YEAR(meeting_hours.time_in)
    //WHERE meeting_hours.time_in>seasons.end_date
    //GROUP BY meeting_hours.user_id,seasons.year
    return DB::table('meeting_hours')
            ->join('seasons', function ($join) {
                $join->on('seasons.year', '=', 'YEAR(meeting_hours.time_in)');
            })->where('meeting_hours.time_in', '>', 'seasons.end_date')
              ->where('seasons.season_id', '=', $this->attributes['season_id'])
              ->where('meeting_hours.user_id', '=', $this->attributes['user_id'])
              ->select('SUM(time_to_sec(IFNULL(timediff(meeting_hours.time_out, meeting_hours.time_in),0)) / 3600)')->groupBy('account_id')->get();
  }
}

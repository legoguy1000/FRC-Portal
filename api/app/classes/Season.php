<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class Season extends Eloquent {
  //table name
  protected $table = 'seasons';
  //Use Custom Primary Key
  protected $primaryKey = 'season_id'; // or null
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'season_id', 'year', 'game_name', 'game_logo', 'start_date','bag_day','end_date','hour_requirement','join_spreadsheet'
  ];


  protected $appends = ['start_date_unix','bag_day_unix','end_date_unix','start_date_formatted','bag_day_formatted','end_date_formatted','start_date_formatted','start_date_formatted'];

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
    'year' => 'integer',
    'hour_requirement' => 'integer',
  ];

  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->season_id = (string) uniqid();
    });
  }
  public function getStartDateUnixAttribute() {
    $date = new DateTime($this->attributes['start_date']);
    return $date->format('U');
  }
  public function getEndDateUnixAttribute() {
    $date = new DateTime($this->attributes['end_date']);
    return $date->format('U');
  }
  public function getBagDayUnixAttribute() {
    $date = new DateTime($this->attributes['bag_day']);
    return $date->format('U');
  }
  public function getStartDateFormattedAttribute() {
    $date = new DateTime($this->attributes['start_date']);
    return $date->format('F j, Y');
  }
  public function getEndDateFormattedAttribute() {
    $date = new DateTime($this->attributes['end_date']);
    return $date->format('F j, Y');
  }
  public function getBagDayFormattedAttribute() {
    $date = new DateTime($this->attributes['bag_day']);
    return $date->format('F j, Y');
  }

  /**
  * Get the Annual requirements.
  */
  public function annual_requirements() {
    return $this->hasMany('FrcPortal\AnnualRequirement', 'season_id', 'season_id');
  }
  /**
  * Get the Annual requirements.
  */
  /*public function users() {
    return DB::table('seasons')->crossJoin('users')->where('seasons.season_id', '=', $this->attributes['season_id'])->select('user_id')->get();
  }*/

}

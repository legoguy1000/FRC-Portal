<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class Event extends Eloquent {
  //table name
  protected $table = 'events';
  //Use Custom Primary Key
  protected $primaryKey = 'event_id'; // or null
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'event_id', 'google_cal_id', 'name', 'type', 'event_start', 'event_end', 'registration_date', 'details', 'location', 'payment_required', 'permission_slip_required', 'food_required', 'room_required', 'drivers_required', 'poc'
  ];


  protected $appends = ['single_day','year','event_start_unix','event_end_unix','registration_date_unix','season','num_days'];

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
    'payment_required' => 'boolean',
    'food_required' => 'boolean',
    'room_required' => 'boolean',
    'drivers_required' => 'boolean',
    'permission_slip_required' => 'boolean',
    'single_day' => 'boolean',
  ];

  public function save($options = array()) {
    if(is_null($this->event_id)) {
      $this->event_id = uniqid();
    }
    return parent::save();
  } /*
  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->event_id = (string) uniqid();
    });
  } */

  public function getSingleDayAttribute() {
    $start = new DateTime($this->attributes['event_start']);
    $end = new DateTime($this->attributes['event_end']);
    return (bool) ($start->format('Y-m-d') == $end->format('Y-m-d'));
  }
  public function getYearAttribute() {
    return date('Y',strtotime($this->attributes['event_start']));
  }
  public function getEventStartUnixAttribute() {
    $date = new DateTime($this->attributes['event_start']);
    return $date->format('U');
  }
  public function getEventEndUnixAttribute() {
    $date = new DateTime($this->attributes['event_end']);
    return $date->format('U');
  }
  public function getRegistrationDateUnixAttribute() {
    $return = null;
    if(!is_null($this->attributes['registration_date'])) {
      $date = new DateTime($this->attributes['registration_date']);
      $return = $date->format('U');
    }
    return $return;
  }
  public function getNumDaysAttribute() {
    $start = strtotime($this->attributes['event_start']);
    $end = strtotime($this->attributes['event_end']);
    $diff = $end - $start;
    return ceil($diff / (60 * 60 * 24));
  }

  public function getSeasonAttribute() {
    return DB::table('seasons')
            ->join('events', function ($join) {
                $join->on('events.event_start', '>=', 'seasons.start_date')->on('events.event_end', '<=', 'seasons.end_date');
            })->where('events.event_id', '=', $this->attributes['event_id'])->select('seasons.*')->limit(1)->get()[0];
  }

  /**
  * Get the Event requirements.
  */
  public function event_requirements() {
    return $this->hasMany('FrcPortal\EventRequirement', 'event_id', 'event_id');
  }
  /**
  * Get the Event Cars.
  */
  public function event_cars() {
    return $this->hasMany('FrcPortal\EventCar', 'event_id', 'event_id');
  }
  /**
   * Get the Room.
   */
  public function event_rooms() {
      return $this->hasMany('FrcPortal\EventRoom', 'event_id', 'event_id');
  }
  /**
  * Get the POC.
  */
  public function poc() {
    return $this->hasOne('FrcPortal\User', 'user_id', 'poc_id');
  }
}

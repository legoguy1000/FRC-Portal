<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
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


  protected $appends = ['single_day','year','event_start_unix','event_end_unix','registration_date_unix'];

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

  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->season_id = (string) uniqid();
    });
  }
  public function getSingleDayAttribute() {
    $start = new DateTime($this->attributes['event_start']);
    $end = new DateTime($this->attributes['event_end']);
    return (bool) ($start->format('Y-m-d') == $end);
  }
  public function getYearAttribute() {
    return date('Y',$this->attributes['event_start']);
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
    $date = new DateTime($this->attributes['registration_date']);
    return $date->format('U');
  }

  public function season() {

    return DB::table('seasons')
            ->join('events', function ($join) {
                $join->on('events.event_start', '>=', 'seasons.start_date')->on('events.event_end', '<=', 'seasons.end_date');
            })->where('events.event_id', '=', $this->attributes['event_id'])->select('seasons.*')->get();
  }

}

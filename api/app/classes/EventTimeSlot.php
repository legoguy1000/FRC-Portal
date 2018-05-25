<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class EventTimeSlot extends Eloquent {
  //table name
  protected $table = 'event_time_slots';
  //Use Custom Primary Key
  protected $primaryKey = 'time_slot_id'; // or null
  public $incrementing = false;
  public $timestamps = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'time_slot_id', 'event_id', 'name','description','time_start','time_end'
  ];


  protected $appends = ['time_start_unix', 'time_end_unix'];

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
  protected $casts = [];

  public function save($options = array()) {
    if(is_null($this->time_slot_id)) {
      $this->time_slot_id = uniqid();
    }
    return parent::save();
  } /*
  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->room_id = (string) uniqid();
    });
  } **/

  /**
   * Get the Season.
   */
  public function events() {
      return $this->belongsTo('FrcPortal\Event', 'event_id', 'event_id');
  }
  /**
   * Get the room.
   */
  public function users() {
      return $this->belongsToMany('FrcPortal\User');
  }

  public function getTimeStartUnixAttribute() {
    $date = new DateTime($this->attributes['time_start']);
    return $date->format('U');
  }
  public function getTimeEndUnixAttribute() {
    $date = new DateTime($this->attributes['time_end']);
    return $date->format('U');
  }
}

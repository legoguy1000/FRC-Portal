<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class EventCar extends Eloquent {
  //table name
  protected $table = 'event_cars';
  //Use Custom Primary Key
  protected $primaryKey = 'car_id'; // or null
  public $incrementing = false;
  public $timestamps = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'car_id', 'user_id', 'event_id', 'car_space'
  ];


  protected $appends = ['car_title'];

  protected $attributes = [
    'user_id' => null,
    'event_id' => null,
    'car_space' => null,
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
    'car_space' => 'integer',
  ];

  public function save($options = array()) {
    if(is_null($this->car_id)) {
      $this->car_id = uniqid();
    }
    return parent::save();
  } /*
  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->car_id = (string) uniqid();
    });
  } */

  /**
   * Get the Season.
   */
  public function events() {
      return $this->belongsTo('FrcPortal\Event', 'event_id', 'event_id');
  }
  /**
   * Get the User.
   */
  public function driver() {
      return $this->belongsTo('FrcPortal\User', 'user_id', 'user_id');
  }
  /**
   * Get the Car.
   */
  public function event_requirements() {
      return $this->belongsTo('FrcPortal\EventRequirement', 'car_id', 'car_id');
  }
  /**
   * Get the users in room.
   */
  public function passengers() {
      return $this->hasManyThrough('FrcPortal\User','FrcPortal\EventRequirement', 'car_id', 'user_id', 'car_id', 'user_id')->where('event_requirements.user_id', '!=', $this->user_id);
  }
  public function getCarTitleAttribute() {
    return isset($this->user_id) && isset($this->car_space) ? $this->driver->full_name.' ('.$this->car_space.')' : null;
  }

}

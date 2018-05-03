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
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'car_id', 'user_id', 'event_id', 'car_space'
  ];


  protected $appends = [];

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

  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->car_id = (string) uniqid();
    });
  }

  /**
   * Get the Season.
   */
  public function event() {
      return $this->belongsTo('FrcPortal\Event', 'event_id', 'event_id');
  }
  /**
   * Get the User.
   */
  public function user() {
      return $this->belongsTo('FrcPortal\User', 'user_id', 'user_id');
  }
  /**
   * Get the Car.
   */
  public function user() {
      return $this->belongsTo('FrcPortal\EventRequirement', 'car_id', 'car_id');
  }
  public function getCarBoolAttribute() {
    return isset($this->attributes['car_id']) && !is_null($this->attributes['car_id']);
  }

}

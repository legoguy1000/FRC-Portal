<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class EventRequirement extends Eloquent {
  //table name
  protected $table = 'event_requirements';
  //Use Custom Primary Key
  protected $primaryKey = 'ereq_id'; // or null
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'user_id', 'event_id', 'registration', 'payment','permission_slip','food','room_id','can_drive','car_id','comments','attendance_confirmed'
  ];


  protected $appends = ['car_bool','room_bool'];

  protected $attributes = [
    'registration' => false,
    'payment' => false,
    'permission_slip' => false,
    'food' => false,
    'room_id' => null,
    'can_drive' => false,
    'car_id' => null,
    'comments' => '',
    'attendance_confirmed' => false
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
    'registration' => 'boolean',
    'payment' => 'boolean',
    'permission_slip' => 'boolean',
    'food' => 'boolean',
    'can_drive' => 'boolean',
    'car_bool' => 'boolean',
    'room_bool' => 'boolean',
  ];

  public function save($options = array()) {
    if(is_null($this->ereq_id)) {
      $this->ereq_id = uniqid();
    }
    return parent::save();
  } /*
  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->ereq_id = (string) uniqid();
    });
  } */

  public function getCarBoolAttribute() {
    return isset($this->attributes['car_id']) && !is_null($this->attributes['car_id']);
  }
  public function getRoomBoolAttribute() {
    return isset($this->attributes['room_id']) && !is_null($this->attributes['room_id']);
  }
/*  public function getReqsCompleteAttribute() {
    $registration = $this->registration;
    $payment = $this->payment;
    $permission_slip = $this->permission_slip;
    $food = $this->food;
    $car_bool = $this->car_bool;
    $room_bool = $this->room_bool;

    if(isset($this->attributes['user_id'])) {
      $userInfo = User::find($this->attributes['user_id']);
      $stu = (bool) $userInfo->user_type == 'Student';
      $men = (bool) $userInfo->user_type == 'Mentor';
      return $jt && $stims && (($stu && $dues) || $men) && $mh;
    } else {
      return false;
    }
  } */
  /**
   * Get the Event.
   */
  public function events() {
      return $this->belongsTo('FrcPortal\Event', 'event_id', 'event_id');
  }
  /**
   * Get the User.
   */
  public function users() {
      return $this->belongsTo('FrcPortal\User', 'user_id', 'user_id');
  }
  /**
   * Get the Event Car.
   */
  public function event_cars() {
      return $this->hasOne('FrcPortal\EventCar', 'car_id', 'car_id');
  }
  /**
  * Get the Event Room.
  */
  public function event_rooms() {
    return $this->hasOne('FrcPortal\EventRoom', 'room_id', 'room_id');
  }

}

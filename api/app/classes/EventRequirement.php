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
    'ereq_id', 'user_id', 'event_id', 'registration', 'payment','permission_slip','food','room_id','can_drive','car_id','comments'
  ];


  protected $appends = ['car_bool'];

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
  ];

  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->ereq_id = (string) uniqid();
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

  public function getCarBoolAttribute() {
    return isset($this->attributes['car_id']) && !is_null($this->attributes['car_id']);
  }

}

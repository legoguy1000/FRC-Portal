<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class EventRoom extends Eloquent {
  //table name
  protected $table = 'event_rooms';
  //Use Custom Primary Key
  protected $primaryKey = 'room_id'; // or null
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'room_id', 'event_id', 'user_type','gender'
  ];


  protected $appends = ['room_type'];

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

  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->room_id = (string) uniqid();
    });
  }

  /**
   * Get the Season.
   */
  public function event() {
      return $this->belongsTo('FrcPortal\Event', 'event_id', 'event_id');
  }
  /**
   * Get the room.
   */
  public function event_requirement() {
      return $this->hasMany('FrcPortal\EventRequirement', 'room_id', 'room_id');
  }
  public function getroomBoolAttribute() {
    return isset($this->attributes['room_id']) && !is_null($this->attributes['room_id']);
  }

  //$room['user_type'] == 'Student' ? str_replace('Male',"Boys",str_replace('Female',"Girls",$room['gender'])).' '.$c[$roomType] : $room['user_type'].' '.$c[$roomType];
  public function getRoomTitleAttribute() {
  	$roomType = $this->attributes['user_type'] == 'Student' ? $this->attributes['user_type'].'.'.$this->attributes['gender'] : $this->attributes['user_type'];
    return  array($roomType);
  }
}

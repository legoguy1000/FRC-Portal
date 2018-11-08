<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class MeetingHour extends Eloquent {
  //table name
  protected $table = 'meeting_hours';
  //Use Custom Primary Key
  protected $primaryKey = 'hours_id'; // or null
  public $incrementing = false;
  //public $timestamps = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'hours_id', 'user_id', 'time_in', 'time_out'
  ];


  protected $appends = ['time_in_unix','time_out_unix', 'date'];

  //$data['requirements'] = array();
  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  protected $attributes = [
    'time_out' => null,
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [];

  public function save($options = array()) {
    if(is_null($this->hours_id)) {
      $this->hours_id = uniqid();
    }
    return parent::save();
  } /*
  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->hours_id = (string) uniqid();
    });
  } */

  /**
   * Get the User.
   */
  public function users() {
      return $this->belongsTo('FrcPortal\User', 'user_id', 'user_id');
  }

  public function getTimeInUnixAttribute() {
    $date = new DateTime($this->attributes['time_in']);
    return $date->format('U');
  }
  public function getTimeOutUnixAttribute() {
    $date = new DateTime($this->attributes['time_out']);
    return $date->format('U');
  }
  public function getDateAttribute() {
    $ti = new DateTime($this->attributes['time_in']);
    $to = new DateTime($this->attributes['time_out']);
    return array(
      'date_raw' => $ti->format('Y-m-d'),
      'date_dow' => $ti->format('D'),
      'date_formatted' => $ti->format('F j, Y'),
      'time_in_formatted' => $ti->format('g:i A'),
      'time_out_formatted' => $to->format('g:i A'),
    );
  }
}

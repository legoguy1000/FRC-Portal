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
  /**
 * The "type" of the primary key ID.
 *
 * @var string
 */
  protected $keyType = 'string';
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


  protected $appends = ['date'];

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
  public function user() {
      return $this->belongsTo('FrcPortal\User', 'user_id', 'user_id');
  }
  public function getDateAttribute() {
    $time_in = formatDateArrays($this->attributes['time_in']);
    $time_out = formatDateArrays($this->attributes['time_out']);
    return array(
      'time_in' => $time_in,
      'time_out' => $time_out,
    );
  }
}

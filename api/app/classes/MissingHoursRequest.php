<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class MissingHoursRequest extends Eloquent {
  //table name
  protected $table = 'missing_hours_requests';
  //Use Custom Primary Key
  protected $primaryKey = 'request_id'; // or null
  public $incrementing = false;
  public $timestamps = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'request_id', 'user_id', 'time_in', 'time_out', 'comment', 'request_date', 'approved', 'approved_date', 'approved_by'
  ];


  protected $appends = ['time_in_unix','time_out_unix','hours','request_date_unix','approved_date_unix'];

  //$data['requirements'] = array();
  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  protected $attributes = [
    'time_in' => null,
    'time_out' => null,
    'comment' => '',
    'approved' => null,
    'approved_date' => null,
    'approved_by' => null
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [];

  public function save($options = array()) {
    if(is_null($this->request_id)) {
      $this->request_id = uniqid();
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
    /**
   * Get the Approver.
   */
  public function approver() {
      return $this->belongsTo('FrcPortal\User', 'approved_by', 'user_id');
  }
  public function getTimeInUnixAttribute() {
    $date = new DateTime($this->attributes['time_in']);
    return $date->format('U');
  }
  public function getTimeOutUnixAttribute() {
    $date = new DateTime($this->attributes['time_out']);
    return $date->format('U');
  }
  public function getHoursAttribute() {
    $in = new DateTime($this->attributes['time_in']);
    $out = new DateTime($this->attributes['time_out']);
    $in = $in->format('U');
    $out = $out->format('U');
    return ($out - $in) / 3600;
  }
  public function getRequestDateUnixAttribute() {
    $date = new DateTime($this->attributes['request_date']);
    return $date->format('U');
  }
  public function getApprovedDateUnixAttribute() {
    $date = new DateTime($this->attributes['approved_date']);
    return $date->format('U');
  }

}

<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class User extends Eloquent {
  //table name
  protected $table = 'users';
  //Use Custom Primary Key
  protected $primaryKey = 'user_id'; // or null
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'user_id', 'fname', 'lname', 'email', 'full_name', 'student_grade', 'grad_year'
  ];

  protected $appends = ['slack_enabled','room_type'];
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
    'admin' => 'boolean',
    'status' => 'boolean',
    'first_login' => 'boolean',
    'admin' => 'boolean',
    'admin' => 'boolean',
  ];

  public function newQuery() {
      return parent::newQuery()->select('users.*', DB::raw('CONCAT(users.fname," ",users.lname) AS full_name,
      CASE
       WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=0  THEN "Graduated"
       WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=12 THEN "Senior"
       WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=24 THEN "Junior"
       WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=36 THEN "Sophmore"
       WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) <=48 THEN "Freshman"
       WHEN users.user_type="student" AND TIMESTAMPDIFF(MONTH,curdate(),CONCAT(users.grad_year,"-07-01")) >48 THEN "Pre-Freshman"
       ELSE ""
      END AS student_grade'));
  }

  public function save($options = array()) {
    if(is_null($this->user_id)) {
      $this->user_id = uniqid();
    }
    return parent::save();
  }
/*  public static function boot() {
    parent::boot();``
    static::creating(function ($instance) {
      $instance->user_id = (string) uniqid();
    });
  } */

  public function getSlackEnabledAttribute() {
    return (bool) isset($this->attributes['slack_id']) && $this->attributes['slack_id'] != '';
  }
  public function getRoomTypeAttribute() {
    $return = null;
    if(isset($this->attributes['user_type']) && isset($this->attributes['gender'])) {
      $return = $this->attributes['user_type'] == 'Student' ? $this->attributes['user_type'].'.'.$this->attributes['gender'] : $this->attributes['user_type'];
    }
    return $return;
  }

  /**
  * Get the School.
  */
  public function school() {
    return $this->hasOne('FrcPortal\School', 'school_id', 'school_id');
  }
  /**
  * Get the Annual requirements.
  */
  public function annual_requirements() {
    return $this->hasOne('FrcPortal\AnnualRequirement', 'user_id', 'user_id')->withDefault();;
  }
  /**
  * Get the Event requirements.
  */
  public function event_requirements() {
    return $this->hasOne('FrcPortal\EventRequirement', 'user_id', 'user_id')->withDefault();;
  }
  /**
  * Get the Event Cars.
  */
  public function event_cars() {
    return $this->hasMany('FrcPortal\EventCar', 'user_id', 'user_id');
  }
  /**
   * Get the POC.
   */
  public function event_pocs() {
      return $this->belongsTo('FrcPortal\Event', 'user_id', 'poc_id');
  }
  /**
  * Get the Meeting Hours.
  */
  public function meeting_hours() {
    return $this->hasMany('FrcPortal\MeetingHour', 'user_id', 'user_id');
  }
  public function last_sign_in() {
    return $this->hasOne('FrcPortal\MeetingHour', 'user_id', 'user_id')->orderBy('time_in', 'DESC');
  }
  /**
  * Get the OAuth IDs
  */
  public function oauth() {
    return $this->hasMany('FrcPortal\Oauth', 'user_id', 'user_id');
  }
  /**
  * Get the Notification Preferences
  */
  public function notification_preferences() {
    return $this->hasMany('FrcPortal\NotificationPreference', 'user_id', 'user_id');
  }
  /**
  * Get the Notification Preferences
  */
  public function notifications() {
    return $this->hasMany('FrcPortal\Notification', 'user_id', 'user_id');
  }
}

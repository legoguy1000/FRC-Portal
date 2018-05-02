<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
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

  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->user_id = (string) uniqid();
    });
  }

  public function setLnameAttribute($value) {
    $this->attributes['lname'] = $value;
    $this->attributes['full_name'] = $this->attributes['fname'].' '.$value;
  }

  public function setGradYearAttribute($value) {
    $return = null;
    if(isset($this->attributes['user_type']) && $this->attributes['user_type'] == 'Student' && $value != null) {
      $grad_year = $value;
      $curren_date = new DateTime();
      $grad_date = new DateTime($grad_year.'-07-01');
      $interval = $grad_date->diff($curren_date);
      $num_months = $interval->m + 12*$interval->y;
      if($num_months <= 0) {
        $return = 'Graduated';
      } else if($num_months <= 12) {
        $return = 'Senior';
      } else if($num_months <= 24) {
        $return = 'Junior';
      } else if($num_months <= 36) {
        $return = 'Sophmore';
      } else if($num_months <= 48) {
        $return = 'Freshman';
      } else if($num_months > 48) {
        $return = 'Pre-Freshman';
      }
    }
    $this->attributes['student_grade'] = $return;
  }
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
    return $this->belongsTo('FrcPortal\School', 'school_id', 'school_id');
  }
}

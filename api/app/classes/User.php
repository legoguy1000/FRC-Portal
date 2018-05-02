<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

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
    'user_id', 'fname', 'lname', 'email'
  ];

  protected $appends = ['full_name','student_grade'];
  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->user_id = (string) uniqid();
    });
  }

  public function getFullNameAttribute() {
    return $this->attributes['fname'].' '.$this->attributes['lname'];
  }
  public function getStudentGradeAttribute() {
    $return = null;
    if($this->attributes['user_type'] == 'Student') {
      $grad_year = $this->attributes['grad_year'];
      $curren_date = new \DateTime();
      $grad_date = new \DateTime($grad_year.'-07-01');
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
    return $return;
  }
  public function getAdminAttribute() {
    return (bool) $this->attributes['admin'];
  }
  public function getStatusAttribute() {
    return (bool) $this->attributes['status'];
  }
  public function getFirstLoginAttribute() {
    return (bool) $this->attributes['first_login'];
  }

  /**
  * Get the School.
  */
  public function school() {
    return $this->belongsTo('FrcPortal\School', 'school_id', 'school_id');
  }
}

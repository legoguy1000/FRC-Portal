<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class School extends Eloquent {
  //table name
  protected $table = 'schools';
  //Use Custom Primary Key
  protected $primaryKey = 'school_id'; // or null
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'school_id', 'school_name', 'abv', 'logo_url'
  ];

  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  public function save($options = array()) {
    if(is_null($this->school_id)) {
      $this->school_id = uniqid();
    }
    return parent::save();
  } /*
  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->school_id = (string) uniqid();
    });
  }*/

  /**
   * Get the user.
   */
  public function users() {
      return $this->belongsTo('FrcPortal\User', 'school_id', 'school_id');
  }

}

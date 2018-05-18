<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Setting extends Eloquent {
  //table name
  protected $table = 'settings';
  //Use Custom Primary Key
  protected $primaryKey = 'setting_id'; // or null
  public $incrementing = false;
  public $timestamps = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'setting_id', 'setting', 'value'
  ];

  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  public function save($options = array()) {
    if(is_null($this->setting_id)) {
      $this->setting_id = uniqid();
    }
    return parent::save();
  } /*
  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->school_id = (string) uniqid();
    });
  }*/

}

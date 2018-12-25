<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Log extends Eloquent {
  //table name
  protected $table = 'logs';
  //Use Custom Primary Key
  protected $primaryKey = 'log_id'; // or null
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'log_id', 'level', 'user_id', 'message'
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [];

  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  public function save($options = array()) {
    if(is_null($this->log_id)) {
      $this->log_id = uniqid();
    }
    return parent::save();
  } /*
  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->note_id = (string) uniqid();
    });
  } */

  /**
   * Get the user.
   */
   public function users() {
     return $this->hasOne('FrcPortal\User', 'user_id', 'user_id');
   }

}

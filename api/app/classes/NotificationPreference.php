<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class NotificationPreference extends Eloquent {
  //table name
  protected $table = 'notification_preferences';
  //Use Custom Primary Key
  protected $primaryKey = 'pref_id'; // or null
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'pref_id', 'user_id', 'method', 'type'
  ];

  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];
  
  public function save($options = array()) {
    if(is_null($this->pref_id)) {
      $this->pref_id = uniqid();
    }
    parent::save();
  } /*
  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->pref_id = (string) uniqid();
    });
  } */

  /**
   * Get the user.
   */
   public function users() {
     return $this->belongsTo('FrcPortal\User', 'user_id', 'user_id');
   }

}

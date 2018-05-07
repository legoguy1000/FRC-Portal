<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Oauth extends Eloquent {
  //table name
  protected $table = 'oauth_ids';
  //Use Custom Primary Key
  protected $primaryKey = 'auth_id'; // or null
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'auth_id', 'user_id', 'oauth_provider', 'oauth_id', 'oauth_user'
  ];

  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];
  
  public function save($options = array()) {
    if(is_null($this->auth_id)) {
      $this->auth_id = uniqid();
    }
    parent::save();
  } /*
  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->auth_id = (string) uniqid();
    });
  }*/

  /**
   * Get the user.
   */
   public function users() {
     return $this->belongsTo('FrcPortal\User', 'user_id', 'user_id');
   }

}

<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use \DateTime;

class UserCredential extends Eloquent {
  //table name
  protected $table = 'user_credentials';
  //Use Custom Primary Key
  protected $primaryKey = 'cred_id'; // or null
  /**
 * The "type" of the primary key ID.
 *
 * @var string
 */
  protected $keyType = 'string';
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'cred_id', 'credential_id', 'public_key', 'user_handle', 'user_id', 'name'
  ];

  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  protected $appends = ['timestamp_unix'];

  public function save($options = array()) {
    if(is_null($this->cred_id)) {
      $this->cred_id = uniqid();
    }
    return parent::save();
  } /*
  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->school_id = (string) uniqid();
    });
  }*/
  public function getTimestampUnixAttribute() {
    $date = new DateTime($this->attributes['created_at']);
    return $date->format('U');
  }
}

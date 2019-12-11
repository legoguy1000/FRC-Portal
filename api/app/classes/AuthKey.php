<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class AuthKey extends Eloquent {
  //table name
  protected $table = 'auth_keys';
  //Use Custom Primary Key
  protected $primaryKey = 'key_id'; // or null
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
    'key_id', 'user_id', ''
  ];

  protected $appends = [];
  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  public function save($options = array()) {
    if(is_null($this->key_id)) {
      $this->key_id = uniqid();
    }
    return parent::save();
  }

  /**
   * Get the user.
   */
  public function users() {
      return $this->belongsTo('FrcPortal\User', 'user_id', 'user_id');
  }

}

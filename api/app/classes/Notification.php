<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Notification extends Eloquent {
  //table name
  protected $table = 'notifications';
  //Use Custom Primary Key
  protected $primaryKey = 'note_id'; // or null
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
    'note_id', 'user_id', 'message', 'acknowledge'
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'acknowledge' => 'boolean',
  ];

  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  public function save($options = array()) {
    if(is_null($this->note_id)) {
      $this->note_id = uniqid();
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
     return $this->belongsTo('FrcPortal\User', 'user_id', 'user_id');
   }

}

<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class EventType extends Eloquent {
  //table name
  protected $table = 'event_types';
  //Use Custom Primary Key
  protected $primaryKey = 'type_id'; // or null
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
    'type_id', 'type', 'description'
  ];

  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  public function save($options = array()) {
    if(is_null($this->type_id)) {
      $this->type_id = uniqid();
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

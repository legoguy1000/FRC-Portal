<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class EventFood extends Eloquent {
  //table name
  protected $table = 'event_food';
  //Use Custom Primary Key
  protected $primaryKey = 'food_id'; // or null
  /**
 * The "type" of the primary key ID.
 *
 * @var string
 */
  protected $keyType = 'string';
  public $incrementing = false;
  public $timestamps = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'food_id', 'event_id', 'group','description'
  ];


  protected $appends = [];

  protected $attributes = [];
  //$data['requirements'] = array();
  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [];

  public function save($options = array()) {
    if(is_null($this->food_id)) {
      $this->food_id = uniqid();
    }
    return parent::save();
  } /*
  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->room_id = (string) uniqid();
    });
  } **/

  /**
   * Get the Season.
   */
  public function events() {
      return $this->belongsTo('FrcPortal\Event', 'event_id', 'event_id');
  }
  /**
   * Get the room.
   */
  public function food_choices() {
      return $this->belongsToMany('FrcPortal\EventRequirement', 'event_food_event_requirements', 'food_id', 'ereq_id');
      //return $this->hasManyThrough('FrcPortal\User', 'FrcPortal\EventRequirement', 'ereq_id', 'user_id', 'ereq_id', 'user_id');
  }
}

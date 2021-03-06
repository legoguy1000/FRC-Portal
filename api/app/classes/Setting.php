<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Setting extends Eloquent {
  //table name
  protected $table = 'settings';
  //Use Custom Primary Key
  protected $primaryKey = 'setting_id'; // or null
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
    'setting_id', 'section', 'setting', 'value'
  ];

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
  protected $casts = [
    'public' => 'boolean',
  ];

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

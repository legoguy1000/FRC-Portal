<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class UserCategory extends Eloquent {
  //table name
  protected $table = 'user_categories';
  //Use Custom Primary Key
  protected $primaryKey = 'cat_id'; // or null
  public $incrementing = false;
  public $timestamps = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'cat_id', 'name', 'description', 'type', 'system'
  ];


  protected $appends = [];

  protected $attributes = [
    'system' => false
  ];

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
  protected $casts = [
    'system' => 'boolean'
  ];

  public function save($options = array()) {
    if(is_null($this->cat_id)) {
      $this->cat_id = uniqid();
    }
    return parent::save();
  } /*

  /**
   * Get the Event Time Slots.
   */
  public function users() {
    return $this->belongsToMany('FrcPortal\User', 'users_user_categories', 'cat_id', 'user_id');
  }
}

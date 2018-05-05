<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class NotificationPreferences extends Eloquent {
  //table name
  protected $table = 'notifications';
  //Use Custom Primary Key
  protected $primaryKey = 'note_id'; // or null
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
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [];

  public static function boot() {
    parent::boot();
    static::creating(function ($instance) {
      $instance->note_id = (string) uniqid();
    });
  }

  /**
   * Get the user.
   */
   public function users() {
     return $this->belongsTo('FrcPortal\User', 'user_id', 'user_id');
   }

}

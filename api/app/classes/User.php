<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class User extends Eloquent {
  //table name
  protected $table = 'users';
  //Use Custom Primary Key
  protected $primaryKey = 'user_id'; // or null
  public $incrementing = false;
  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
  protected $fillable = [
    'user_id', 'fname', 'lname', 'email'
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
      $instance->user_id = (string) uniqid();
    });
  }

}

<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Auth {

  /**
  * Public 'is_logged' field
  * @var bool
  */
  public static $isAuthenticated = FALSE;
  /**
  * @var null
  */
  protected static $currentuser = NULL;
  /**
  * @var null
  */
  protected static $user = NULL;

  public static function setCurrentUser($user_id) {
    self::$currentuser = $user_id;
    $user = User::with(['school'])->find($user_id);
    if(!is_null($user)) {
      self::$user = $user;
      return true;
    }
    return false;
  }

  public static function user() {
    return self::$user;
  }

  public static function isAdmin() {
  	$return = false;
  	$user = self::$user;
  	if($user->status && $user->admin) {
  		$return = true;
  	}
  	return $return;
  }

}

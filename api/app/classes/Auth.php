<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Auth {
  //use Traits\AdminStuff;
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
  /**
  * @var null
  */
  protected static $token = NULL;
  /**
  * @var null
  */
  protected static $ip_address = NULL;
  /**
  * @var null
  */
  protected static $route = NULL;

  public static function setCurrentUser($user_id) {
    self::$currentuser = $user_id;
    if($user_id == getIniProp('admin_user')) {
      $user = new User();
      $user->email = '';
      $user->fname = 'Local';
      $user->lname = 'Admin';
      $user->admin = true;

      self::$isAuthenticated = true;
      self::$user = $user;
      return true;
    } else {
      $user = User::with(['school'])->find($user_id);
      if(!is_null($user)) {
        self::$isAuthenticated = true;
        self::$user = $user;
        return true;
      }
    }
    return false;
  }

  public static function user() {
    return self::$user;
  }

  public static function isAdmin() {
    $user = self::$user;
    return checkAdmin($user);
  }

  public static function setCurrentToken($token) {
    self::$token = $token;
    return true;
  }

  public static function currentToken() {
    return self::$token;
  }

  public static function isAuthenticated() {
    return self::$isAuthenticated;
  }

  public static function setClientIP($ip) {
    self::$ip_address = $ip;
    return true;
  }
  public static function getClientIP() {
    return self::$ip_address;
  }
  public static function setRoute($r) {
    self::$route = $r;
    return true;
  }
  public static function getRoute() {
    return self::$route;
  }
}

<?php
namespace FrcPortal;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Auth {

  /**
  * Public 'is_logged' field
  * @var bool
  */
  public $isAuthenticated = FALSE;
  /**
  * @var null
  */
  protected $currentuser = NULL;
  /**
  * @var null
  */
  protected $user = NULL;

  /**
   * Initiates database connection
   *
   * @param \PDO $dbh
   * @param $config
   */
  public function __construct($user_id) {
      $this->currentuser = $user_id;
      $user = User::find($user_id);
      if(!is_null($user)) {
        $this->user = $user;
        return true;
      }
  }

  public static function setCurrentUser($user_id) {
    $this->currentuser = $user_id;
    $user = User::with(['school'])->find($user_id);
    if(!is_null($user)) {
      $this->user = $user;
      return true;
    }
    return false;
  }

  public static function user() {
    return $this->user;
  }



}

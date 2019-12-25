<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;

class UserTest extends TestCase {

  protected $app;

  public function setUp(): void {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('error_log', tempnam(sys_get_temp_dir(), 'slim'));
    $this->app = (new FrcPortal\App())->get();
    $user = new FrcPortal\User();
    $user->email = 'abcd@example.org';
    $user->fname = 'John';
    $user->lname = 'Doe';
    $user->getGenderByFirstName();
    $user->user_type = 'Adult';
    $user->phone = '1234567890';
    //$user->getGetSlackIdByEmail();
    $user->save();
    //$this->app->config('debug', true);
  }

  public function testVersionGet() {
      $this->assert(true);
  }
}

?>

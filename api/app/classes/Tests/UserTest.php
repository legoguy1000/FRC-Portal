<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Firebase\JWT\JWT;
use FrcPortal\Utilities\IniConfig;

class UserTest extends TestCase {

  protected $app;
  protected $jwt;
  protected $user;

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
    $user->admin = true;
    $user->status = true;
    //$user->getGetSlackIdByEmail();
    $user->save();
    $this->user = $user;
    $this->jwt = $user->generateUserJWT();
    //$this->app->config('debug', true);
  }

  public function testUserJWT() {
    $authToken = JWT::decode(
        $this->jwt,
        getSettingsProp('jwt_key') ? getSettingsProp('jwt_key') : IniConfig::iniDataProperty('db_pass'),
        array("HS256", "HS512", "HS384")
    );
    $this->assertSame($authToken->data->user_id , $this->user->user_id);
  }
}

?>

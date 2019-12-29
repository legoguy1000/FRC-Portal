<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Firebase\JWT\JWT;
use FrcPortal\Utilities\IniConfig;

class UserTest extends TestCase {

  protected $app;
  protected static $jwt;
  protected static $user;

  public static function setUpBeforeClass(): void {
    $user = new FrcPortal\User();
    $user->email = 'abcd@example.org';
    $user->fname = 'John';
    $user->lname = 'Doe';
    $user->getGenderByFirstName();
    $user->user_type = 'Mentor';
    $user->phone = '1234567890';
    $user->admin = true;
    $user->status = true;
    //$user->getGetSlackIdByEmail();
    $user->save();
    self::$user = $user;
    self::$jwt = $user->generateUserJWT();
    //$this->app->config('debug', true);
  }

  public static function tearDownAfterClass(): void {
      FrcPortal\User::destroy(self::$user->user_id);
  }

  public function setUp(): void {
    $this->app = (new FrcPortal\App())->get();
  }

  public function tearDown(): void {
    unset($this->app);
  }


  public function testUserJwt() {
    $authToken = JWT::decode(
        self::$jwt,
        getSettingsProp('jwt_key') ? getSettingsProp('jwt_key') : IniConfig::iniDataProperty('db_pass'),
        array("HS256", "HS512", "HS384")
    );
    $this->assertSame($authToken->data->user_id , self::$user->user_id);
  }

  public function testGetUsers() {
    $env = Environment::mock([
      'REQUEST_METHOD' => 'GET',
      'REQUEST_URI'    => '/users',
    ]);
    $request = Request::createFromEnvironment($env);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request = $request->withHeader('Authorization', 'Bearer '.self::$jwt);
    $this->app->getContainer()['request'] = $request;
    $response = $this->app->run(true);
    $this->assertSame($response->getStatusCode(), 200);
    $body = json_decode((string) $response->getBody());
    $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    $this->assertTrue($body->status);
    $this->assertObjectHasAttribute('data' , $body);
    $this->assertGreaterThanOrEqual(1 , count($body->data));
  }

  public function testGetUser() {
    $env = Environment::mock([
      'REQUEST_METHOD' => 'GET',
      'REQUEST_URI'    => '/users/'.self::$user->user_id,
    ]);
    $request = Request::createFromEnvironment($env);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request = $request->withHeader('Authorization', 'Bearer '.self::$jwt);
    $this->app->getContainer()['request'] = $request;
    $response = $this->app->run(true);
    $this->assertSame($response->getStatusCode(), 200);
    $body = json_decode((string) $response->getBody());
    $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    $this->assertTrue($body->status);
    $this->assertObjectHasAttribute('data' , $body);
    $this->assertSame($body->data->user_id , self::$user->user_id);
  }

  public function testSetUserPinGood() {
    $env = Environment::mock([
      'REQUEST_METHOD' => 'PUT',
      'REQUEST_URI'    => '/users/'.self::$user->user_id.'/pin',
    ]);
    $request = Request::createFromEnvironment($env);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request = $request->withHeader('Authorization', 'Bearer '.self::$jwt);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request->getBody()->write(json_encode(array(
      'pin' => '12345',
    )));
    $this->app->getContainer()['request'] = $request;
    $response = $this->app->run(true);
    $this->assertSame($response->getStatusCode(), 200);
    $body = json_decode((string) $response->getBody());
    $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    $this->assertTrue($body->status);
    $this->assertObjectHasAttribute('data' , $body);
    $this->assertSame($body->data->user_id , self::$user->user_id);
  }

  public function testSetUserPinEmpty() {
    $env = Environment::mock([
      'REQUEST_METHOD' => 'PUT',
      'REQUEST_URI'    => '/users/'.self::$user->user_id.'/pin',
    ]);
    $request = Request::createFromEnvironment($env);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request = $request->withHeader('Authorization', 'Bearer '.self::$jwt);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request->getBody()->write(json_encode(array(
      'pin' => '',
    )));
    $this->app->getContainer()['request'] = $request;
    $response = $this->app->run(true);
    $this->assertSame($response->getStatusCode(), 400);
    $body = json_decode((string) $response->getBody());
    $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    $this->assertFalse($body->status);
    $this->assertObjectNotHasAttribute('data' , $body);
  }

public function testSetUserPinTooShort() {
    $env = Environment::mock([
      'REQUEST_METHOD' => 'PUT',
      'REQUEST_URI'    => '/users/'.self::$user->user_id.'/pin',
    ]);
    $request = Request::createFromEnvironment($env);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request = $request->withHeader('Authorization', 'Bearer '.self::$jwt);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request->getBody()->write(json_encode(array(
      'pin' => '123',
    )));
    $this->app->getContainer()['request'] = $request;
    $response = $this->app->run(true);
    $this->assertSame($response->getStatusCode(), 400);
    $body = json_decode((string) $response->getBody());
    $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    $this->assertFalse($body->status);
    $this->assertObjectNotHasAttribute('data' , $body);
  }
  public function testSetUserPinTooLong() {
    $env = Environment::mock([
      'REQUEST_METHOD' => 'PUT',
      'REQUEST_URI'    => '/users/'.self::$user->user_id.'/pin',
    ]);
    $request = Request::createFromEnvironment($env);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request = $request->withHeader('Authorization', 'Bearer '.self::$jwt);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request->getBody()->write(json_encode(array(
      'pin' => '1234567890',
    )));
    $this->app->getContainer()['request'] = $request;
    $response = $this->app->run(true);
    $this->assertSame($response->getStatusCode(), 400);
    $body = json_decode((string) $response->getBody());
    $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    $this->assertFalse($body->status);
    $this->assertObjectNotHasAttribute('data' , $body);
  }
  public function testSetUserPinNotNumbers() {
    $env = Environment::mock([
      'REQUEST_METHOD' => 'PUT',
      'REQUEST_URI'    => '/users/'.self::$user->user_id.'/pin',
    ]);
    $request = Request::createFromEnvironment($env);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request = $request->withHeader('Authorization', 'Bearer '.self::$jwt);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request->getBody()->write(json_encode(array(
      'pin' => 'asdfasdf',
    )));
    $this->app->getContainer()['request'] = $request;
    $response = $this->app->run(true);
    $this->assertSame($response->getStatusCode(), 400);
    $body = json_decode((string) $response->getBody());
    $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    $this->assertFalse($body->status);
    $this->assertObjectNotHasAttribute('data' , $body);
  }
}

?>

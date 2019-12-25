<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Firebase\JWT\JWT;
use FrcPortal\Utilities\IniConfig;

class AuthTest extends TestCase {

  protected $app;
  protected $jwt;
  protected $user;

  public function setUp(): void {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('error_log', tempnam(sys_get_temp_dir(), 'slim'));
    $this->app = (new FrcPortal\App())->get();
  }

  public function testSuccessfulLocalAdminLogin() {
    $env = Environment::mock([
      'REQUEST_METHOD' => 'POST',
      'REQUEST_URI'    => '/auth/admin',
    ]);
    $request = Request::createFromEnvironment($env);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request->getBody()->write(json_encode(array(
      'user' => 'admin',
      'password' => getenv('ADMIN_PASS')
    )));
    $this->app->getContainer()['request'] = $request;
    $response = $this->app->run(true);
    $this->assertSame($response->getStatusCode(), 200);
    $body = json_decode((string) $response->getBody());
    $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    $this->assertTrue($body->status);
    $this->assertClassHasStaticAttribute('token' , $body);
    $this->assertClassHasStaticAttribute('userInfo' , $body);
  }

  public function testFailedLocalAdminLogin() {
    $env = Environment::mock([
      'REQUEST_METHOD' => 'POST',
      'REQUEST_URI'    => '/auth/admin',
    ]);
    $request = Request::createFromEnvironment($env);
    $request = $request->withHeader('Content-Type', 'application/json');
    $request->getBody()->write(json_encode(array(
      'user' => 'admin1',
      'password' => getenv('ADMIN_PASS')
    )));
    $this->app->getContainer()['request'] = $request;
    $response = $this->app->run(true);
    $this->assertSame($response->getStatusCode(), 200);
    $body = json_decode((string) $response->getBody());
    $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    $this->assertFalse($body->status);
    $this->assertClassNotHasStaticAttribute('token' , $body);
    $this->assertClassNotHasStaticAttribute('userInfo' , $body);
  }
}

?>

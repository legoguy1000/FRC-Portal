<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;

class VersionTest extends TestCase {

  protected $app;

  public function setUp(): void {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('error_log', tempnam(sys_get_temp_dir(), 'slim'));
    $this->app = (new FrcPortal\App())->get();
    //$this->app->config('debug', true);
  }

  public function testVersionGet() {
      $env = Environment::mock([
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI'    => '/version',
      ]);
      $this->app->getContainer()['request'] = Request::createFromEnvironment($env);
      $response = $this->app->run(true);
      $this->assertSame($response->getStatusCode(), 200);
      $body = json_decode((string) $response->getBody());
      $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
      $this->assertSame((string) $body->current_version , VERSION);
  }

}

?>

<?php
namespace FrcPortal;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use \Firebase\JWT\JWT;
use FrcPortal\Utilities\Auth;
use FrcPortal\Utilities\IniConfig;

class App {

  /**
   * Stores an instance of the Slim application.
   *
   * @var \Slim\App
   */
  private $app;

  public function __construct() {
    $app = AppFactory::create();
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();
    // Add routing middleware
    $app->addRoutingMiddleware();
    $app->setBasePath('/api');
    $app->add(function(Request $request, RequestHandler $handler) {
      $header = "";
      $message = "Using token from request header";
      $token = null;
      $data = null;
      $authToken = $request->getAttribute("token");
      if(is_null($authToken)) {
        /* Check for token in header. */
        $headers = $request->getHeader('Authorization');
        $header = isset($headers[0]) ? $headers[0] : "";
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $token = $matches[1];
          } else if(!is_null($request->getParam('auth_token'))) {
            $token = $request->getParam('auth_token');
          }
          if(!is_null($token)) {
            try {
              $decoded = JWT::decode(
                  $token,
                  getSettingsProp('jwt_key') ? getSettingsProp('jwt_key') : IniConfig::iniDataProperty('db_pass'),
                  array("HS256", "HS512", "HS384")
              );
              $authToken = (array) $decoded;
              $request = $request->withAttribute('token', $data);
            } catch (Exception $exception) {
                handleExceptionMessage($exception);
            }
          }
      }
      if(!is_null($authToken)) {
        $userId = $authToken['data']->user_id;
      	Auth::setCurrentUser($userId);
      	Auth::setCurrentToken($authToken);
        /* Everything ok, call next middleware. */
      }
      $ipAddress = $request->getAttribute('ip_address');
      Auth::setClientIP($ipAddress);
      $route = $request->getAttribute('route');
      Auth::setRoute($route);
    	$response = $handler->handle($request);
      return $response;
    });
    $app->add(new \RKA\Middleware\IpAddress($checkProxyHeaders = true, $trustedProxies = array()));
    $app->add(new \Tuupola\Middleware\JwtAuthentication([
        "secret" => getSettingsProp('jwt_key') ? getSettingsProp('jwt_key') : IniConfig::iniDataProperty('db_pass'),
        "rules" => [
            new \Tuupola\Middleware\JwtAuthentication\RequestPathRule([
              "path" => ['/api'],
              "ignore" => ['/api/version','/api/manifest.json','/api/auth','/api/webauthn/authenticate','/api/slack','/api/hours/signIn/list','/api/hours/signIn/authorize','/api/hours/signIn/deauthorize','/api/hours/signIn/token','/api/config'],
            ]),
            new Utilities\RequestPathMethodRule([
              "passthrough" => [
                "/api/events" => ["GET"],
                "/api/eventTypes" => ["GET"],
                "/api/events/([a-z0-9]{13})" => ["GET"],
                "/api/events/([a-z0-9]{13})/timeSlots" => ["GET"],
                "/api/reports/topHourUsers/([0-9]{4})" => ["GET"],
                "/api/hours/signIn" => ["POST"],
              ],
            ])
        ],
        "before" => function ($request, $arguments) {
          //$authToken = $request->getAttribute("token");
          //$userId = $authToken['data']->user_id;
          //Auth::setCurrentUser($userId);
          //Auth::setCurrentToken($authToken);
          //$test = Auth::user()->user_id;
          //error_log($test, 0);
          return $request;
        },
        "after" => function ($response, $arguments) {
          $token = Auth::currentToken();
          $exp = $token['exp'];
          $status = $response->getStatusCode();
          if($exp - time() <= 15*60 && $status == 200) {
            $body = json_decode($response->getBody(),true);
            $user = Auth::user();
            if(!is_null($user)) {
              $body['token'] = $user->generateUserJWT();
            }
            return $response->withJson($body, $status);
          }
          return $response;
        }
    ]));
    $app->addErrorMiddleware(false, true, true);
    $app->get('/version', function (Request $request, Response $response, array $args) {
      //$this->logger->addInfo('Called version endpoint');
      $route = Auth::getRoute();
      $version = getGitVersion();
      $responseArr = array_merge($version, array(
        'host' => $_SERVER["HTTP_HOST"],
        'user' => Auth::user(),
        'token' => Auth::currentToken(),
        'isAuthenticated' => Auth::isAuthenticated(),
        'ip' => Auth::getClientIP(),
        /*'route' => array(
          'name' => $route->getName(),
          'groups' => $route->getGroups(),
          'methods' => $route->getMethods(),
          'arguments' => $route->getArguments(),
          'identifier' => $route->getIdentifier(),
        ),*/
      ));
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Version');
    $app->get('/config', function ($request, $response, $args) {
      $configArr = array(
        'google_oauth_client_id',
        'facebook_oauth_client_id',
        'microsoft_oauth_client_id',
        'amazon_oauth_client_id',
        'github_oauth_client_id',
        'discord_oauth_client_id',
        'team_name',
        'team_number',
        'team_logo_url',
        'team_domain',
        'google_calendar_id',
        'slack_team_id',
        'slack_url',
        'local_login_enable',
        'google_login_enable',
        'facebook_login_enable',
        'microsoft_login_enable',
        'amazon_login_enable',
        'github_login_enable',
        'discord_login_enable',
        'slack_enable',
        'email_enable',
        'team_color_primary',
        'team_color_secondary',
        'notification_email',
        'env_url',
        'require_team_email',
        'google_form_url',
        'google_analytics_id',
        'enable_team_emails',
      );
    //  $settings = FrcPortal\Setting::where('public',true)->get();
      $settings = Setting::whereIn('setting', $configArr)->get();
      $constantArr = array();
      foreach($settings as $set) {
        $constantArr[$set->setting] = formatSettings($set->setting, $set->value);
      }
      $responseStr = 'angular.module("FrcPortal").constant("configItems", '.json_encode($constantArr).');';
      $response->getBody()->write($responseStr);
      $response = $response->withHeader('Content-type', 'application/javascript');
      return $response;
    })->setName('Config');
    $app->get('/manifest.json', function ($request, $response, $args) {
      //$this->logger->addInfo('Called manifest endpoint');
      $configArr = array(
        'team_name',
        'team_number',
        'team_logo_url',
        'team_domain',
        'env_url',
        'team_color_primary',
        'team_color_secondary',
      );
      $settings = Setting::whereIn('setting', $configArr)->get();
      $constantArr = array();
      foreach($settings as $set) {
        $constantArr[$set->setting] = formatSettings($set->setting, $set->value);
      }
      $responseArr = array(
        'name' => 'Team '.$constantArr['team_number'].' Portal',
        'short_name' => 'Team '.$constantArr['team_number'],
        'lang' => 'en-US',
        'start_url' => '/home',
        'theme_color' => $constantArr['team_color_secondary'],
        'background_color' => $constantArr['team_color_secondary'],
        'display' => 'standalone',
        'icons' => array(
          array(
            'src' => '/favicons/android-chrome-192x192.png?v=47Myd2nElq',
            'sizes' => '192x192',
            'type' => 'image/png',
          ),
          array(
            'src' => '/favicons/android-chrome-512x512.png?v=47Myd2nElq',
            'sizes' => '512x512',
            'type' => 'image/png',
          )
        )
      );
      $response = $response->withJson($responseArr);
      return $response;
    })->setName('Manifest');


    require(__DIR__.'/../routes/auth.php');
    require(__DIR__.'/../routes/seasons.php');
    require(__DIR__.'/../routes/hours.php');
    require(__DIR__.'/../routes/users.php');
    require(__DIR__.'/../routes/events.php');
    require(__DIR__.'/../routes/reports.php');
    require(__DIR__.'/../routes/schools.php');
    require(__DIR__.'/../routes/slack.php');
    require(__DIR__.'/../routes/settings.php');
    require(__DIR__.'/../routes/public.php');
    require(__DIR__.'/../routes/eventTypes.php');
    require(__DIR__.'/../routes/userCategories.php');
    require(__DIR__.'/../routes/logs.php');

    $this->app = $app;
  }


  /**
   * Get an instance of the application.
   *
   * @return \Slim\App
   */
  public function get() {
      return $this->app;
  }

}
?>

<?php
require_once('app/includes.php');
require_once('app/libraries/CustomAuthRule.php');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;
use \Firebase\JWT\JWT;

$config = array();
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$config['determineRouteBeforeAppMiddleware'] = true;
$config['db']['driver']   = 'mysql'; //your mysql server
$config['db']['host']   = getIniProp('db_host'); //your mysql server
$config['db']['user']   = getIniProp('db_user'); //your mysql server username
$config['db']['pass']   = getIniProp('db_pass'); //your mysql server password
$config['db']['dbname'] = getIniProp('db_name'); //the mysql database to use
$config['db']['charset'] = 'utf8';
$config['db']['collation'] = 'utf8_unicode_ci';
$config['db']['prefix'] = '';
 //asdf
$app = new \Slim\App(['settings' => $config]);
$app->add(function ($request, $response, $next) {
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
        try {
          $decoded = JWT::decode(
              $token,
              getSettingsProp('jwt_key') ? getSettingsProp('jwt_key') : getIniProp('db_pass'),
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
  	FrcPortal\Auth::setCurrentUser($userId);
  	FrcPortal\Auth::setCurrentToken($authToken);
    /* Everything ok, call next middleware. */
  }
  $ipAddress = $request->getAttribute('ip_address');
  FrcPortal\Auth::setClientIP($ipAddress);
  $route = $request->getAttribute('route');
  FrcPortal\Auth::setRoute($route);
	$response = $next($request, $response);
  return $response;
});
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders = true, $trustedProxies = array()));
$app->add(new Tuupola\Middleware\JwtAuthentication([
    "secret" => getSettingsProp('jwt_key') ? getSettingsProp('jwt_key') : getIniProp('db_pass'),
    "rules" => [
        new Tuupola\Middleware\JwtAuthentication\RequestPathRule([
          "path" => ['/'],
          "ignore" => ['/version','/manifest.json','/auth','/slack','/hours/signIn/list','/hours/signIn/authorize','/hours/signIn/deauthorize','/hours/signIn/token','/config'],
        ]),
        new Tuupola\Middleware\JwtAuthentication\RequestPathMethodRule([
          "passthrough" => [
            "/events" => ["GET"],
            "/eventTypes" => ["GET"],
            "/events/([a-z0-9]{13})" => ["GET"],
            "/events/([a-z0-9]{13})/timeSlots" => ["GET"],
            "/reports/topHourUsers/([0-9]{4})" => ["GET"],
            "/hours/signIn" => ["POST"],
          ],
        ])
    ],
    "before" => function ($request, $arguments) {
      //$authToken = $request->getAttribute("token");
      //$userId = $authToken['data']->user_id;
      //FrcPortal\Auth::setCurrentUser($userId);
      //FrcPortal\Auth::setCurrentToken($authToken);
      //$test = FrcPortal\Auth::user()->user_id;
      //error_log($test, 0);
      return $request;
    },
    "after" => function ($response, $arguments) {
      $token = FrcPortal\Auth::currentToken();
      $exp = $token['exp'];
      $status = $response->getStatusCode();
      if($exp - time() <= 15*60 && $status == 200) {
        $body = json_decode($response->getBody(),true);
        $user = FrcPortal\Auth::user();
        if(!is_null($user)) {
          $body['token'] = $user->generateUserJWT();
        }
        return $response->withJson($body, $status);
      }
      return $response;
    }
]));
$container = $app->getContainer();
$container['upload_directory'] = __DIR__ . '/app/secured';
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler(__DIR__ . '/app/secured/app-'.date('Y-m-d').'.log',Monolog\Logger::DEBUG);
    $logger->pushHandler($file_handler);
    return $logger;
};

$app->get('/version', function (Request $request, Response $response, array $args) {
  $this->logger->addInfo('Called version endpoint');
  $route = FrcPortal\Auth::getRoute();
  $responseArr = array(
    'version' => VERSION,
    'host' => $_SERVER["HTTP_HOST"],
    'user' => FrcPortal\Auth::user(),
    'token' => FrcPortal\Auth::currentToken(),
    'isAuthenticated' => FrcPortal\Auth::isAuthenticated(),
    'ip' => FrcPortal\Auth::getClientIP(),
    'route' => array(
      'name' => $route->getName(),
      'groups' => $route->getGroups(),
      'methods' => $route->getMethods(),
      'arguments' => $route->getArguments(),
      'identifier' => $route->getIdentifier(),
    ),
  );

  insertLogs($level = 'Information', $message = 'Called version endpoint');
  $response = $response->withJson($responseArr);
  return $response;
})->setName('Version');
$app->get('/config', function ($request, $response, $args) {
  $configArr = array(
    'google_oauth_client_id',
    'facebook_oauth_client_id',
    'microsoft_oauth_client_id',
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
    'slack_enable',
    'email_enable',
    'team_color_primary',
    'team_color_secondary',
    'notification_email',
    'env_url',
    'require_team_email',
    'google_form_url',
    'google_analytics_id',
  );
//  $settings = FrcPortal\Setting::where('public',true)->get();
  $settings = FrcPortal\Setting::whereIn('setting', $configArr)->get();
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
  $this->logger->addInfo('Called manifest endpoint');
  $configArr = array(
    'team_name',
    'team_number',
    'team_logo_url',
    'team_domain',
    'env_url',
    'team_color_primary',
    'team_color_secondary',
  );
  $settings = FrcPortal\Setting::whereIn('setting', $configArr)->get();
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


require_once('./app/routes/auth.php');
require_once('./app/routes/seasons.php');
require_once('./app/routes/hours.php');
require_once('./app/routes/users.php');
require_once('./app/routes/events.php');
require_once('./app/routes/reports.php');
require_once('./app/routes/schools.php');
require_once('./app/routes/slack.php');
require_once('./app/routes/settings.php');
require_once('./app/routes/public.php');
require_once('./app/routes/eventTypes.php');
require_once('./app/routes/userCategories.php');
require_once('./app/routes/logs.php');

$app->run();

?>

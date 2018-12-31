<?php
use \Firebase\JWT\JWT;

function getRealIpAddr() {
	$ip = '';
	if(substr(php_sapi_name(), 0, 3) == 'cli') {
		global $WEBSOCKET_IP;
		$ip=$WEBSOCKET_IP;
	}
	elseif(substr(PHP_SAPI, 0, 6) == 'apache') {
		if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
		{
		  $ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
		{
		  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
		  $ip=$_SERVER['REMOTE_ADDR'];
		}
	}
    return $ip;
}

/*
function insertLogs($userId, $type, $status, $msg) {
	$db = db_connect();
	$id = uniqid();
	$ip = getRealIpAddr();
	$user_id = 'NUll';
	if($userId != '' && $userId != 'NULL') {
		$user_id = db_quote($userId);
	}
	$query = 'INSERT INTO logs (id, user_id, type, status, msg, remote_ip) VALUES ('.db_quote($id).', '.$user_id.', '.db_quote($type).', '.db_quote($status).', '.db_quote($msg).', '.db_quote($ip).')';
	//$result = db_query($query);
	return $id;
} */

function defaultTableParams() {
	$params = array();
	$params['filter'] = '';
	$params['limit'] = 5;
	$params['order'] = '';
	$params['page'] = 1;
	return $params;
}

function checkSearchInputs($request, $defaults) {
	$masterDefaults = defaultTableParams();
	$filter = $request->getParam('filter') !== null ? $request->getParam('filter'):$defaults['filter'];
	$limit = $request->getParam('limit') !== null ? $request->getParam('limit'):$defaults['limit'];
	$order = $request->getParam('order') !== null ? $request->getParam('order'):$defaults['order'];
	$page = $request->getParam('page') !== null ? $request->getParam('page'):$defaults['page'];

	return array(
		'filter' => $filter,
		'limit' => $limit,
		'order' => $order,
		'page' => $page,
	);
}

function transposeData($data)
{
  $retData = array();
  foreach ($data as $row => $columns) {
    foreach ($columns as $row2 => $column2) {
        $retData[$row2][$row] = $column2;
    }
  }
  return $retData;
}

function metricsCreateCsvData($data, $timeInc, $series) {
	$csvData = transposeData(array_values($data));
	for ($i=0; $i < count($csvData); $i++) {
		array_unshift($csvData[$i],$timeInc[$i]);
	}
	$csvHeader = $series;
	array_unshift($csvHeader,'');
	return array(
		'data' => $csvData,
		'header' => $csvHeader
	);
}

function filterArrayData ($inputArray, $filter) {
	return !empty($filter) ?  array_filter($inputArray,
														function ($var) use ($filter) {
															return in_array($var,$filter, true);
													},
													ARRAY_FILTER_USE_KEY) : $inputArray;
}

function getServiceAccountFile() {
	$file = __DIR__.'/../secured/service_account_credentials.json';
	if(!file_exists($file)) {
		throw new Exception("Credentials file does not exist");
	}
	$valid = json_validate(file_get_contents($file));
	if(!$valid['status']) {
		throw new Exception("Credentials file is not valid");
	}
	return array(
		'contents' => $valid['data'],
		'path' => $file
	);
}

function handleExceptionMessage($e) {
	error_log($e);
	$data = json_decode($e->getMessage(), true);
	if(json_last_error() == JSON_ERROR_NONE) {
		return $data['error']['message'];
	} else {
		return $e->getMessage();
	}
	return 'Something went wrong';
}

function insertLogs($level, $message) {
	$authed = FrcPortal\Auth::isAuthenticated();
	$log = new FrcPortal\Log();
	if($authed) {
		$userId = FrcPortal\Auth::user()->user_id;
		$log->user_id = $userId;
	}
	$route = FrcPortal\Auth::getRoute();
	if(!is_null($route)) {
		$log->route = $route->getName();
	}
	$ip = FrcPortal\Auth::getClientIP();
	$log->level = ucfirst($level);
	$log->message = $message;
	$log->ip_address = $ip;
	$log->save();
}

function getMembershipFormName() {
	$mfn = getSettingsProp('membership_form_name');
	if(is_null($mfn) || $mfn == '') {
		$mfn = '###YEAR### Membership (Responses)';
	}
	return $mfn;
}

function buildGoogleDriveQuery($file_name) {
	$fileArr = explode(' ',$file_name);
	$q = '';
	$qArr = array();
	foreach($fileArr as $str) {
		$qArr[] = 'name contains "'.$str.'" ';
	}
	if(count($qArr) > 0) {
		$q .= implode(' and ',$qArr).' and ';
	}
	$q .= 'mimeType = "application/vnd.google-apps.spreadsheet"';
	return $q;
}

function json_validate($string) {
    // decode the JSON data
		$result = array(
			'status' => false,
			'msg' => '',
			'data' => null
		);
    $json = json_decode($string,true);
    // switch and check possible JSON errors
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = ''; // JSON is valid // No error has occurred
            break;
        case JSON_ERROR_DEPTH:
            $error = 'The maximum stack depth has been exceeded.';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = 'Invalid or malformed JSON.';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = 'Control character error, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_SYNTAX:
            $error = 'Syntax error, malformed JSON.';
            break;
        // PHP >= 5.3.3
        case JSON_ERROR_UTF8:
            $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_RECURSION:
            $error = 'One or more recursive references in the value to be encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_INF_OR_NAN:
            $error = 'One or more NAN or INF values in the value to be encoded.';
            break;
        case JSON_ERROR_UNSUPPORTED_TYPE:
            $error = 'A value of a type that cannot be encoded was given.';
            break;
        default:
            $error = 'Unknown JSON error occured.';
            break;
    }
		$result = array(
			'status' => $error != '' ? false : true,
			'msg' => $error,
			'data' => $json
		);

    // everything is OK
    return $result;
}

function formatSettings($setting, $value) {
	if(strpos($setting, 'enable') !== false || strpos($setting, 'require') !== false) {
		$value = (boolean) $value;
	}
	return $value;
}

function exportDB() {
	//ENTER THE RELEVANT INFO BELOW
	$folder = __DIR__.'/../secured/db_exports/';
	if (!file_exists($folder)) {
	    mkdir($folder,0777,true);
	}
	$mysqlDatabaseName = getIniProp('db_name');
	$mysqlUserName = getIniProp('db_user');
	$mysqlPassword = getIniProp('db_pass');
	$mysqlHostName = getIniProp('db_host');
	$mysqlExportPath = $folder.date('Y-m-d H:i:s').' '.$mysqlDatabaseName.'.sql';
	$worked = null;
	if (!file_exists($mysqlExportPath)) {
		$command='mysqldump --opt -h '.$mysqlHostName.' -u '.$mysqlUserName.' -p'.$mysqlPassword.' '.$mysqlDatabaseName.' > "' .$mysqlExportPath.'"';
		exec($command,$output,$worked);
		switch($worked){
		case 0:
			return true;
		case 1:
			return true;
		case 2:
			return false;
		}
	}
}

function updateComposer() {
  exec("composer install");
  exec("composer dump-autoload");
	sleep(2);
}

function formatDateArrays($date_raw) {
	$date = new DateTime($date_raw);
	return array(
		'year' => $date->format('Y'),
		'unix' => $date->format('U'),
		'date_raw' => $date->format('Y-m-d'),
		'date_time_raw' => $date->format('Y-m-d H:i:s'),
		'date_ym' => $date->format('Y-m'),
		'long_date' => $date->format('F j, Y'),
		'time_formatted' => $date->format('g:i A'),
		'date_dow' => $date->format('D'),
		'multi_day_start' => $date->format('F j'),
		'multi_day_end' => $date->format('j, Y'),
		'full_formatted' => $date->format('F j, Y g:i A'),
	);
}

function standardResponse($status = false, $msg = '', $data = null) {
	$responseArr = array(
		'status' => $status,
		'msg' => $msg, // 'Something went wrong',
		'data' => $data
  );
	return $responseArr;
}

function unauthorizedResponse($response, $msg = 'Unauthorized Action') {
	$responseArr = standardResponse($status = false, $msg = $msg, $data = null);
	return $response->withJson($responseArr,403);
}

function badRequestResponse($response, $msg = 'Invalid Request') {
	$responseArr = standardResponse($status = false, $msg = $msg, $data = null);
	return $response->withJson($responseArr,400);
}

function exceptionResponse($response, $msg = 'Error', $code = 200) {
	$responseArr = standardResponse($status = false, $msg = $msg, $data = null);
	return $response->withJson($responseArr,$code);
}

function slackPostAPI($endpoint, $data) {
	$content = str_replace('#new_line#','\n',json_encode($data));
	$slack_token = getSettingsProp('slack_api_token');
	$client = new GuzzleHttp\Client(['base_uri' => 'https://slack.com/api/']);
	$response = $client->request('POST', $endpoint, array(
		'body' => $content,
		'headers' => array(
			'Authorization' => 'Bearer '.$slack_token,
			'Content-Type' => 'application/json',
		)
	));
	$code = $response->getStatusCode(); // 200
	$reason = $response->getReasonPhrase(); // OK
}

function slackGetAPI($endpoint, $params = array()) {
	$slack_token = getSettingsProp('slack_api_token');
	$params['token'] = $slack_token;
	$url = 'https://slack.com/api/'.$endpoint.'?'.http_build_query($params);
	$client = new GuzzleHttp\Client(['base_uri' => 'https://slack.com/api/']);
	$response = $client->request('GET', $endpoint, array(
		'query' => $params
	));
	$code = $response->getStatusCode(); // 200
	$reason = $response->getReasonPhrase(); // OK
	$body = $response->getBody();
	return $body;
}

function write_ini_file($assoc_arr, $path, $has_sections=FALSE) {
    $content = "";
    if ($has_sections) {
        foreach ($assoc_arr as $key=>$elem) {
            $content .= "[".$key."]\n";
            foreach ($elem as $key2=>$elem2) {
                if(is_array($elem2)) {
                    for($i=0;$i<count($elem2);$i++) {
                        $content .= $key2."[] = \"".$elem2[$i]."\"\n";
                    }
                }
                else if($elem2=="") $content .= $key2." = \n";
                else $content .= $key2." = \"".$elem2."\"\n";
            }
        }
    } else {
        foreach ($assoc_arr as $key=>$elem) {
            if(is_array($elem)) {
                for($i=0;$i<count($elem);$i++) {
                    $content .= $key."[] = \"".$elem[$i]."\"\n";
                }
            }
            else if($elem=="") $content .= $key." = \n";
            else $content .= $key." = \"".$elem."\"\n";
        }
    }
    if (!$handle = fopen($path, 'w')) {
        return false;
    }
    $success = fwrite($handle, $content);
    fclose($handle);
    return $success;
}

function clinput($question, $required = true) {
	if(substr(trim($question), -1) != ':') {
		$question .= ': ';
	}
	echo $question;
	$handle = fopen ("php://stdin","r");
	$line = fgets($handle);
	if(trim($line) == '' && $required){
	    echo "No input. Aborting!\n";
	    exit;
	}
	return trim($line);
}

function formatGoogleLoginUserData($me) {
	$email = $me['email'];
	$fname = $me['given_name'];
	$lname = $me['family_name'];
	$image = $me['picture'];
	$id = $me['sub'];

	$userData = array(
		'id' => $id,
		'provider' => 'Google',
		'email' => $email,
		'fname' => $fname,
		'lname' => $lname,
		'profile_image' => $image,
	);
	return $userData;
}

function formatFacebookLoginUserData($me) {
	$email = $me['email'];
	$fname = $me['first_name'];
	$lname = $me['last_name'];
	$image = $me['picture']['data']['url'];
	$id = $me['id'];

	$userData = array(
		'id' => $id,
		'provider' => 'Facebook',
		'email' => $email,
		'fname' => $fname,
		'lname' => $lname,
		'profile_image' => $image,
	);
	return $userData;
}

function formatMicrosoftLoginUserData($me) {
	$email = $me['userPrincipalName'];
	$fname = $me['givenName'];
	$lname = $me['surname'];
	$image = ''; //$me['image']['url'];\
	$id = $me['id'];

	$userData = array(
		'id' => $id,
		'provider' => 'Microsoft',
		'email' => $email,
		'fname' => $fname,
		'lname' => $lname,
		'profile_image' => $image,
	);
	return $userData;
}

?>

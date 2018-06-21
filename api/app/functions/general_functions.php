<?php
use \Firebase\JWT\JWT;


function getTokenFromHeaders() {
	$return = false;
	$headers = apache_request_headers();
	if(isset($headers['Authorization'])) {
		$jwt = str_replace('Bearer ','',$headers['Authorization']);
		if($jwt != '') {
			$return = $jwt;
		}
	}
	return $return;
}

function checkToken($die=true,$die401=false) {
	$data = false;
	$jwt = null;
	$jwt = getTokenFromHeaders();
	$data = checkTokenManually($jwt,$die,$die401);
	return $data;
}

function checkTokenManually($token,$die=true,$die401=false) {
	$data = array();
	if(isset($token) && $token != '' && $token != false && $token != null) {
		$jwt = $token;
		$key = getSettingsProp('jwt_key');
		try{
			$decoded = JWT::decode($jwt, $key, array('HS256'));
		}catch(\Firebase\JWT\ExpiredException $e){
			if($die401) {
				header("HTTP/1.1 401 Unauthorized");
				exit;
			} elseif($die) {
				die(json_encode(array('status'=>false, 'type'=>array('toast'=>'error', 'alert'=>'danger'), 'msg'=>'Authorization Error. '.$e->getMessage())));
			} else {
				return false;
			}
		} catch(\Firebase\JWT\SignatureInvalidException $e){
			if($die401) {
				header("HTTP/1.1 401 Unauthorized");
				exit;
			} elseif($die) {
				die(json_encode(array('status'=>false, 'type'=>array('toast'=>'error', 'alert'=>'danger'), 'msg'=>'Authorization Error. '.$e->getMessage())));
			} else {
				return false;
			}
		}
		$decoded_array = json_encode($decoded);
		$data = json_decode($decoded_array,true);
		return $data;
	} else {
		if($die401) {
			header("HTTP/1.1 401 Unauthorized");
			exit;
		} elseif($die) {
			die(json_encode(array('status'=>false, 'type'=>array('toast'=>'error', 'alert'=>'danger'), 'msg'=>'Authorization Error.  Please try logging in again.')));
		} else {
			return false;
		}
	}
}

function verifyToken($token,$die=true,$die401=false)
{
	global $app;
	$data = array();
	if(isset($token) && $token != '' && $token != false && $token != null)
	{
		$jwt = $token;
		$key = getSettingsProp('jwt_key');
		$decoded = JWT::decode($jwt, $key, array('HS256'));
		$decoded_array = json_encode($decoded);
		$data = json_decode($decoded_array,true);
		return $data;
	}
	else
	{
		if($die401)
		{
			header("HTTP/1.1 401 Unauthorized");
			exit;
		}
		elseif($die)
		{
			die(json_encode(array('status'=>false, 'type'=>array('toast'=>'error', 'alert'=>'danger'), 'msg'=>'Authorization Error.  Please try logging in again.')));
		}
		else
		{
			return false;
		}
	}
}

function getRealIpAddr()
{
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

function insertLogs($userId, $type, $status, $msg)
{
	$db = db_connect();
	$id = uniqid();
	$ip = getRealIpAddr();
	$user_id = 'NUll';
	if($userId != '' && $userId != 'NULL') {
		$user_id = db_quote($userId);
	}
	$query = 'INSERT INTO logs (id, user_id, type, status, msg, remote_ip) VALUES ('.db_quote($id).', '.$user_id.', '.db_quote($type).', '.db_quote($status).', '.db_quote($msg).', '.db_quote($ip).')';
	$result = db_query($query);
	return $id;
}

function defaultTableParams() {
	$params = array();
	$params['filter'] = '';
	$params['limit'] = 5;
	$params['order'] = '';
	$params['page'] = 5;
	return $params;
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
	$result = array(
		'status' => false,
		'msg' => 'File Doesn\'t Exist',
		'data' => array(
			'path' => $file,
			'contents' => null
		)
	);
	if(file_exists($file)) {
		$result['status'] = true;
		$result['msg'] = 'File Present';
		$result['data']['contents'] = json_decode(file_get_contents($file),true);
	}
	return $result;
}

function getMembershipFormName() {
	$mfn = getSettingsProp('membership_form_name');
	if(is_null($mfn)) {
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
	$mysqlDatabaseName = getIniProp('db_name');
	$mysqlUserName = getIniProp('db_user');
	$mysqlPassword = getIniProp('db_pass');
	$mysqlHostName = getIniProp('db_host');
	$mysqlExportPath = __DIR__.'/../secured/db_exports/'.date('Y-m-d H:i:s').' '.$mysqlDatabaseName.'.sql';
	$worked = null;
	$command='mysqldump --opt -h' .$mysqlHostName .' -u' .$mysqlUserName .' -p' .$mysqlPassword .' ' .$mysqlDatabaseName .' > ' .$mysqlExportPath;
	exec($command,$output=array(),$worked);
	switch($worked){
	case 0:
		return true;
	case 1:
		return true;
	case 2:
		return false;
	}
}
?>

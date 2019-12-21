<?php
use \Firebase\JWT\JWT;
use Goutte\Client;
use Symfony\Component\DomCrawler\Field\InputFormField;
use GuzzleHttp\Client as GuzzleClient;
use FrcPortal\Utilities\IniConfig;

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

function getServiceAccountData() {
	$gsa_data = FrcPortal\Setting::where('section', 'service_account')->where('setting', 'google_service_account_data')->first();
	if(!is_null($gsa_data) && $gsa_data->value != '') {
		$gsa_arr = explode(',',$gsa_data->value);
		$encypted_json = $gsa_arr[1];
		$json = decryptItems($encypted_json);
		return json_decode($json, true);
	} else {
		throw new Exception("Google Service Account credentials do not exist");
		//return false;
	}
}

function encryptItems($decrypted) {
	$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
	$key = hex2bin(IniConfig::iniDataProperty('encryption_key'));
	$ciphertext = sodium_crypto_secretbox($decrypted, $nonce, $key);
	$encrypted = base64_encode($nonce.$ciphertext);
	return $encrypted;
}

function decryptItems($encrypted) {
	$decoded = base64_decode($encrypted);
	$nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
	$ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
	$key = hex2bin(IniConfig::iniDataProperty('encryption_key'));
	$decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
	return $decrypted;
}

function handleGoogleAPIException($e, $google_service) {
	error_log($e);
	$data = json_decode($e->getMessage(), true);
	if(json_last_error() == JSON_ERROR_NONE) {
		$errorRoot = $data['error']['errors'][0];
		$msg = $google_service.' Error: '.$errorRoot['message'];
		if(substr($msg,-1) != '.') {
			$msg = $msg.'.';
		}
		$msg = $msg.' Please check API key and/or Service Account Credentials.';
		if(isset($errorRoot['extendedHelp'])) {
			$msg = $msg.' See '.$errorRoot['extendedHelp'].' for more information.';
		}
		return $msg;
	} else {
		return $google_service.' Error: '.$e->getMessage();
	}
	return 'Something went wrong';
}

function handleExceptionMessage($e) {
	error_log($e);
	$data = json_decode($e->getMessage(), true);
	if(json_last_error() == JSON_ERROR_NONE) {
		//insertLogs('warning', $data['error']['message']);
		return $data['error']['message'];
	} else {
		//insertLogs('warning', $e->getMessage());
		return $e->getMessage();
	}
	return 'Something went wrong';
}

function insertLogs($level, $message) {
	$authed = FrcPortal\Utilities\Auth::isAuthenticated();
	$log = new FrcPortal\Log();
	if($authed) {
		$userId = FrcPortal\Utilities\Auth::user()->user_id;
		if($userId == IniConfig::iniDataProperty('admin_user')) {
			$message = '(Local Admin) '.$message;
		} else {
			$log->user_id = $userId;
		}
	}
	$route = FrcPortal\Utilities\Auth::getRoute();
	if(!is_null($route)) {
		$log->route = $route->getName();
	}
	$ip = FrcPortal\Utilities\Auth::getClientIP();
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
	$mysqlDatabaseName = IniConfig::iniDataProperty('db_name');
	$mysqlUserName = IniConfig::iniDataProperty('db_user');
	$mysqlPassword = IniConfig::iniDataProperty('db_pass');
	$mysqlHostName = IniConfig::iniDataProperty('db_host');
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
	exec('cd '. __DIR__ .'/../ && composer install && composer dump-autoload');
  //exec("composer install");
  //exec("composer dump-autoload");
	sleep(.5);
}

function formatDateArrays($date_raw) {
	if(is_null($date_raw)) {
		return null;
	}
	$date = new DateTime($date_raw);
	return array(
		'year' => $date->format('Y'),
		'unix' => $date->format('U'),
		'date_raw' => $date->format('Y-m-d'),
		'date_time_raw' => $date->format('Y-m-d H:i:s'),
		'date_ym' => $date->format('Y-m'),
		'long_date' => $date->format('F j, Y'),
		'short_date' => $date->format('M j, Y'),
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

function unauthorizedReloginResponse($response, $msg = 'Unauthorized Action') {
	$responseArr = standardResponse($status = false, $msg = $msg, $data = null);
	return $response->withJson($responseArr,401);
}

function unauthorizedResponse($response, $msg = 'Unauthorized Action') {
	$responseArr = standardResponse($status = false, $msg = $msg, $data = null);
	return $response->withJson($responseArr,403);
}

function badRequestResponse($response, $msg = 'Invalid Request') {
	$responseArr = standardResponse($status = false, $msg = $msg, $data = null);
	return $response->withJson($responseArr,400);
}

function notFoundResponse($response, $msg = 'Not Found') {
	$responseArr = standardResponse($status = false, $msg = $msg, $data = null);
	return $response->withJson($responseArr,404);
}

function exceptionResponse($response, $msg = 'Error', $code = 200, $error = null) {
	$responseArr = standardResponse($status = false, $msg = $msg, $data = null);
	$responseArr['error'] = $error;
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
	//$url = 'https://slack.com/api/'.$endpoint.'?'.http_build_query($params);
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

function formatAmazonLoginUserData($me) {
	$email = $me['email'];
	$name = explode(' ',$me['name']);
	$fname = $name[0];
	$lname = $name[1];
	$image = ''; //$me['image']['url'];\
	$id = $me['user_id'];

	$userData = array(
		'id' => $id,
		'provider' => 'Amazon',
		'email' => $email,
		'fname' => $fname,
		'lname' => $lname,
		'profile_image' => $image,
	);
	return $userData;
}

function formatGithubLoginUserData($me) {
	$email = $me['email'];
	$name = explode(' ',$me['name']);
	$fname = $name[0];
	$lname = $name[1];
	$image = $me['avatar_url']; //$me['image']['url'];\
	$id = $me['id'];

	$userData = array(
		'id' => $id,
		'provider' => 'Github',
		'email' => $email,
		'fname' => $fname,
		'lname' => $lname,
		'profile_image' => $image,
	);
	return $userData;
}

function formatDiscordLoginUserData($me) {
	$email = $me['email'];
	//$name = explode(' ',$me['name']);
	$fname = '';
	$lname = '';
	$id = $me['id'];
	$image = 'https://cdn.discordapp.com/avatars/'.$id.'/'.$me['avatar'].'.png'; //$me['image']['url'];\
	$username = $me['username'];

	$userData = array(
		'id' => $id,
		'provider' => 'Discord',
		'email' => $email,
		'fname' => $fname,
		'lname' => $lname,
		'profile_image' => $image,
	);
	return $userData;
}

function checkJwtFormat($token) {
	if(!is_string($token)) {
		return false;
	}
	$arr = explode('.',$token);
	if(count($arr) != 3) {
		return false;
	}
	if(preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $arr[0]) != true || preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $arr[1]) != true) {
     return false;
  }
	return true;
}

function getInstallSource() {
	$installType = 'source';
	if(is_dir(__DIR__ . '/../../../.git')) {
		$installType = 'git';
	}
	return $installType;
}

function getBranchOptions() {
	$installType = getInstallSource();
	$options = array('master');
	if($installType == 'git') {
		$options[] = 'dev';
	}
	return $options;
}

function executeGit($params, $trim=true) {
	$output = shell_exec("git ".$params);
	if($trim) {
		$output = trim(str_replace("\r\n",'',$output));
	}
	return $output;
}

function getGitVersion() {
	$cur_commit_hash = null;
	$remote_name = null;
	$branch_name = null;
	$installType = getInstallSource();
	if($installType == 'git') {
		$cur_commit_hash  = executeGit('rev-parse HEAD', $trim=true);
		//if(!preg_match('^[a-z0-9]+$', $cur_commit_hash)){
			//logger.error('Output does not look like a hash, not using it.')
		//	$cur_commit_hash = null;
		//}
		$remote_branch  = executeGit('rev-parse --abbrev-ref --symbolic-full-name @{u}', $trim=true);
		$remote_branch = explode('/',$remote_branch);
		if(count($remote_branch) == 2) {
			$remote_name = $remote_branch[0];
			$branch_name = $remote_branch[1];
		}
		if(is_null($remote_name)) {
			//logger.error('Could not retrieve remote name from git. Defaulting to origin.')
			$remote_name = 'origin';
		}
		if(is_null($branch_name)) {
			//logger.error('Could not retrieve branch name from git. Defaulting to master.')
			$branch_name = 'master';
		}
		$version = getVersion();
		return array(
			'install_type' => $installType,
			'tag' => $cur_commit_hash,
			'current_version' => $version,
			'remote_name' => $remote_name,
			'branch_name' => $branch_name,
		);
	}	else {
		$version = getVersion();
		return array(
			'install_type' => $installType,
			'hash' => null,
			'tag' => 'v'.$version,
			'current_version' => $version,
			'remote_name' => 'origin',
			'branch_name' => 'master',
		);
	}
}

function check_github($branch=null) {
	$versionInfo = getGitVersion();
	$latestVersion = $versionInfo['tag'];
	$commitsBehind = 0;
	$latestRelease = NULL;
	# Get the latest version available from github
	//logger.info('Retrieving latest version information from GitHub')
	try {
		$client = new GuzzleHttp\Client(['base_uri' => 'https://api.github.com/']);
		$response = $client->request('GET', 'repos/legoguy1000/frc-portal/commits/'.$versionInfo['branch_name']);
		$code = $response->getStatusCode(); // 200
		$reason = $response->getReasonPhrase(); // OK
		$gitData = json_decode($response->getBody());
	} catch (ClientException $e) {
		$error = Psr7\str($e->getResponse());
		insertLogs('Warning', $error);
		$result['msg'] = 'Something went wrong getting info from GitHub';
		$result['error'] = $error;
	} catch (Exception $e) {
			$error = handleExceptionMessage($e);
			insertLogs('Warning', $error);
			$result['msg'] = 'Something went wrong getting info from GitHub';
			$result['error'] = $error;
	}

	if(is_null($gitData)) {
		//logger.warn('Could not get the latest version from GitHub. Are you running a local development version?')
		return $versionInfo['tag'];
	}
	$latestVersion = $gitData->sha;
	//logger.info('Comparing currently installed version with latest GitHub version')
	$response = $client->request('GET', 'repos/legoguy1000/frc-portal/compare/'.$latestVersion.'...'.$versionInfo['tag']);
	$code = $response->getStatusCode(); // 200
	$reason = $response->getReasonPhrase(); // OK
	$gitData = json_decode($response->getBody());
	if(is_null($gitData)) {
		//logger.warn('Could not get commits behind from GitHub.')
		$versionInfo['latest_version'] = $latestVersion;
		return $versionInfo;
	}
	$commitsBehind = $gitData->behind_by;
	//echo "In total, ".$commitsBehind." commits behind";
	if($commitsBehind > 0 && $gitData->status == "behind") {
		//echo 'New version is available. You are '.$commitsBehind.' commits behind';
		$response = $client->request('GET', 'repos/legoguy1000/frc-portal/releases');
		$code = $response->getStatusCode(); // 200
		$reason = $response->getReasonPhrase(); // OK
		$gitData = json_decode($response->getBody());
		if(is_null($gitData)) {
			//logger.warn('Could not get releases from GitHub.')
			$versionInfo['latest_version'] = $latestVersion;
			return $versionInfo;
		}
		if($versionInfo['branch_name'] == 'master') {
			$filteredRleases = array_filter($gitData, function($obj){
				return $obj->prerelease == false ? true:false;
			});
			$release = $filteredRleases[0];
		} else if($versionInfo['branch_name'] == 'dev') {
			//$filteredRleases = array_filter($gitData, function($obj){
			//	return !substr_compare($obj->tag_name, '-nightly', -strlen('-nightly')) === 0 ? true:false;
			//});
			$release = $gitData[0];
		} else {
			$release = $gitData[0];
		}
		$latestRelease = $release->tag_name;
		$versionInfo['update_available'] = true;
	} else if($commitsBehind == 0 && $gitData->status == "identical") {
		//echo 'FRC Portal is up to date';;
		$versionInfo['update_available'] = false;
	}
	$versionInfo['commits_behind'] = $commitsBehind;
	$versionInfo['latest_version'] = $latestVersion;
	$versionInfo['latest_release'] = $latestRelease;
	return $versionInfo;
}

function updatePortal() {
	$versionInfo = check_github();
	if($versionInfo['install_type'] == 'git') {
		$output = shell_exec("git pull ".$versionInfo['remote_name']." ".$versionInfo['branch_name']);
		$outArr = explode('\n',$output);
		foreach($outArr as $line) {
			if(strpos($line, 'Already up-to-date.') !== false) {
				//logger.info('No update available, not updating')
				//logger.info('Output: ' + str(output))
			} elseif (substr_compare($line, 'Aborting', -strlen('Aborting')) === 0) {
				//logger.error('Unable to update from git: ' + line)
				//logger.info('Output: ' + str(output))
			}
		}
	} else {
		$client = new GuzzleHttp\Client(['base_uri' => 'https://github.com/']);
		$filePath = __DIR__ . '/../secured/'.date('Y-m-d').'-'.$versionInfo['branch_name'].'-github';
		$response = $client->request('GET', 'legoguy1000/frc-portal/tarball/'.$versionInfo['branch_name'], ['sink' => $filePath.'.tar.gz']);
		$code = $response->getStatusCode(); // 200
		$reason = $response->getReasonPhrase(); // OK
		//update_dir = os.path.join(plexpy.PROG_DIR, 'update')
		//version_path = os.path.join(plexpy.PROG_DIR, 'version.txt')
		//logger.info('Downloading update from: ' + tar_download_url)
		try {
				//logger.info('Extracting file: ' + tar_download_path)
		    $phar = new PharData($filePath.'.tar.gz');
		    $phar->extractTo($filePath, null, true); // extract all files
		} catch (Exception $e) {
		    // handle errors
		}
		# Delete the tar.gz
		unlink($filePath.'.tar.gz');
		//file_put_contents($file, $current);
	}
	//include(__DIR__ . '/../postUpgrade.php');
}

function getVersion() {
	//$version = null;
	//$version = file_get_contents(__DIR__.'/../secured/version.txt');
	//if(!isset($version) || is_null($version)) {
	  $version = VERSION;
	//}
	return $version;
}

function loginToFirst($email, $password) {
	$data = array();
	$client = new Client();
	// Go to the FIRST website
	$crawler = $client->request('GET', 'https://www.firstinspires.org');
	$form = $crawler->selectButton('edit-openid-connect-client-generic-login')->form();
	$crawler = $client->submit($form);
	$crawler1 = $crawler->filter('#modelJson')->eq(0);
	$json = html_entity_decode($crawler1->text());
	$json = json_decode($json);
	$form = $crawler->selectButton('LOG IN')->form();
	$domdocument = new \DOMDocument;
	$ff = $domdocument->createElement('input');
	$ff->setAttribute('name', $json->antiForgery->name);
	$ff->setAttribute('value', $json->antiForgery->value);
	$formfield = new InputFormField($ff);
	$form->set($formfield);

	$node = $form->getNode(0);
	$node->setAttribute('action', $json->loginUrl);
	$form['username'] = $email;
	$form['password'] = $password;
	$crawler = $client->submit($form);
	// remove all h2 nodes inside .content
	$crawler->filter('script')->each(function ($crawler) {
	    foreach ($crawler as $node) {
	        $node->parentNode->removeChild($node);
	    }
	});
	$form = $crawler->filter('form')->form();
	$crawler = $client->submit($form);
	$cookieJar = $client->getCookieJar();
	$cookies = array();
	$cookie = $cookieJar->get('DashboardTokenV0002', '/Dashboard', 'my.firstinspires.org');
	if(is_null($cookie)) {
		return false;
	}
	$cookies[] = 'DashboardTokenV0002='.$cookie->getValue();
	$cookie = $cookieJar->get('LBr', '/', 'my.firstinspires.org');
	if(!is_null($cookie)) {
		$cookies[] = 'LBr='.$cookie->getValue();
	}
	$cookie = $cookieJar->get('ASP.NET_SessionId', '/', 'my.firstinspires.org');
	if(!is_null($cookie)) {
		$cookies[] = 'ASP.NET_SessionId='.$cookie->getValue();
	}
	return implode('; ',$cookies);
}

use MadWizard\WebAuthn\Config\WebAuthnConfiguration;
function getWebAuthnConfiguration() {
	$config = new WebAuthnConfiguration();
	$env_url = rtrim(getSettingsProp('env_url'),'/');
	if($env_url == '') {
		$env_url = 'https://'.$_SERVER['HTTP_HOST'];
	}
	$config->setRelyingPartyId(preg_replace('#^https?://#', '', $env_url));
	$config->setRelyingPartyName('FRC Portal');
	$config->setRelyingPartyOrigin($env_url);
	return $config;
}

use MadWizard\WebAuthn\Server\UserIdentity;
use MadWizard\WebAuthn\Format\ByteBuffer;
use MadWizard\WebAuthn\Server\Registration\RegistrationOptions;
use MadWizard\WebAuthn\Dom\AuthenticatorSelectionCriteria;
use MadWizard\WebAuthn\Credential\UserHandle;
function getWebAuthnRegistrationOptions($user) {
	// Get user identity. Note that the userHandle should be a unique identifier for each user
	// (max 64 bytes). The WebAuthn specs recommend generating a random byte sequence for each
	// user. The code below is just for testing purposes!
	$userId = new UserIdentity(UserHandle::fromBuffer(new ByteBuffer($user->user_id)), $user->user_id, $user->full_name);
	$options = new RegistrationOptions($userId);
	$options->setAttestation('none');
	$options->setExcludeExistingCredentials(true);
	$criteria = new AuthenticatorSelectionCriteria();
	$criteria->setAuthenticatorAttachment('platform');
	$criteria->setUserVerification('preferred');
	$options->setAuthenticatorSelection($criteria);
	return $options;
}
?>

<?php

function getIniProp($prop) {
	$value = '';
	$ini = parse_ini_file(__DIR__.'/../secured/config.ini');
	if(isset($ini[$prop])) {
		$value = $ini[$prop];
	}
	return $value;
}

function getSettingsProp($prop) {
	$value = null;
	$setting = FrcPortal\Setting::where('setting',$prop)->first();
	//$ini = parse_ini_file(__DIR__.'/../secured/config.ini');
	if(!is_null($setting) && isset($setting->value)) {
		$value = $setting->value;
	}
	return $value;
}

function getVersionFile() {
	$value = '';
	$version = file_get_contents(__DIR__.'/../secured/version.txt');
	return $version;
}


?>

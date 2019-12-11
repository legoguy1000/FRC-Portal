<?php

function getIniProp($prop) {
	$value = '';
	$ini = parse_ini_file(__DIR__.'/../secured/config.ini');
	if(isset($ini[$prop])) {
		$value = $ini[$prop];
	}
	return $value;
}

function getSettingsProp($prop, $section = null) {
	$value = null;
	$setting = FrcPortal\Setting::where('setting',$prop);
	if($section != null) {
		$setting = $setting->where('section',$section);
	}
	$setting = $setting->first();
	//$ini = parse_ini_file(__DIR__.'/../secured/config.ini');
	if(!is_null($setting) && isset($setting->value)) {
		$value = $setting->value;
	}
	return $value;
}

?>

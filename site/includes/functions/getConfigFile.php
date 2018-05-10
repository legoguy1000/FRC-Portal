<?php

function getIniProp($prop) {
	$value = '';
	$ini = parse_ini_file(__DIR__.'/../config.ini');
	if(isset($ini[$prop])) {
		$value = $ini[$prop];
	}
	return $value;
}






?>

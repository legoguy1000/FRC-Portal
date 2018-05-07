<?php

function getIniProp($prop) {
	$value = '';
	$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/site/includes/config.ini');
	if(isset($ini[$prop])) {
		$value = $ini[$prop];
	}
	return $value;
}






?>

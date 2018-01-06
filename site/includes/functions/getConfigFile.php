<?php

function getIniProp($prop)
{
	$value = '';
	$ini = parse_ini_file('/home/team2363_admin/portal.team2363.org/site/includes/config.ini');
	if(isset($ini[$prop]))
	{
		$value = $ini[$prop];
	}
	return $value;
}






?>
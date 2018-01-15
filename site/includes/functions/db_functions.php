<?php

function db_connect() {

    // Define connection as a static variable, to avoid connecting more than once
    static $db;
	$db = mysqli_init();
    // Try and connect to the database, if a connection has not been established yet
	if (!$db) {
		die('mysqli_init failed');
	}

	if (!$db->options(MYSQLI_INIT_COMMAND, 'SET time_zone = "America/New_York"')) {
		die('Setting MYSQLI_INIT_COMMAND failed');
	}

	if (!$db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
		die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
	}

	$db_server = getIniProp('db_host'); //your mysql server
	$db_user = getIniProp('db_user'); //your mysql server username
	$db_pass = getIniProp('db_pass'); //your mysql server password
	$db_name = getIniProp('db_name'); //the mysql database to use

	if (!$db->real_connect($db_server, $db_user, $db_pass, $db_name)) {
		return $db->connect_error;
	}

    return $db;
}

function db_query($query) {
    // Connect to the database
    $db = db_connect();

    // Query the database
    $result = $db->query($query);
		if($result === false) {
			db_error($query);
			return false;
		}
    return $result;
}

function db_select($query) {
    $rows = array();
    $result = db_query($query);

    // If query failed, return `false`
    if($result === false) {
        return false;
    }

    // If query was successful, retrieve all the rows into an array
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function db_select_single($query) {
    $rows = array();
    $result = db_query($query);

    // If query failed, return `false`
    if($result === false) {
        return false;
    }

    $row = $result->fetch_assoc();
    return $row;
}

function db_begin_transaction() {
	$db = db_connect();
	$db->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
}

function db_commit() {
	$db = db_connect();
	$commit = $db->commit();
	if(!$commit) {
		$db->rollback();
	}
  return $commit;
}

function db_escape($value) {
    $db = db_connect();
    return mysqli_real_escape_string($db,$value);
}

function db_quote($value) {
    $db = db_connect();
    return '"'.db_escape($value).'"';
}

function db_error($query) {
    $db = db_connect();
	//	errorHandle(mysqli_error($db), $query);
	//die(mysqli_error($db));
    return mysqli_error($db);
}


?>

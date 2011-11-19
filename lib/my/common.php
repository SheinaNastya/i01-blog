<?php

	setlocale(LC_ALL, "ru_RU.UTF-8");

	$errors = '';

	function myErrorHandler($errno, $errstr, $errfile, $errline) {
		global $errors;
		$errors .= "<p>$errstr<br/>\n".
			  "  line $errline of file $errfile</p>\n";
	}
	set_error_handler('myErrorHandler');
	error_reporting(E_ALL);

	ini_set("include_path", ".:lib/pear:lib/my:../__pear");

	require_once("PEAR.php");

	require_once('HTTP/Session.php');

	HTTP_Session::useCookies(true);
	HTTP_Session::start('s', uniqid('ours'));
	HTTP_Session::setExpire(60);
	HTTP_Session::setIdle(5);
	HTTP_Session::updateIdle();

	require_once("db/dsn.php");
	require_once("stats.php");
	$stats =& new Stats($dsn,"blog");
	$stats->insertEntry();

	$theme = 'Default';

    $options =& PEAR::getStaticProperty('DB_DataObject','options');
    $config = parse_ini_file('db/dataobject.ini',TRUE);
    $options = $config['DB_DataObject'];

?>

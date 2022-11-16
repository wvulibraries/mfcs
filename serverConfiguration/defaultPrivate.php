<?php

// This file should be set to be readable only by the web server user and the system administrator (or root)

global $engineVarsPrivate;//MySQL Information

$engineVarsPrivate['mysql']['server']   = getenv("DATABASE_HOST");
$engineVarsPrivate['mysql']['port']     = getenv("DATABASE_PORT");
$engineVarsPrivate['mysql']['username'] = getenv("DATABASE_USER");
$engineVarsPrivate['mysql']['password'] = getenv("DATABASE_PASSWORD");

$engineVarsPrivate["privateVars"]["engineDB"] = array(
	array(
		'file'     => 'auth.php',
		'function' => '__construct',
	),
	array(
		'file'     => 'errorHandle.php',
		'function' => 'recordError',
	),
	array(
		'file'     => 'stats.php',
		'function' => '__construct',
	),
	array(
		'file'     => 'mysql.php',
		'function' => 'mysqlLogin',
	),
);
?>
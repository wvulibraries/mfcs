<?php

// I have updated this file to use getenv() to pull the environment variables from the docker-compose file
// This is a more secure way to store the database information
// Additional updates my be needed to the other files to use the environment variables
// like the engineDB default file.

global $engineVarsPrivate; // MySQL Information

$engineVarsPrivate['mysql']['server']   = getenv("DATABASE_HOST");
$engineVarsPrivate['mysql']['port']     = getenv("DATABASE_PORT");
$engineVarsPrivate['mysql']['username'] = getenv("DATABASE_USER");
$engineVarsPrivate['mysql']['password'] = getenv("DATABASE_PASSWORD");
$engineVarsPrivate['mysql']['database'] = getenv("DATABASE_NAME");

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
	// this doesn't appear to have ever been implemented
	// there should have been a choice to use mysql or ldap
	// either through a config or environment variable
	// array(
	// 	'file'     => 'mysql.php',
	// 	'function' => 'mysqlLogin',
	// ),
	array(
		'file'     => 'ldap.php',
		'function' => 'ldapLogin',
	),
);
?>
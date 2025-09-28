<?php
$url			= $_SERVER['HTTP_HOST'];
$active_group	= ENVIRONMENT == 'production' ? 'default' : 'development';
$query_builder	= TRUE;

$db['development'] = array(
	'dsn'	=> '',
	'hostname' => $_ENV['database.development.hostname'] ?: 'localhost',
	'username' => $_ENV['database.development.username'] ?: 'root',
	'password' => $_ENV['database.development.password'] ?: '',
	'database' => $_ENV['database.development.database'] ?: 'roketxpress_db',
	'dbdriver' => $_ENV['database.development.dbdriver'] ?: 'mysqli',
	'dbprefix' => $_ENV['database.development.dbprefix'] ?: '',
	'pconnect' => FALSE,
	'db_debug' => FALSE,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => $_ENV['database.default.hostname'] ?: 'localhost',
	'username' => $_ENV['database.default.username'] ?: 'root',
	'password' => $_ENV['database.default.password'] ?: '',
	'database' => $_ENV['database.default.database'] ?: 'roketxpress_db',
	'dbdriver' => $_ENV['database.default.dbdriver'] ?: 'mysqli',
	'dbprefix' => $_ENV['database.default.dbprefix'] ?: '',
	'pconnect' => FALSE,
	'db_debug' => FALSE,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

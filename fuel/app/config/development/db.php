<?php
/**
 * The development database settings. These get merged with the global settings.
 */

return array(
	'default' => array(
		'type' => 'pdo',
		'connection' => array(
			'dsn' => 'mysql:host=db;dbname=atcoder_archive_db',
			'username' => 'root',
			'password' => 'root',
		),
		'identifier'   => '`',
		'table_prefix' => '',
		'charset'      => 'utf8',
		'collation'    => 'utf8_unicode_ci',
		'enable_cache' => true,
		'profiling'    => false,
	),
);

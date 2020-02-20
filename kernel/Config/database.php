<?php

/**
 * Database configuration
 *
 * @return array set of database configurations
 * @author Moorexa <www.moorexa.com> 
 **/

$kernel->db([

	//enable access from PHP to MYSQL database.
	
	'new-db' => [
		'dsn' 		=> '{driver}:host={host};dbname={dbname};charset={charset}',
		'driver'    => 'mysql',
		'host' 	    => '',
		'user'      => '',
		'password'  => '',
		'dbname'    => '',
		'charset'   => 'utf8mb4',
		'port'      => '',
		'attributes'=> true,
		'handler'   => 'pdo',
		'production'=> [
			'driver'  =>   'mysql',
			'host'    =>   '',
			'user'    =>   '',
			'password'  =>   '',
			'dbname'    =>   '',
		],
	],

	'mediaccess-db' => [
		'dsn' 		=> '{driver}:host={host};dbname={dbname};charset={charset}',
		'driver'    => 'mysql',
		'host' 	    => 'localhost',
		'user'      => 'wekiwork',
		'password'  => 'Wekiwork@2019',
		'dbname'    => 'mediaccess',
		'charset'   => 'UTF8',
		'port'      => '8889',
		'handler'   => 'pdo',
		'prefix'	=> '',
		'attributes'=> true,
		'production'=> [
			'driver'  =>   'mysql',
			'host'    =>   'mysql5022.site4now.net',
			'user'    =>   'a0c157_mediapi',
			'password'  =>   'mediaccess@2019',
			'dbname'    =>   'db_a0c157_mediapi',
		],
		'production2'=> [
			'driver'  =>   'mysql',
			'host'    =>   'localhost',
			'user'    =>   'wekiwork',
			'password'  =>   'Wekiwork@2019',
			'dbname'    =>   'mediaccess',
		],
		'options'   => [ PDO::ATTR_PERSISTENT => true ]
	],
 
	
// choose from any of your configuration for a default connection
])
->default(['development' => 'mediaccess-db', 'live' => 'mediaccess-db'])
->domain('mediaccess.com.ng', ['development' => 'mediaccess-db@production2'])

// add channel to requests
->channel(function($request){
	$request->has('get', [Medi\Data::class, 'watchQueryRequest']);
});

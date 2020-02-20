<?php

// Application Aliases

$alias = [
	'Moorexa\Assets' 		=> ['path' => PATH_TO_ASSETS . 'assets.php'],
	'Moorexa\DB' 			=> ['path' => PATH_TO_DB . 'db.php'],
	'Moorexa\DBPromise' 	=> ['path' => PATH_TO_DB . 'dbPromise.php'],
	'Moorexa\TestManager' 	=> ['path' => PATH_TO_INC . 'testManager.php'],
	'Moorexa\DB\Pagination' => ['path' => PATH_TO_DB . 'pagination.php'],
	'Moorexa\Structure' 	=> ['path' => PATH_TO_DB . 'structure.php'],
	'Moorexa\Packages' 		=> ['path' => PATH_TO_INC . 'packages.php'],
	'Moorexa\Rexa' 			=> ['path' => PATH_TO_LIB . 'rexa.php'],
	'env' 					=> ['path' => PATH_TO_INC . 'env.php'],
	'Moorexa\Plugins' 		=> ['path' => PATH_TO_INC . 'plugins.php'],
	'async|Async' 			=> ['path' => 'async.php'],
	'task|Task' 			=> ['path' => PATH_TO_INC . 'tasks.php'],
	'settings|Settings' 	=> ['path' => PATH_TO_INC . 'settings.php'],
	'Moorexa\File' 			=> ['path' => PATH_TO_INC . 'files.php'],
	'ApiManager' 			=> ['path' => PATH_TO_INC . 'apimanager.php'],
	'Moorexa\ApiModel' 		=> ['path' => PATH_TO_INC . 'apimodel.php'],
	'Moorexa\Controller' 	=> ['path' => PATH_TO_INC . 'controllers.php'],
	'Authenticate' 			=> ['path' => PATH_TO_INC . 'authenticate.php'],
	'Objects|objects' 		=> ['path' => PATH_TO_LIB . 'object.php'],
	'Moorexa\Middleware' 	=> ['path' => PATH_TO_INC . 'middleware.php'],
	'Moorexa\Route' 		=> ['path' => PATH_TO_INC . 'routes.php'],
	'Moorexa\Location' 		=> ['path' => PATH_TO_INC . 'location.php'],
	'Moorexa\Event' 		=> ['path' => PATH_TO_INC . 'events.php'],
	'Moorexa\Model' 		=> ['path' => PATH_TO_INC . 'model.php'],
	'Moorexa\Dir' 			=> ['path' => PATH_TO_INC . 'dir.php'],
	'Moorexa\Request' 		=> ['path' => PATH_TO_INC . 'request.php'],
	'Moorexa\Registry' 		=> ['path' => PATH_TO_INC . 'registry.php'],
	'Moorexa\CHS' 			=> ['path' => PATH_TO_LIB . 'chs.php'],
	'Moorexa\UrlConfig' 	=> ['path' => PATH_TO_CONFIG . 'urlConfig.php'],
	'Moorexa\Template' 		=> ['path' => PATH_TO_INC . 'templates.php'],
	'Moorexa\Linker' 		=> ['path' => PATH_TO_INC . 'linker.php'],
	'Moorexa\Session' 		=> ['path' => PATH_TO_LIB . 'Browser/session.php'],
	'Moorexa\Cookie' 		=> ['path' => PATH_TO_LIB . 'Browser/cookie.php'],
	'Moorexa\Tag' 			=> ['path' => PATH_TO_INC . 'tags.php'],
	'Moorexa\Injectables' 	=> ['path' => PATH_TO_INC . 'injectable.php'],
	'Moorexa\ServiceManager'=> ['path' => PATH_TO_INC . 'serviceManager.php'],
	'Moorexa\HTTPHeaders' 	=> ['path' => PATH_TO_INC . 'httpheaders.php'],
	'Moorexa\Provider' 		=> ['path' => PATH_TO_INC . 'provider.php'],
	'Moorexa\Hash' 			=> ['path' => PATH_TO_INC . 'hashes.php'],
	'Moorexa\HttpPost' 		=> ['path' => PATH_TO_LIB . 'Http/httpPost.php'],
	'Moorexa\HttpGet' 		=> ['path' => PATH_TO_LIB . 'Http/httpGet.php'],
	'Moorexa\HttpApi' 		=> ['path' => PATH_TO_LIB . 'Http/httpApi.php'],
	'Moorexa\Form' 			=> ['path' => PATH_TO_LIB . 'form.php'],
	'Moorexa\DB\ORMReciever'=> ['path' => PATH_TO_DB . 'orm_db.php'],
	'Moorexa\DB\FetchRow' 	=> ['path' => PATH_TO_DB . 'fetch_db.php'],
	'Moorexa\DB\Table' 		=> ['path' => PATH_TO_DB . 'tables.php'],
	'Moorexa\Directive' 	=> ['path' => PATH_TO_LIB . 'directives.php'],
	'Moorexa\Interfaces\Directive' => ['path' => PATH_TO_INTERFACE . 'directives.php']
];

/*
***************************
* 
* @database.tables (default = array) 
* info: enables fluid communication with the database tables. Also provides aliases for tables
*/
$tables = [
	'mediaccess-db' => [
		'account',
		'account_types',
		'views',
		'labs',
		'states',
		'cities',
		'pharmacies',
		'hospitals',
		'doctors',
		'rating',
		'reviews',
		'rating_option',
		'account_verification',
		'wishlist',
		'photo_gallery',
		'web_photo',
		'orders',
		'account_groups',
		'payments',
		'pharmacytypes'
	]
];









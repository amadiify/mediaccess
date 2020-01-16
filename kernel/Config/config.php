<?php

// Configure app 
$kernel->bootstrap ([

	/*
	 ***************************
	 * 
	 * @config.comingSoon (default = false) 
	 * info: generates a coming soon template
	*/ 
	"comingSoon" => false,


	/*
	 ***************************
	 * 
	 * @config.maintainceMode (default = false) 
	 * info: generates a maintenance mode template
	*/
	"maintainceMode" => false,


	/*
	 ***************************
	 * 
	 * @config.timezone (default = false) 
	 * info: set default timezone for application
	*/
	"timezone" => 'GMT',


	/*
	 ***************************
	 * 
	 * @config.autogenerate.partials (default = true) 
	 * info: would generate partial if it doesn't exist.
	*/
	"autogenerate.partials" => true,


	/*
	 ***************************
	 * 
	 * @config.enable.caching (default = true) 
	 * info: enable caching for views
	 * !important if you will be using directives.
	*/
	"enable.caching" => true,

	
	/*
	 ***************************
	 * 
	 * @config.force_https (default = false) 
	 * info: force https for all route requests.
	 * You could also include paths and seperate them with a comma (,)
	 * (*) wildcard also supported.
	 * eg . app/*, *
	*/

	"force_https" => false,


	/*
	 ***************************
	 * 
	 * @config.activate_csrf_token (default = true) 
	 * info: allow the use of csrf_token in forms 
	*/	
	"activate_csrf_token" => true,


	/*
	 ***************************
	 * 
	 * @config.csrf_salf 
	 * info: Generates a csrf salt that would be used to double encryption when generating the csrf token
	*/
	'csrf_salt' => '91552ae187dffa0e1db025a2add4c25646c24d4d',


	/*
	 ***************************
	 * 
	 * @config.csrf_public_key 
	 * info: Your public csrf token for incoming http post,put requests via a thirdparty program.
	*/
	'csrf_public_key' => '4e31b4492d561eadddbfb675b9f62c5edf105c7d',

	/*
	 ***************************
	 * 
	 * @config.assist_token 
	 * info: Assist CLI Token for production transactions. like DB migration etc.
	 * You should apply token to this request header 'assist-cli-token'
	*/
	'assist_token' => '4ca9281c54eaabb543cbba31914f5728708a482a',


	/*
	 ***************************
	 * 
	 * @config.minifyhtml (default = false) 
	 * info: allow moorexa to minfy html rendering to the browser.
	*/
	'minifyhtml' => false,


	/*
	 ***************************
	 * 
	 * @config.use_data-src (default = false) 
	 * info: allow moorexa to load images after DOM has been loaded.
	*/
	'use_data-src' => false,


	/*
	 ***************************
	 * 
	 * @config.debugMode (default = 'on') 
	 * info: allow moorexa to display errors. Helpful during development.
	*/
	'debugMode' => 'on',


	/*
	 ***************************
	 * 
	 * @config.router_default (default = @starter/home) 
	 * 
	*/
	'router_default' => implode('/', array_values([
		    'controller' => config('router.default.controller'),
		    'view' 		 => config('router.default.view')
	])),

	/*
	 ***************************
	 * 
	 * @config.controller basepath (default = ROOT/pages) 
	 * 
	*/
	'controller.basepath' => HOME . 'pages',
	
	/*
	 ***************************
	 * 
	 * @config.secret_key (default = none) 
	 * info: secret key for encryption open SSL. 
	*/
	'secret_key' => '54c258a540fc1bff693e8e6bb7af27f0adc9e25d',


	/*
	 ***************************
	 * 
	 * @config.sanitize_html (default = true) 
	 * info: allow moorexa to sanitize html. Provides a safer output against injections.
	*/
	'sanitize_html' => false,



	/*
	 ***************************
	 * 
	 * @config.filter-input (default = true) 
	 * info: allow moorexa to filter user input, provides a safer output against injections.
	*/
	'filter-input' => true,

	
	/*
	 ***************************
	 * 
	 * @config.static_url (default = '') 
	 * info: Cookie free static url for serving static files. eg static.example.com
	*/
	'static_url' => 'https://cdn.jsdelivr.net/gh/amadiify/mediaccess/',


	/*
	 ***************************
	 * 
	 * @config.error_codes (default = array_config) 
	 * info: Add, Configure error codes and what they should represent.
	*/
	'error_codes' => [
		200  	=> ['flag' => 'Success', 'text' => 'Page Loaded Successfully'],
		204  	=> ['flag' => 'Invalid Controller'],
		404  	=> ['flag' => 'Page Not Found'],
		500  	=> ['flag' => 'Internal Server Error'],
		300  	=> ['flag' => 'Directory Not Found'],
		405  	=> ['flag' => 'Method Not Allowed'],
	],

	/*
	 ***************************
	 * 
	 * @config.http_access_control (default = array_config) 
	 * info: Add access control headers
	*/
	'http_access_control' => [
		'Content-Type',
		'X-Api-Token',
		'Api-Request-Token'
		// you can add more here
	]
]);


/*
 ***************************
 * 
 * @config.api (param = configuration (array)) 
 * info: manage api settings.
*/

$kernel->api()->listen();


/*
 ***************************
 * 
 * @config.finder (default = array_option ) 
 * info: set finder configuration for applications.
*/

$kernel->finder([

	/*
	 ***************************
	 * 
	 * @finder.autoloader (default = array ) 
	 * info: Enables quick access to files inside these directories listed in the array via namespacing.
	*/
	'autoloader' => [
		'lab/*',
		'utility/*',
		'utility/Classes/*'
	],


	/*
	 ***************************
	 * 
	 * @finder.namespacing (default = array ) 
	 * info: Enables quick access to files through namespacing
	*/
	'namespacing' => [
		'Exception\*' => 'utility/Exceptions/',
		'Component\*' => 'utility/Components/',
		'Plugin\*'	  => 'utility/Plugins/',
		'Controller\*' => 'system/Inc/'
	]
]);
<?php

namespace Moorexa;

use utility\Classes\BootMgr\Manager as BootMgr;

/**
 * @package Moorexa Core Dependencies
 * @version 0.0.1
 * @author Ifeanyi Amadi <amadiify.com>
 */

// for custom defines and for core application dependencies.
class SET
{
	private static $dependencies = 
	[ 
		PATH_TO_LIB 	. 	'funcLib',
		PATH_TO_INC 	. 	'autoloader',
		PATH_TO_CONFIG  .	'constants',
		PATH_TO_INC 	. 	'main',
		PATH_TO_DB  	.	'handler',
		PATH_TO_SERVICES .	'boot',
		PATH_TO_INC 	. 	'plugins',
		PATH_TO_CONFIG  .	'initSystem',
		PATH_TO_CONFIG  .	'db'
	];

	// loaded kernel instance
	public static $kernel = null;

	// loaded shortcuts
	public static $shortcuts = [];

	// loaded service vars
	public static $serviceVars = [];

	// setter for defines
	public function __set($name, $val)
	{
		//check if var hasn't been defined previously
		if (!defined($name))
		{
			define($name, $val);
		}
	}

	// load core dependencies... 
	public static function loadDependencies()
	{
		// get dependencies.
		$dependencies = self::$dependencies;

		// errors caught
		$errors = [];

		// kernel loaded.
		$kernel = null;

		// load dependencies
		array_walk($dependencies, function($path) use (&$errors, &$kernel)
		{
			switch (basename($path))
			{
				case 'initSystem':
					// load coreApp: contains the bootloader class.
					include_once  PATH_TO_CORE . 'coreApp.php';

					// load app: contains all we need for the views.
					include_once  PATH_TO_INC . 'view.php';

					// create an instance of bootloader
					$kernel = BootMgr::singleton(Bootloader::class);

					// load shortcuts
					include_once PATH_TO_EXTRA . 'shortcuts.php';

					// Collect system and user configuration
					include_once PATH_TO_CONFIG . 'config.php';

					// load custom directives for view
					include_once PATH_TO_EXTRA . 'directives.php';
					
					// load providers.
					include_once PATH_TO_SERVICES . 'registry.php';

					// load authentication handler for controllers and views
					include_once PATH_TO_SERVICES . 'authentication.php';

					// #note: at this point app hasn't started. we just booting application.
				break;

				case 'db':
					// load database configuration file.
					include_once PATH_TO_CONFIG . 'database.php';
				break;

				default:
					// check if dependency exists
					if (file_exists($path . '.php'))
					{
						if (basename($path) == 'constants')
						{
							$set = new SET;
						}

						include_once $path . '.php';
					}
					else
					{
						$errors[] = $path . '.php';
					}
			}
		});

		// save instance
		self::$kernel = $kernel;

		// have we any error? throw an exception
		if (count($errors) > 0)
		{
			throw new \Exception('Could not load this dependencies { '. implode ( ",", $errors )  . ' } please check and reload page. ');
		}
	}
}

// load dependencies
SET::loadDependencies();
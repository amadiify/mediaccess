<?php

Namespace Moorexa;

/**
 * Packages class
 *
 * @package Moorea Package loader
 * @author Moorexa <www.moorexa.com>
 * @version 0.0.1
 **/

class Packages
{
	// loaded packages
	private static $loadedPackages = [];

	// find package
	public static function __callStatic($meth, $args)
	{
		$first = isset($args[0]) ? $args[0] : null;
		$cont = isset(BootLoader::$helper['active_c']) ? BootLoader::$helper['active_c'] : null;

		if ($cont == null && Model::$cont != null)
		{
			$cont = Model::$cont;
		}


		if ($first !== null && substr($first, 0,2) == './')
		{
			$cont = substr($first, 2);
		}

		// load package.
		return self::loadPackage($cont, $meth, $args);
		
	}

	// load package
	public static function loadPackage($controller, $packageName, $args)
	{
		// only find path and create an instance for this package, if not previously called.
		if (!isset(self::$loadedPackages[$controller . '@' .$packageName]))
		{
			// build path to package
			$path = HOME . 'pages/' . ucfirst($controller) . '/Packages/' . $packageName . '.php';

			// check if package exists.
			if (file_exists($path))
			{
				include_once $path;

				$class = ucfirst($controller)."\\Packages\\$packageName";

				if (class_exists($class))
				{
					$ref = new \ReflectionClass($class);
					// does package utilizes a constructor?
					if ($ref->hasMethod('__construct'))
					{
						// get arguments 
						Bootloader::$instance->getArguments($class, '__construct', $const, $args);
						// new instance
						$package = $ref->invokeArgs($const);
					}
					else
					{
						// new instance
						$package = new $class;
					}

					// save called package so that next time it's request we dont have to check again
					self::$loadedPackages[$controller . '@' . $packageName] = $package;

					// return package
					return $package;
				}
				else
				{
					throw new \Exceptions\Packages\PackageException('Package class '.$class.' doesn\'t exists.');
				}
			}
			else
			{
				throw new \Exceptions\Packages\PackageException($controller, $packageName);			
			}
		}
		else
		{
			return self::$loadedPackages[$controller . '@' . $packageName];
		}
	}
} // END class 
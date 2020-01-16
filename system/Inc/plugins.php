<?php

namespace Moorexa;

/**
 * Plugins loader class
 *
 * @package Moorexa/Plugins<autoloader>
 * @author  wekiwork.
 * @copyright 2018 Amadi ifeanyi
 * @link  www.moorexa.com
 **/

// Plugin class 
class Plugins 
{
	public function __get($name)
	{
		include_once( PATH_TO_ASSETS . 'assets.php');
		
		$location = PATH_TO_PLUGIN . $name . '.php';
		
		if (file_exists($location))
		{
			include_once($location);

			$assets = new Assets();

			$className = ucfirst($name);
			$plugins = new Plugins();

			if (class_exists($className))
			{
				// create reflection class
				$ref = new ReflectionClass($className);

				if ($ref->hasMethod('__construct'))
				{
					Bootloader::$instance->getParameters($className, '__construct', $const);

					// invoke class
					$class = $ref->newInstanceArgs($const);
				}
				else
				{
					$class = new $className;
				}

				$class->image = $assets->image();
				$class->plugin = $plugins;
				$class->db = Bootloader::$helper['activedb'];
				$class->{Bootloader::$helper['connectWith']} = Bootloader::$helper['activedb']; 

				return $class;
			}
			else
			{
				
				$plugins->message->error("$location Plugin cannot be loaded. Class $className not found.");
				return false;
			}
		}
	}

	public static function __callStatic($meth, $prop)
	{
		$dir = PATH_TO_PLUGIN . $meth;

		if (is_dir($dir))
		{
			// get index file
			$indexfile = $dir . '/index.php';

			// include file
			if (file_exists($indexfile))
			{
				$basename = basename($dir);
                $base = strtoupper($basename);

                $constant = $base.'_PLUGIN';

                if (!defined($constant))
                {
				    define ($constant, $dir . '/');
                }

				include_once ($indexfile);	
			}
			else
			{
				$methfile = $dir . '/'.$meth.'.php';

				if (file_exists($methfile))
				{
					define ("PLUGIN", $dir . '/');

					include_once $methfile;
				}
				else
				{
					// noting really!
					throw new Exceptions\Plugins\PluginsException("Plugin startup index file for $meth not found in ". $dir . '/' . $meth);
				}
			}
			

			if (class_exists($meth))
			{
				// create reflection class
				$ref = new \ReflectionClass($meth);

				if ($ref->hasMethod('__construct'))
				{
					Bootloader::$instance->getParameters($meth, '__construct', $const, $prop);

					// invoke class
					return $ref->newInstanceArgs($const);
				}

				// fallback
				return new $meth;
			}
			else
			{
				$standard = ucfirst($meth);

				if (class_exists($standard))
				{
					// create reflection class
					$ref = new ReflectionClass($standard);

					if ($ref->hasMethod('__construct'))
					{
						Bootloader::$instance->getParameters($standard, '__construct', $const, $prop);
						// invoke class
						return $ref->newInstanceArgs($const);
					}

					// fallback
					return new $standard;
					
				}
				else
				{
					throw new Exceptions\Plugins\PluginsException("$standard class for plugin $meth not found in ". $indexfile);
				}
				
			}

			
		}
		else
		{
			throw new \Exceptions\Plugins\PluginsException("Plugin not found in ". PATH_TO_PLUGIN);
		}
	}

}
// END class
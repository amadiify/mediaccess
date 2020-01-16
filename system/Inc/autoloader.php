<?php

use utility\Classes\BootMgr\Manager as BootMgr;

/**
 * @package Moorexa Autoloader
 * @version 0.0.1
 * @author Ifeanyi Amadi
 */

include_once PATH_TO_CONFIG . 'aliases.php';


spl_autoload_register(function($class) use ($alias, $tables){

	$namespacing =& $alias;

	$continue = true;

	$dbsource = Moorexa\DatabaseHandler::$connectWith;

	$notdbtable = true;

	if (!is_null($dbsource))
	{
		if (strrpos($dbsource, '@') !== false)
		{
			$dbsource = substr($dbsource, 0, strpos($dbsource, '@'));
		}

		$data = isset($tables[$dbsource]) ? $tables[$dbsource] : [];

		if (count($data) > 0)
		{
			$preloaded = Moorexa\DatabaseHandler::$preloadedTables;

			if (is_null($preloaded))
			{
				$preloaded = Moorexa\DatabaseHandler::loadPreloadedTables($data);
			}

			$preloaded = array_flip($preloaded);
			
			if (isset($preloaded[$class]))
			{
				$notdbtable = false;
			}

			$preloaded = null;
		}
	}

	if (count($namespacing) > 0 && $notdbtable)
	{
		$found = false;
		$path = null;
		$keylist = [];
		$paths = [];

		$index = 0;

		foreach ($namespacing as $key => $arr)
		{
			if (strpos($key, '|') > 0)
			{
				$keylist[$index] = explode('|', $key);
				$paths[$index] = $arr['path'];
				$index++;
			}

			if ($class == $key)
			{
				$found = true;
				$path = $arr['path'];
				break;
			}
		}

		if ($found == false)
		{
			foreach ($keylist as $key => $arr)
			{
				foreach ($arr as $i => $cls)
				{
					if (strcmp($cls, $class) == 0)
					{
						$found = true;
						$path = $paths[$key];
						break;
					}
				}
			}
		}
			
		$arr = null;
		$namespacing = null;

		if ($found)
		{
			if (is_null($path))
			{
				throw new \Exceptions\Autoloader\AutoloaderException("Path: $path doesn't exists in config/aliases.php");
			}
			elseif (file_exists($path))
			{
				include_once $path;
			}

			$continue = false;
		}
	}
	
	if ($continue === true)
	{
		// namespace?
		if (strpos($class, '\\') > 0)
		{
			if (strpos($class, 'Moorexa') == 0 || strpos($class, 'moorexa') == 0)
			{
				$string = str_replace('Moorexa\\', '', $class);	
			}
			else
			{
				$string = $class;
			}

			$string = str_replace('\\', '/', $string);

			if (class_exists('Moorexa\Bootloader'))
			{
				$controller = isset(Moorexa\Bootloader::$helper['c_controller']) ? Moorexa\Bootloader::$helper['c_controller'] : "";

				if (strpos($string, 'Pages') == 0 || strpos($string, 'pages') == 0)
				{
					$str2 = strtolower($string);

					if (strpos($str2, 'models') > 0)
					{
						$uf = ucfirst($controller);
						$rem = str_ireplace("Pages/{$controller}/Main/", "", $string);

						$arr = explode('/', $rem);

						if (count($arr) > 0)
						{
							if ($arr[0] != 'Models')
							{
								$rem = 'Pages/' . implode('/', $arr);
							}
							else
							{
								$rem = 'Pages/'. $uf . '/' . implode('/', $arr);
							}

							$string = $rem;
						}
					}

					if (strpos($str2, 'packages') === 0)
					{
						
						$string = str_replace('\\', '/', $class);
						
						$exp = explode("/", $string);

						$controller = strtolower($exp[1]);


						if (is_dir(HOME . 'pages/'.$controller))
						{
							unset($exp[1]);

							$string = HOME . 'pages/'.$controller.'/'.strtolower(implode('/', $exp));
						}
					}

					if (strpos($str2, 'db') === 0)
					{
						$name = 'accounts';
						$class = substr($str2,2);	
					}
				}
			}
			
			$toarr = explode('/', $string);

			$mainClass = end($toarr);
			array_pop($toarr);

			$path = implode('/', $toarr);
				
			if (is_dir($path))
			{
				$_path = rtrim($path, '/') .'/'. $mainClass;

				$match = [$mainClass . '.php', ucfirst($mainClass).'.php', strtoupper($mainClass).'.php', strtolower($mainClass).'.php'];

				$path = deepScan($path, $match);

				if (file_exists($path))
				{
					include_once ($path);
				}
				else
				{
					throw new \Exceptions\Autoloader\AutoloaderException($_path . ' could not be found! Failed to load file');
				}

			}
			else
			{
				
				$autoload = finder('autoloader');

				$continue = true;

				$newPath = null;

				if (strlen($path) > 4)
				{
					$newPath = deepScan($path, $mainClass.'.php');
				}

				if (!is_null($newPath) && is_file($newPath))
				{
					include_once (strtolower($newPath));

					$continue = false;
				}
				else
				{
					if (is_array($autoload))
					{
						$file = basename($string);

						foreach ($autoload as $d => $dir)
						{
							$dir = rtrim($dir, '/');
							$dir = rtrim($dir, '/*');

							$seek = dig(HOME . $dir . '/', $file . '.php');

							if (strlen($seek) > 2 && file_exists($seek))
							{
								include_once $seek;

								$continue = false;
							}
						}
					}
				}


				if ($continue)
				{
					// check in namespacing
					$namespacing = finder('namespacing');

					if (is_array($namespacing))
					{
						foreach ($namespacing as $ns => $dir)
						{
							$ns = rtrim($ns, '*');
							$path = str_replace("/", '\\', $path);
							$ns2 = $path . '\\';

							$ns = rtrim($ns, "\\");
							$nsArray = explode("\\", $ns);

							$ns2 = rtrim($ns2, "\\");
							$ns2Array = explode("\\", $ns2);

							$len = count($nsArray);
							$newArray = array_splice($ns2Array, 0, $len);

							$ns2 = implode("\\", $newArray);

							if (strcmp($ns, $ns2) === 0)
							{
								$file = basename($string);
								$find = dig($dir, $file . '.php');
								
								if (strlen($find) > 2 && file_exists($find))
								{
									include_once $find;
								}
							}
						}
					}
				}
				
			}	
		}
		else
		{
			// boot manager has named?
			if (BootMgr::hasNamed($class))
			{
				// get class
				$bootClass = BootMgr::get($class);
		
				// create class alias
				class_alias(get_class($bootClass), $class);

				return false;
			}


			// check table config in aliases or throw en exception
			if (!$notdbtable)
			{	
				$createClass = function($class)
				{
					$newClass = new class($class) extends \Moorexa\DB\ORMReciever
					{
						private static $thisclass;
						public $name = null;

						public function __construct($className)
						{
							if (isset(\Moorexa\DatabaseHandler::$databaseTables[$className]))
							{
								$className = \Moorexa\DatabaseHandler::$databaseTables[$className];
							}

							$db = new \Moorexa\DB();
							$db->table = $className;

							parent::getInstance($db);

							$db = null;
							return $this;
						}
					};

					// get classname
					$className = get_class($newClass);
					class_alias($className, $class);
				};

				// create class
				$createClass($class);
			}
			else 
			{

				// excaped class ?
				$autoload = (object)[

				// check components folder
				'components' => function($class)
				{
					// match for 
					// check for possible file naming
					// must be an array
					$match = [$class . '.php', ucfirst($class).'.php', strtoupper($class).'.php', strtolower($class).'.php'];

					$path = deepScan(PATH_TO_COMPONENTS, $match);

					if ($path !== "")
					{
						return $path;
					}

					return false;
				},
				// check exceptions folder
				'exceptions' => function($class)
				{
					$path = deepScan(PATH_TO_EXCEPTIONS, [$class . '.php', ucfirst($class).'.php', strtoupper($class).'.php', strtolower($class).'.php']);

					if ($path !== "")
					{
						return $path;
					}

					return false;
				}

				];

				// load file 
				$found = false;

				foreach ($autoload as $func => $f)
				{
					// avoid duplication!
					// only load from one source
					if ($found === false)
					{
						$path = call_user_func($autoload->{$func}, $class);

						if ($path !== false && !is_array($path) && file_exists($path))
						{
							$found = true;
							include_once $path;
							break;
						}
					}
				}

				if ($found == false)
				{
					// throw exception
					$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,3);
					$trace = array_pop($trace);

					if (isset($trace['function']) && $trace['function'] == 'class_exists')
					{
						return false;
					}

					throw new \Exceptions\Autoloader\AutoloaderException("Class: $class doesn't exists. Autoload failed!");
				}
			}
		}

	}
	

});	

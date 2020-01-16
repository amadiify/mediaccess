<?php

namespace Moorexa;

/**
 * @package Moorexa Middleware Manager
 * @version 0.0.1
 * @author Ifeanyi Amadi
 */

class Middleware
{
	public static $active = [];
	private $caller = null;
	public static $waiting = [];
	public static $loaded = [];

	public function __construct($caller)
	{
		$this->caller = $caller;
	}
	
	public static function __callStatic($middleware, $params)
	{
		$middleware_dir = PATH_TO_MIDDLEWARE;

		$system = isset(self::$loaded['System']) ? self::$loaded['System'] : null;

		if (!empty(View::$external_config))
		{
			$middleware_dir = Model::$thirdparty_path . 'utility/Middlewares/';
		}

		if (is_null($system))
		{
			if (isset($_GET['__app_request__']))
			{
				$app = explode("/", $_GET['__app_request__']);
			}
			else
			{
				if (isset($_SERVER['REQUEST_QUERY_STRING']))
				{
					$app = explode('/', $_SERVER['REQUEST_QUERY_STRING']);
				}
			}
		}
		else
		{
			// system has been loaded.
			// get url
			$app = $system->getUrl();
			// url helper
			$system->urlHelper($app);
	
		}

		$first = isset($app[0]) ? $app[0] : null;

		if ($first !== null)
		{
			$cont = Bootloader::$helper['get_controller'];

			if (($cont !== ucfirst($first)) && (strtolower($cont) != strtolower($first)) )
			{
				$view = $first;
			}
			else
			{
				if (isset($app[1]))
				{
					$view = $app[1];
				}
				else
				{
					$view = isset(Bootloader::$helper['active_v']) ? Bootloader::$helper['active_v'] : config('router.default.view');
				}
			}
		}

		// check if method exits in middleware directory
		$file = deepScan($middleware_dir, [$middleware . '.php', ucfirst($middleware) . '.php', lcfirst($middleware) . '.php']);

		if ($file == "")
		{
			$file = deepScan( PATH_TO_MIDDLEWARE, [$middleware . '.php', ucfirst($middleware) . '.php', lcfirst($middleware) . '.php']);
		}
		
		if (strlen($file) > 2)
		{
			include_once($file);

			if(class_exists('\\'.$middleware))
			{
				$ref = new \ReflectionClass($middleware);

				// get constructor arguments
				if ($ref->hasMethod('__construct'))
				{
					Bootloader::$instance->getParameters($middleware, '__construct', $const, $params);
					// create instance
					$mw = $ref->newInstanceArgs($const);
				}
				else
				{
					// create instance
					$mw = new $middleware;
				}
										
				$vars = \System::$local_vars;
				
				if (count($vars) > 0)
				{
					foreach ($vars as $key => $val)
					{
						$mw->{$key} = $val;
					}
				}

				// ensure view is not empty
				$view = empty($view) ? config('router.default.view') : $view;
			
				$vars = null;
				if (get_class($mw) != 'System')
				{
					if (isset($view))
					{
						Middleware::$waiting[$view][] = $mw;
					}
				}

				// save middleware
				self::$loaded[get_class($mw)] = $mw;
				
				return $mw;
			}
			else
			{
				throw new \Exceptions\Middleware\MiddlewareException("Middleware class '$middleware' not found.");
			}
		}
		else
		{
			throw new \Exceptions\Middleware\MiddlewareException("Middleware '$middleware' doesn't exist.");
		}
	}

	public function except()
	{
		if ($this->caller == 'auth')
		{
			$this->arguments = func_get_args();

			return $this;
		}

		return null;
	}

	public function apply()
	{
		if ($this->caller === 'auth')
		{
			$args = func_get_args();

			$app = new View();
			
			if (isset($this->arguments))
			{
				$req = Bootloader::$pagePath;

				$found = false;

				$flip = [];

				foreach ($req as $i => $x)
				{
					if (!is_bool($x) && !is_null($x))
					{
						$flip[] = $x;
					}
				}

				$flip = array_flip($flip);

				foreach ($this->arguments as $i => $x)
				{
					if (isset($flip[$x]))
					{
						$found = true;
						break;
					}
				}

				if ($found === false)
				{
					Bootloader::$instance->getParameters($app, 'auth', $const, $args);

					return call_user_func_array([$app, 'auth'], $const);
				}
			}
			else
			{
				Bootloader::$instance->getParameters($app, 'auth', $const, $args);

				return call_user_func_array([$app, 'auth'], $const);
			}
		}

		return null;
	}

	// call waiting
	public static function callWaiting($list, $render)
	{
		$complete = 0;
		$total = count($list);

		$called = function() use (&$complete)
		{
			$complete++;
		};

		foreach ($list as $i => $obj)
		{
			if (is_object($obj) && method_exists($obj, 'request'))
			{
				Bootloader::$instance->getParameters($obj, 'request', $const, [$called]);

				call_user_func_array([$obj, 'request'], $const);
					
			}
			else
			{
				$complete++;
			}
		}

		if ($complete == $total)
		{
			$render();
		}

		foreach ($list as $i => $obj)
		{
			if (is_object($obj) && method_exists($obj, 'requestClosed'))
			{
				Bootloader::$instance->getParameters($obj, 'requestClosed', $const, []);

				call_user_func_array([$obj, 'requestClosed'], $const);
			}
		}
	}
}
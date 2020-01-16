<?php

namespace Moorexa;

use utility\Classes\BootMgr\Manager as BootMgr;

/**
 * @package Moorexa Router
 * @version 0.0.1
 * @author  Ifeanyi Amadi
 */

class Route
{
	protected $type = null;
	private static $instance = null;
	public  static $request = null;
	public  static $requestUri = [];
	public 	static $requestMatch = [];
	public  static $controllerVars = [];
	private static $requestMethod = null;
	public  static $thirdparty_path = "";
	public  static $callback = null;
	private static $caller = [];
	private static $lastMemory = [];
	private static $servingThirdparty = false;
	private static $controllerFound = false;
	public  static $channelData = [];
	public  static $channelChs = null;
	private static $closureName = null;
	private static $closureUsed = [];
	private $__name;
	private $__type;
	private $is_match = false;
	public static $loadedProviders = [];

	// apply route to a page
	public static function page($name, $callback = false)
	{
		if (is_null(self::$instance))
		{
			self::$instance = new Route;
		}

		if (is_null(Route::$request))
		{
			Route::$request = Bootloader::$getUrl;
		}

		self::$instance->__name = explode('|', $name);
		self::$instance->__type = 'page';
		self::$instance->is_match = false;
		self::$channelData = [];
		self::$channelChs = null;

		if ($callback !== false)
		{
			if (isset(Route::$request[1]))
			{
				$app = Route::$request[1];

				if (in_array($app, self::$instance->__name))
				{
					self::$instance->is_match = true;
					// call closure function.
					call_user_func($callback, controller($app));
				}
			}
		}
		// return instance;
		return self::$instance;
	}

	// apply route to a controller
	public static function controller($name, $callback = false)
	{
		if (is_null(self::$instance))
		{
			self::$instance = new Route;
		}

		if (is_null(Route::$request))
		{
			Route::$request = Bootloader::$getUrl;
		}

		self::$instance->__type = 'controller';
		self::$instance->is_match = false;
		self::$instance->__name = explode('|', $name);
		self::$instance->routerRequest = false;
		self::$instance->packageFound = false;
		self::$channelData = [];
		self::$channelChs = null;

		if ($callback !== false && is_callable($callback))
		{
			if (isset(Route::$request[0]))
			{
				$app = Route::$request[0];

				if (in_array($app, self::$instance->__name))
				{
					self::$instance->is_match = true;
					// call closure function.
					$closureReturnData = call_user_func($callback, controller($app));

					if (is_string($closureReturnData))
					{
						Route::$requestMatch = explode('/', $closureReturnData);
					}
				}
			}
		}
		else
		{
			if (isset(Route::$request[0]))
			{
				$default = config('router.default.controller');
				$app = Route::$request[0];

				if (in_array($app, self::$instance->__name))
				{
					self::$controllerFound = $app;
					Route::$request = array_slice(Route::$request, 1);
				}
				else
				{
					if (in_array($default, self::$instance->__name))
					{
						self::$controllerFound = $default;
					}
					else
					{
						self::$controllerFound = false;
						Route::$request = null;
					}
				}
			}
			else
			{
				self::$controllerFound = false;
				Route::$request = null;
			}
		}

		if (!self::$controllerFound)
		{
			self::$instance->is_match = true;
		}

		return self::$instance;
	}

	// load middleware
	private function loadMiddleware($bus, $callback)
	{
		$name = $this->__name;

		// get
		$bus = explode('|', $bus);

		// load middleware
		switch ($this->__type)
		{
			// page
			case 'page':
				$page = isset(Route::$request[1])  ? Route::$request[1] : Route::$request[0];

				if (in_array($page, $name))
				{
					run_middleware($bus, $callback);
				}
			break;

			// controller
			case 'controller':
				$app = isset(Route::$request[0]) ? Route::$request[0] : null;

				if (!is_null($app))
				{
					if (in_array($app, $name))
					{
						run_middleware($bus, $callback);
					}
				}
			break;

			default:
				$bus = explode('@', implode('|', $bus));
				list($middleware) = $bus;
				// load middleware
				$instance = call_user_func('\\Moorexa\Middleware::'.$middleware);
				// try call method
				if (count($bus) > 0)
				{
					$method = $bus[1];
					// get arguments
					$args = func_get_args();
					$args = array_splice($args, 1);
					// load arguments
					Bootloader::$instance->getParameters($instance, $method, $const, $args);
					// load method
					call_user_func_array([$instance, $method], $const);
				}

				// return instance
				return $this;
		}
	}

	// prepare middleware for route.
	public function middleware($bus, $callback=null)
	{
		// load when match
		if ($this->is_match)
		{
			// get type
			switch (gettype($bus))
			{
				// string
				case 'string':
					$this->loadMiddleware($bus, $callback);
				break;

				// array
				case 'array':
					array_walk($bus, function($middleware) use (&$callback)
					{
						// load middleware
						$this->loadMiddleware($middleware, $callback);
					});
				break;
			}
		}

		// return object.
		return $this;
	}

	// load provider for route
	private function loadProvider($bus)
	{
		// get provider
		$bus = explode('@', $bus);
		// provider
		list($provider) = $bus;

		// append Provider 
		$provider = ucfirst($provider) . 'Provider';

		// build provider class
		$className = '\\Providers\\'.ucwords($provider);
		// check if provider exists
		$path = PATH_TO_PROVIDER . ucfirst($provider) . '.php';
		// throw exception if provider doesn't exists
		throw_unless(!file_exists($path), ['\Exceptions\Providers\ProviderException', 'Provider '.$provider.' doesn\'t exists.']);
		// load provider
		include_once $path;
		// check if provider class exists
		throw_unless(!class_exists($className), ['\Exceptions\Providers\ProviderException', 'Provider Class '.$className.' doesn\'t exists.']);
		// create provider class
		if (class_exists($className))
		{
			// create reflection class
			$clas = BootMgr::singleton($className, [Bootloader::$instance]);
			
			if (BootMgr::$BOOTMODE[$className] == CAN_CONTINUE)
			{
				if (method_exists($clas, 'boot'))
				{
					$request = $className . '@boot';

					BootMgr::method($request, null);

					if (BootMgr::$BOOTMODE[$request] == CAN_CONTINUE)
					{
						Bootloader::$instance->getParameters($className, 'boot', $const, [Bootloader::$instance]);
						BootMgr::methodGotCalled($request, call_user_func_array([$clas, 'boot'], $const));

						// add to loaded provider
						self::$loadedProviders[$className] = $clas;
					}
				}

				// ensure it has the 
				if (isset($bus[1]) && $bus[1] != 'boot')
				{
					$meth = $bus[1];
					// check method.
					if (method_exists($clas, $meth))
					{
						$request = $className . '@' . $meth;

						BootMgr::method($request, null);
                        
						if (BootMgr::$BOOTMODE[$request] == CAN_CONTINUE)
						{
							Bootloader::$instance->getParameters($className, $meth, $const, [Bootloader::$instance]);
							BootMgr::methodGotCalled($request, call_user_func_array([$clas, $meth], $const));
						}
					}
				}
			}
		}
	}

	// prepare provider for route.
	public function provider($bus, $callback=null)
	{
		// only load if route was successful
		if ($this->is_match)
		{
			// get type
			switch (gettype($bus))
			{
				// string
				case 'string':
					$this->loadProvider($bus, $callback);
				break;

				// array
				case 'array':
					array_walk($bus, function($provider) use (&$callback)
					{
						// load provider
						$this->loadProvider($provider, $callback);
					});
				break;
			}
		}

		// return object
		return $this;
	}

	// load authentication for route
	private function loadAuthentication($bus, $callback)
	{
		// get handler
		list($handler, $method) = explode('@', $bus);
		// build path
		$dir = PATH_TO_AUTHENTICATION;
		// get path
		$path = deepScan($dir, [$handler.'.php', $handler.'.auth.php']);
		throw_unless(empty($path), ['\Exceptions\Authentication\AuthenticationException', 'Invalid Authentication handler \''.$handler.'\'']);

		// include handler
		include_once $path;

		$handler = basename($handler);

		if (strpos($handler, '.auth') === false)
		{
			$handler .= '.auth';
		}

		$class = ucwords(str_replace(".", ' ', $handler));
		$class = preg_replace('/[\s]{1,}/', '', $class);

		$handler = BootMgr::singleton($class);

		if (BootMgr::$BOOTMODE[$class] == CAN_CONTINUE)
		{
			// trigger error if method doesn't exists.
			throw_unless(!method_exists($handler, $method), ['\Exceptions\Authentication\AuthenticationException', $class . ' handler method > ' . $method . ' doesn\'t exists. Authentication failed!.']);

			// build request
			$request = $class . '@' . $method;
			BootMgr::method($request, null);

			if (BootMgr::$BOOTMODE[$request] == CAN_CONTINUE)
			{
				// call method
				Bootloader::$instance->getParameters($handler, $method, $const);
				BootMgr::methodGotCalled($request, call_user_func_array([$handler, $method], $const));
			}
		}

		// return object
		return $this;
	}

	// prepare authentication for route.
	public function authentication($bus, $callback=null)
	{
		// only load if route was successful
		if ($this->is_match)
		{
			// get type
			switch (gettype($bus))
			{
				// string
				case 'string':
					$this->loadAuthentication($bus, $callback);
				break;

				// array
				case 'array':
					array_walk($bus, function($auth) use (&$callback)
					{
						// load authentication
						$this->loadAuthentication($auth, $callback);
					});
				break;
			}
		}

		// return object
		return $this;
	}

	// match a path
	private static function match($path, $callback, $regxpArray = null)
	{
		ob_start();

		$fullpath = "";
		self::$channelData = [];
		self::$channelChs = null;

		// create instance
		if (is_null(self::$instance))
		{
			self::$instance = new self;
		}

		if (self::$servingThirdparty !== false)
		{
			$uri = self::$requestUri;
			$val = self::$servingThirdparty;

			$key = array_key($val, $uri);
			unset($uri[$key]);

			self::$requestUri = $uri;
		}

		if (is_array(self::$requestUri) && count(self::$requestUri) > 0 && count(self::$requestMatch) == 0)
		{
			// get uri
			$uri = self::$requestUri;

			// check if path has opening brackets
			if (preg_match_all('/([(].*?[)]?(.*[)]))/', $path, $matches))
			{
				// push to regxparray
				array_walk($matches[2], function($match, $index) use (&$path, &$regxpArray)
				{
					// regxp key for match
					$key = 'uri'.$index;
					// remove trailing )
					$match = preg_replace('/[)]$/','',$match);
					// replace match with key
					$path = str_replace('('.$match.')', '{'.$key.'}', $path);
					// push to regxparray
					$regxpArray[$key] = '('.$match.')';
				});
			}

			$parameters = [];
			$success = false;

			// ensure size is equal
			$pathUri = explode('/', $path);

			array_walk($pathUri, function($req, $index) use (&$uri, &$path, &$regxpArray, &$parameters, &$pathUri)
			{
				if (isset($uri[$index]))
				{
					// request passed from the browser at this index
					$uriReq = $uri[$index];
					// replace {} with expression
					// search for binding
					if (preg_match_all('/([\{]([\S\s]*?)[\}])/', $req, $matches))
					{
						// get bind
						foreach ($matches[2] as $i => $bind)
						{
							$optional = false;

							// bind original
							$bindOriginal = $bind;

							// optional ?
							if (strrpos($bind,'?') !== false)
							{
								$optional = true;
								// remove question mark
								$bind = preg_replace('/[?]$/','',$bind);
							}

							// check if bind exists in regxpArray
							if (isset($regxpArray[$bind]))
							{
								// get expression
								$getExp = $regxpArray[$bind];
								// we run regxp here
								// first we replace $req on this index
								$req = str_replace('{'.$bindOriginal.'}', $getExp, $req);

								// check for {} bind after str_replace
								self::findBindInPath($req, $regxpArray);
							}
							else
							{
								$exp = $optional ? '/([\S]*)/' : '/([\S]+)/';
								// replace 
								$req = str_replace('{'.$bindOriginal.'}', $exp, $req);
								$regxpArray[$bind] = $exp;
							}


							// remove /( or /[ so we can make a proper regxp
							$req = preg_replace('/[\/]\s{0}([\(|\[])/','$1', $req);
							$req = preg_replace('/([\)|\]])\s{0}[\/]/','$1', $req);

							// quote request
							$quoteRequest = preg_replace('/(\\\{0}[\/])/','\/',$req);
							$quoteRequest = str_replace('\\\\','\\',$quoteRequest);
							
							// run regxp
							$exec = preg_match_all("/^($quoteRequest)/i", $uriReq, $match, 2);
							if ($exec)
							{
								// best match
								$bestMatch = $match[0][0];

								// get params
								$result = end($match[0]);

								// assign parameters
								if ($optional)
								{
									$pathUri[$index] = $bestMatch == '' ? ($uri[$index]) : $bestMatch;
									$result = $bestMatch == '' ? $pathUri[$index] : $result;
								}
								else
								{
									$pathUri[$index] = $bestMatch;
								}

								$parameters[$bind] = $result;
							}
							
						}
					}
				}
				else
				{
					// remove index.
					unset($pathUri[$index]);
					// set parameter to null.
					array_push($parameters, null);
				}
			});

			// now get call back params
			if (is_callable($callback))
			{
				// get params
				$ref = new \ReflectionFunction($callback);
				$params = $ref->getParameters();

				// created parameters
				$newParams = [];

				// get names
				array_walk($params, function($param, $index) use (&$newParams, $parameters)
				{
					$name = $param->getName();
					if (isset($parameters[$name]))
					{
						$newParams[$index] = $parameters[$name];
					}
					else
					{
						// get keys
						$keys = array_keys($parameters);
						if (isset($keys[$index]) && isset($parameters[$keys[$index]]))
						{
							$newParams[$index] = $parameters[$keys[$index]];
						}
						else
						{
							$newParams[$index] = null;
						}
					}
				});

				// clean 
				$ref = null;
				$params = null;

				// home path
				$homePath = false;

				if (implode('/', $pathUri) == '/')
				{
					if (count($uri) == 1)
					{
						$homePath = true;
						$newParams = $uri;
					}
				}

				// compare $pathUri with $uri
				if ( (implode('/', $pathUri) == implode('/', $uri)) || $homePath == true)
				{
					// success
					// matched!
					self::$instance->is_match = true;
					// call callback now
					self::getParameters($callback, $const, $newParams);
					$data = call_user_func_array($callback, $const);
					self::$requestMatch = explode('/', $data);
				}
			}
			
		}

		Route::$lastMemory[] = [$path, $callback];

		if (self::$servingThirdparty === false)
		{
			//self::thirdpartyLoader($path, $fullpath);
		}

		return self::$instance;

	}	

	// find binds from complex paths
	public static function findBindInPath(&$path, &$regxpArray)
	{
		if (preg_match_all('/([\{]([a-zA-Z0-9_-]*?)[\}])/', $path, $matches1))
		{
			foreach ($matches1[2] as $index => $bind)
			{
				$optional = false;

				// bind original
				$bindOriginal = $bind;

				// optional ?
				if (strrpos($bind,'?') !== false)
				{
					$optional = true;

					// remove question mark
					$bind = preg_replace('/[?]$/','',$bind);
				}

				// check if bind exists in regxpArray
				if (isset($regxpArray[$bind]))
				{
					// get expression
					$getExp = $regxpArray[$bind];
					// we run regxp here
					// first we replace $req on this index
					$path = str_replace('{'.$bindOriginal.'}', $getExp, $path);
				}
				else
				{
					$exp = $optional ? '/([\S]*)/' : '/([\S]+)/';
					// replace 
					$path = str_replace('{'.$bindOriginal.'}', $exp, $path);
					$regxpArray[$bind] = $exp;
				}

				// check again and call if match
				if (preg_match('/([\{]([a-zA-Z0-9_-]*?)[\}])/', $path))
				{
					// call
					self::findBindInPath($path, $regxpArray);
				}
			}
		}
	}

	public function __call($meth, $arg)
	{
		if ($meth == 'as' || $meth == 'named')
		{
			return $this->_as(
				$arg[0]
			);
		}
	}

	public static function getParameters($arg, &$bind, $other = [])
	{
		$continue = false;

		if (is_string($arg))
		{
			if (strpos($arg, '::') === false)
			{
				$continue = true;
			}
		}
		elseif (is_callable($arg))
		{
			$continue = true;
		}

		if ($continue)
		{
				
			$ref = new \ReflectionFunction($arg);
			$params = $ref->getParameters();

			$url = $other;

			if (is_array($url))
			{
				$url = array_values($url);
			}

			$ref = null;
			$meth = null;
			$newarr = [];
		
			if (count($params) > 0)
			{
				foreach ($params as $i => $obj)
				{
					$newarr[$i][] = $obj->name;

					$ref = new \ReflectionParameter($arg, $i);

					try
					{
						$class = $ref->getClass();

						if ($class !== null)
						{
							if ($class->isInstantiable())
							{
								$class = new $class->name;

								if (is_subclass_of($class, '\Moorexa\ApiModel'))
								{
									// get rules
									ApiModel::getSetRules($class);
								}
								
								$newarr[$i][] = $class;
							}
						}
						else
						{
							if ($ref->isDefaultValueAvailable())
							{
								$val = $ref->getDefaultValue();
								$newarr[$i][] = $val;
							}
						}
					}
					catch(Exception $e)
					{
						
					}

					$ref = null; $val = null;
				}

				$obj = null;
			}
			
			$params = null;
			

			$pushed = [];

			foreach ($newarr as $i => $x)
			{
				if (isset($x[1]))
				{
					if (is_object($x[1]))
					{
						$pushed[$i] = $x[1];
						unset($newarr[$i]);
					}
					else
					{
						$pushed[$i] = null;
					}
				}
				else
				{
					$pushed[$i] = null;
				}
			}

			$values = array_values($newarr);
			$index = 0;

			foreach($pushed as $i => $val)
			{
				if ($val == null)
				{
					if (isset($values[$index]))
					{
						$x = isset($values[$index][1]) ? $values[$index][1] : null;

						if (!is_null($x))
						{
							if (isset($url[$index]))
							{
								$pushed[$i] = $url[$index];
							}
							else
							{
								$pushed[$i] = $x;
							}
						}
						else
						{
							if (isset($url[$index]))
							{
								$pushed[$i] = $url[$index];
							}	
						}
					}

					$index++;
				}
			}

			$bind = $pushed;

			$ref = null;
		}
		else
		{
			$bind = is_null($bind) ? [] : $bind;
		}
	}

	// aliases
	public function _as($name)
	{
		$last = end(Route::$lastMemory);
		array_pop(Route::$lastMemory);

		Route::$caller[$name] = end($last);
	}

	// caller
	public static function __callStatic($name, $args)
	{
		if (isset(self::$closureUsed[$name]))
		{
			return self::$closureUsed[$name];
		}

		if (isset(Route::$caller[$name]))
		{
			$data = call_user_func_array(Route::$caller[$name], $args);
			Route::$requestMatch = explode('/', $data);
			return $data;
		}
		else
		{
			if (isset($_POST['REQUEST_METHOD']))
			{
				$method = $_POST['REQUEST_METHOD'];
				$_SERVER['REQUEST_METHOD'] = $method;

				// format args
				self::formatArgs($args);

				return call_user_func_array('\Moorexa\Route::request', $args);
			}	
		}
	}

	// Any Request
	public static function any()
	{
		$arg = func_get_args();
		$end = end($arg);

		self::$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'get';

		// assign arg1
		if (!isset($arg[1]))
		{
			$arg[1] = function(){};
		}

		// format args
		self::formatArgs($arg);

		$match = call_user_func_array('\Moorexa\Route::match', $arg);		

		if (is_string($end))
		{
			self::$closureUsed[$end] = $arg[1];
		}

		return $match;
	}

	// specific requests
	public static function request($types, $match = null, $call = null)
	{
		$arg = \func_get_args();

		$end = end($arg);


		// split
		$types = explode('|', $types);

		$valid = false;
		$meth = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		foreach ($types as $i => $type)
		{
			if ($type != '')
			{
				if (strtolower($type) == strtolower($meth))
				{
					$valid = true;
					break;
				}
			}
		}

		if ($valid === true && $call !== null )
		{
			self::$requestMatch = [];

			// format args
			self::formatArgs($arg);

			$match = call_user_func_array('\Moorexa\Route::match', $arg);
			

			if (is_string($end))
			{
				self::$closureUsed[$end] = $call;
			}

			return $match;
		}
		elseif ($valid === true && is_null($call))
		{
			return call_user_func($match);
		}
	}

	// format arguments
	private static function formatArgs(&$args)
	{
		/**
		 * #1 path
		 * #2 callback
		 * #3 regxp array
		 */
		$newArgs = [];

		// run through
		array_walk($args, function($data) use (&$newArgs){
			// set type
			switch (gettype($data))
			{
				// path
				case 'string':
					$newArgs[0] = $data;
				break;

				// case array
				case 'array':
					$newArgs[2] = $data;
				break;

				// callback
				default:
					if (is_callable($data))
					{
						$newArgs[1] = $data;
					}
			}
		});

		// switch to newargs
		ksort($newArgs);
		$args = $newArgs;
	}

	//GET request
	public static function get()
	{
		$arg = func_get_args();

		$end = end($arg);

		if (is_string($arg[0]))
		{
			$is = self::method('get');

			if ($is->method)
			{
				// format args
				self::formatArgs($arg);

				$match = call_user_func_array('\Moorexa\Route::match', $arg);

				return $match;
			}

			self::$lastMemory[] = [$arg[0], $arg[1]];
		}
		elseif (is_callable($arg[0]))
		{
			if (self::$requestMethod == 'get')
			{
				call_user_func($arg[0]);
				self::$lastMemory[] = [$arg[0], $arg[1]];
			}
		}

		return new self;
	}

	// POST request
	public static function post()
	{
		$arg = func_get_args();

		$end = end($arg);

		if (is_string($end))
		{
			self::$closureName = $end;
		}

		if (is_string($arg[0]))
		{
			$is = self::method('post');

			if ($is->method)
			{
				// format args
				self::formatArgs($arg);

				$match = call_user_func_array('\Moorexa\Route::match', $arg);				

				if (is_string($end))
				{
					self::$closureUsed[$end] = $arg[1];
				}

				return $match;
			}

			self::$lastMemory[] = [$arg[0], $arg[1]];
		}
		elseif (is_callable($arg[0]))
		{
			$meth = $_SERVER['REQUEST_METHOD'];

			self::$lastMemory[] = [$arg[0]];

			if ($meth == 'POST')
			{
				call_user_func($arg[0]);
			}
		}

		return new self;
	}

	// DELETE request
	public static function delete()
	{
		$arg = func_get_args();

		$end = end($arg);

		if (is_string($arg[0]))
		{
			$is = self::method('delete');

			if ($is->method)
			{
				// format args
				self::formatArgs($arg);

				$match = call_user_func_array('\Moorexa\Route::match', $arg);
				
				if (is_string($end))
				{
					self::$closureUsed[$end] = $arg[1];
				}

				return $match;
			}

			self::$lastMemory[] = [$arg[0], $arg[1]];
		}
		elseif (is_callable($arg[0]))
		{
			$meth = $_SERVER['REQUEST_METHOD'];

			self::$lastMemory[] = [$arg[0]];

			if ($meth == 'DELETE')
			{
				call_user_func($arg[0]);
			}
		}

		return new self;
	}

	// PUT request
	public static function put()
	{
		$arg = func_get_args();

		$end = end($arg);

		if (is_string($arg[0]))
		{
			$is = self::method('put');

			if ($is->method)
			{
				// format args
				self::formatArgs($arg);

				$match = call_user_func_array('\Moorexa\Route::match', $arg);
				
				if (is_string($end))
				{
					self::$closureUsed[$end] = $arg[1];
				}

				return $match;
			}

			self::$lastMemory[] = [$arg[0], $arg[1]];
		}
		elseif (is_callable($arg[0]))
		{
			$meth = $_SERVER['REQUEST_METHOD'];

			self::$lastMemory[] = [$arg[0]];

			if ($meth == 'PUT')
			{
				call_user_func($arg[0]);
			}
		}

		return new self;
	}

	// PATCH request
	public static function patch()
	{
		$arg = func_get_args();

		$end = end($arg);

		if (is_string($arg[0]))
		{
			$is = self::method('patch');

			if ($is->method)
			{
				// format args
				self::formatArgs($arg);

				$match = call_user_func_array('\Moorexa\Route::match', $arg);
				
				if (is_string($end))
				{
					self::$closureUsed[$end] = $arg[1];
				}

				return $match;
			}

			self::$lastMemory[] = [$arg[0], $arg[1]];
		}
		elseif (is_callable($arg[0]))
		{
			$meth = $_SERVER['REQUEST_METHOD'];

			self::$lastMemory[] = [$arg[0]];

			if ($meth == 'PATCH')
			{
				call_user_func($arg[0]);
			}
		}

		return new self;
	}

	private static function method($meth)
	{
		$post = $_POST;
		$useToken = false;
		$request = $_SERVER['REQUEST_METHOD'];
		$method = false;


		if (strtoupper($meth) == 'GET' && $request == 'GET')
		{
			$method = true;
		}
		else
		{
			if (strtoupper($meth) == $request)
			{
				$data = count($_POST) > 0 ? $_POST : file_get_contents('php://input');

				if (is_string($data))
				{
					$_POST = parse_query($data);
				}

				$post = $_POST;

				if (count($post) > 0)
				{
					if (isset($post['method.useToken']) || isset($post['method_useToken']))
					{
						$useToken = true;
						unset($_POST['method.useToken'], $_POST['method_useToken']);
					}

					$method = true;
				}
			}
			else
			{
				if (count($post) > 0)
				{
					if (isset($post['method']) || isset($post['method.useToken']) || isset($post['method_useToken']))
					{
						if (isset($post['method']) && $post['method'] == $meth)
						{	
							$method = true;

							unset($_POST['method']);
						}
						elseif (isset($post['method.useToken']) || isset($post['method_useToken']))
						{
							$method = !isset($post['method.useToken']) ? $post['method_useToken'] : $post['method.useToken'];

							if ($method == $meth)
							{
								$method = true;
							}

							$useToken = true;
							unset($_POST['method.useToken'], $_POST['method_useToken']);
						}
					}
				}
			}
		}
		

		return (object) ['method' => $method, 'useToken' => $useToken];
	}

	public static function domain(string $domain, \closure $callback)
	{
		if (isset($_SERVER['HTTP_HOST']))
		{
			// call closure
			$callClosure = false;

			// get server name
			$serverName = $_SERVER['SERVER_NAME'];

			if ($serverName == $domain)
			{
				$callClosure = true;
			}

			// get host
			$host = $_SERVER['HTTP_HOST'];

			if ($host == $domain)
			{
				$callClosure = true;
			}

			// get port
			$port = $_SERVER['REMOTE_PORT'];

			if ($host . ':' . $port == $domain)
			{
				$callClosure = true;
			}


			// quote domain
			$domain = str_replace('*', '(.*?)', $domain);
			$domain = str_replace('/', '\/', $domain);


			if (preg_match("/($domain)/", $host . $_SERVER['REQUEST_URI']))
			{
				$callClosure = true;	
			}	

			// call closure 
			if ($callClosure)
			{
				call_user_func($callback);
			}
		}
	}


	public static function redir($path)
	{
		ob_start();
		header('location: '.url($path));
	}

	public static function __match($url)
	{
		Route::$requestUri = $url;

		include_once PATH_TO_LIB . 'routes_func.php';

		include PATH_TO_SERVICES . 'routes.php';

		// apply route method to boot manager
		BootMgr::method('System@route', null);

		// check if bootmode can continue
		if (BootMgr::$BOOTMODE['System@route'] == CAN_CONTINUE)
		{
			$url = implode('/', Route::$requestMatch);

			$continue = Middleware::http()->getRegister($url);
			
			if ($continue)
			{
				return count(Route::$requestMatch) > 0 && Route::$requestMatch[0] != "" ? Route::$requestMatch : null;
			}

			$continue = null;
		}

		die();
	}

	public static function getUrl()
	{
		return Route::$requestUri;
	}

	public function channel()
	{
		if (self::$controllerFound !== false)
		{
			$controller = self::$controllerFound;
			$channels = func_get_args();

			$request = Route::$request;

			foreach($channels as $i => $channel)
			{
				if (in_array($channel, $request))
				{
					$this->routerRequest = [self::$controllerFound, $channel];
				}
			}
		}

		return $this;
	}

	public function from(string $package)
	{
		if ($this->routerRequest !== null && $this->routerRequest !== false)
		{
			if (substr($package, 0, 2) == './')
			{
				$package = HOME . 'pages/' . substr($package, 2);
			}
			else
			{
				$package = HOME . 'pages/' . self::$controllerFound . '/' . $package;
			}

			if (is_dir($package))
			{
				$class = basename($package);
				$indexFile = $package . '/index.php';

				if (file_exists($indexFile))
				{
					$require = $package . '/require.php';

					$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
					$method = isset($this->routerRequest[1]) ? $this->routerRequest[1] : null;
					
					$request = strtolower($requestMethod).''.ucfirst($method);

					$allrequests = [
						'post'.ucfirst($method),
						'get'.ucfirst($method),
						'put'.ucfirst($method),
						'delete'.ucfirst($method)
					];

					$requestData = null;

					// check if require file exists
					if (file_exists($require))
					{
						$req = include_once($require);

						if (is_array($req))
						{
							foreach($req as $i => $path)
							{
								$fullpath = $package . '/' . $path;
								if (file_exists($fullpath))
								{
									include_once($fullpath);

									if (class_exists($i))
									{
										$cls = new $i;

										if (method_exists($cls, $request))
										{
											$requestData = $cls->{$request}();
										}

										$cls = null;
									}
								}
							}
						}
					}

					include_once($indexFile);

					if (class_exists($class))
					{
						$chs = $package . '/chs';

						$package = new $class;
						$package->{$request} = $requestData;

						foreach ($allrequests as $i => $req)
						{
							if ($req != $request)
							{
								$package->{$req} = false;
							}
						}

						$requestData = null;

						$start = array_key($method, Bootloader::$getUrl) + 1;

						Bootloader::RedirectedData($package);

						if (!is_null($method))
						{
							if (method_exists($package, $method))
							{
								$this->packageFound = true;
								$args = array_slice(Bootloader::$getUrl, $start);
								
								$view = call_user_func_array([$package, $method], $args);

								$packageArr = toArray($package);
								$packageArr['view'] = $view;
								$packageArr['request'] = $method;

								$this->packageArr = $packageArr;

								if (is_dir($chs))
								{
									Route::$channelChs = $chs;
								}

							}
							else
							{
								// method not found
							}
						}
						else
						{
							// no request sent.
						}
						
						$package = null;
						$method = null;
					}
					else
					{
						// class doesn't exists
					}
				}
				else
				{
					// index file not found
				}
			}
			else
			{
				// dir not found
			}

		}

		return $this;
	}

	public function mapto(string $map)
	{
		if ($this->packageFound !== null && $this->packageFound !== false)
		{
			$mapArray = explode('/', $map);
			Route::$requestMatch = $mapArray;

			$package = $this->packageArr;

			self::$channelData[$map] = $package;

			$this->packageArr = null;
			$mapArray = null;
		}

		return $this;
	}

	// handle web requests
	public static function web($callback, &$listener=null)
	{
		static $called;

		if ($called == null && \ApiManager::$listener == null)
		{
			$ls = new \ApiManager();
			// call listen
			$ls->listen();
			$called = $ls;
		}

		$listener = \ApiManager::$listener;

		if ($listener === false && is_callable($callback))
		{
			// good
			call_user_func($callback, self::$requestUri);
		}
	}

	// handle api requests
	public static function api($callback)
	{
		self::web(null, $serving);

		if ($serving === true)
		{
			$storage = new class {
				public function __set($key, $val)
				{
					\ApiManager::$storage[$key] = $val;
				}
			};
			// good
			call_user_func($callback, self::$requestUri, $storage);
		}
	}

	// track page routes
	public static function track()
	{
		$url = Bootloader::$helper['location.url'];
		$cont = Bootloader::$helper['active_c'];

		if (strtolower($url[0]) == strtolower($cont))
		{
			$url = array_splice($url, 1);
		}

		$url = implode('/', $url);
		$tracker = [];
		session()->has('link.tracker', $tracker);

		if (!isset($tracker[$url]))
		{
			$tracker[$url] = [
				'controller' => $cont,
				'link' => $url
			];
		}
		else
		{
			$keys = array_keys($tracker);
			$first = $keys[0];
			if ($first == $url)
			{
				$tracker = [];
				$tracker[$url] = [
					'controller' => $cont,
					'link' => $url
				];
			}
		}

		// get last 2;
		$tracker = array_splice($tracker, -2, 2);
		session()->set('link.tracker', $tracker);
	}

	// get previous page
	public static function previous()
	{
		if (session()->has('link.tracker', $tracker))
		{
			if (count($tracker) > 1)
			{
				$keys = array_keys($tracker);
				$firstKey = $keys[0];
				return (object) $tracker[$firstKey];	
			}
		}

		return (object) ['controller' => Bootloader::$helper['active_c'], 'link' => '/'];
	}
}
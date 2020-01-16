<?php 

use Moorexa\Bootloader;

/**
 * @package Moorexa Authentication
 * @author  Ifeanyi Amadi <amadiify.com>
 * @version 0.0.1
 */

class Authenticate
{
	// routes used
	public $routes = [];
	private $failed = [];
	private $paths = [];
	private $instances = [];
	public static $controller = null;
	private static $allhandlers = [];
	protected $defaultTimeZone = null;
	private $disallow = false;

	public function __construct()
	{
		$this->defaultTimeZone = is_null($this->defaultTimeZone) ? date_default_timezone_get() : $this->defaultTimeZone;
	}

	public function redir($path, $other = null)
	{
		$app = new Moorexa\View();
		$app->renderNew($path, $other);
	}

	// check current browser
	public function browser(&$agent = null)
	{
		$agent = $_SERVER['HTTP_USER_AGENT'];

		if (!isset($_SESSION['AUTH_USER']))
		{
			session('AUTH_USER', encrypt($agent . url() . get_query()));

			return true;
		}
		else
		{
			$auth = session('AUTH_USER');

			if ($auth === encrypt($agent . url() . get_query()))
			{
				session('AUTH_USER',false);

				return true;
			}
			else
			{
				session('AUTH_USER', encrypt($agent . url() . get_query()));

				return false;
			}
		}

		return false;
	}

	// load 
	public function load()
	{
		// all paths
		$paths = func_get_args();

		if (count($paths) > 0)
		{
			array_map(function($e){
				if (is_dir($e))
				{
					$this->paths[] = $e;
				}
			}, $paths);
		}
		return $this;
	}

	// use 
	public function apply()
	{
		if (count($this->paths) == 0)
		{
			$this->paths[] = PATH_TO_AUTHENTICATION;
		}

		if (count($this->paths) > 0)
		{
			$argument = func_get_args();
			$data = isset($argument[0]) ? $argument[0] : null;
			$other = array_splice($argument, 1);

			$arg = [];

			if (count($other) > 0)
			{
				foreach ($other as $i => $d)
				{
					$arg[$i] = &$other[$i];
				}
			}

			$other =& $arg;

			$args = [];

			if (!is_null($data))
			{
				// convert data to array
				$data = explode('@', $data);
				$data[1] = isset($data[1]) ? $data[1] : null;
				// get handler and method
				list($class, $meth) = $data;

				// $dataReceived
				$dataReceived = [];

				// manage handler
				foreach($this->paths as $k => $dir)
				{	
					// get file path
					$file = deepScan($dir, [$class.'.php', $class.'.auth.php']);

					if (strlen($file) > 0 && is_file($file))
					{
						// include auth file
						include_once($file);

						$allow = null;
						$controller = null;
						$allowAllExcept = null;


						$class = basename($class);

						if (strpos($class, '.auth') === false)
						{
							$class .= '.auth';
						}

						$class = ucwords(str_replace(".", ' ', $class));
						$class = preg_replace('/[\s]{1,}/', '', $class);
						
						if (class_exists($class))
						{
							if (!is_null($meth))
							{
								if (!isset(self::$allhandlers[$class]))
								{
									// create instance
									Bootloader::$instance->getParameters($class, '__construct', $const, $other);

									$ref = new \ReflectionClass($class);
											
									$instance = $ref->newInstanceArgs($const);

									self::$allhandlers[$class] = $instance;
								}
								else
								{
									$instance =& self::$allhandlers[$class];
								}

								if (property_exists($instance, 'allow'))
								{
									$allow = $instance->allow;
								}

								if (property_exists($instance, 'allowAllExcept'))
								{
									$allowAllExcept = $instance->allowAllExcept;
								}

								$continue = true;

								if (!is_null($controller))
								{
									$all = explode(',', $controller);
									foreach($all as $i => $pa)
									{
										if (trim($pa) == trim($this->routes[0]))
										{
											$continue = true;
										}
										else
										{
											$continue = false;
										}
									}
								}

								if (!is_null($allowAllExcept))
								{
									if (is_array($allowAllExcept))
									{
										$url = $this->routes;
										foreach ($allowAllExcept as $i => $exe)
										{
											$len = count(explode('/', $exe));
											$extr = array_splice($url, 0, $len);
											$extr = implode('/', $extr);
											$url = $this->routes;

											$quote = preg_quote($extr, '/');

											if (preg_match("/($quote)/i", $exe))
											{
												$continue = true;
												break;
											}
											else
											{
												$continue = false;
											}
										}
									}
								}

								if (!is_null($allow))
								{
									if (is_array($allow))
									{
										$url = $this->routes;

										foreach ($allow as $i => $exe)
										{
											$len = count(explode('/', $exe));
											$extr = array_splice($url, 0, $len);
											$extr = implode('/', $extr);

											$url = $this->routes;

											$quote = preg_quote($extr, '/');

											if (preg_match("/($quote)/i", $exe))
											{
												$continue = false;
												break;
											}
										}
									}
								}

								if ($continue)
								{
									if (method_exists($instance, $meth))
									{
										Bootloader::$instance->getParameters($instance, $meth, $const, $other);

										// make reference
										$args = [];

										foreach ($const as $index => $value)
										{
											if ($value != null)
											{
												$args[] = &$const[$index];
											}
										}

										$return = call_user_func_array([$instance, $meth], $args);

										// update
										self::$allhandlers[$class] = $instance;

										$dataReceived[$meth] = $return;
									}
									else
									{
										$this->failed[] = "Method '$meth' doesn't exists in Class '$class'";
									}
								}
							}
							else
							{			
								$ref = new \ReflectionClass($class);

								if ($ref->isInstantiable())
								{
									// create instance
									Bootloader::$instance->getParameters($class, '__construct', $const, $other);

									$instance = $ref->newInstanceArgs($const);


									if (property_exists($instance, 'allow'))
									{
										$allow = $instance->allow;
									}

									if (property_exists($instance, 'allowAllExcept'))
									{
										$allowAllExcept = $instance->allowAllExcept;
									}

									$continue = true;

									if (!is_null($controller))
									{
										$all = explode(',', $controller);
										foreach($all as $i => $pa)
										{
											if (trim($pa) == trim($this->routes[0]))
											{
												$continue = true;
											}
											else
											{
												$continue = false;
											}
										}
									}

									if (!is_null($allowAllExcept))
									{
										if (is_array($allowAllExcept))
										{
											$url = $this->routes;
											foreach ($allowAllExcept as $i => $exe)
											{
												$len = count(explode('/', $exe));
												$extr = array_splice($url, 0, $len);
												$extr = implode('/', $extr);
												$url = $this->routes;

												$quote = preg_quote($extr, '/');

												if (preg_match("/($quote)/i", $exe))
												{
													$continue = true;
													break;
												}
												else
												{
													$continue = false;
												}
											}
										}
									}

									if (!is_null($allow))
									{
										if (is_array($allow))
										{
											$url = $this->routes;

											foreach ($allow as $i => $exe)
											{
												$len = count(explode('/', $exe));
												$extr = array_splice($url, 0, $len);
												$extr = implode('/', $extr);

												$url = $this->routes;

												$quote = preg_quote($extr, '/');

												if (preg_match("/($quote)/i", $exe))
												{
													$continue = false;
													break;
												}
											}
										}
									}

									if ($continue)
									{
										self::$allhandlers[$class] = $instance;
										return $instance;
									}
									else
									{
										$this->disallow = true;
									}

									return $this;
									
								}
								
							}
						}
						else
						{
							$this->failed[] = "Class '{$class}' doesn't exists in $file";
						}
						
					}
					else
					{
						$this->failed[] = 'Authentication handler '.$dir.$class.' not found!';
					}

					// reset paths
					$this->paths = [];

				}

				if (count($dataReceived) > 0)
				{
					foreach ($dataReceived as $meth => $sent)
					{
						if (!is_null($sent))
						{
							return $sent;
						}
					}
				}
			}
		}

		if (env('bootstrap','debugMode') == 'on')
		{
			if (count($this->failed) > 0)
			{
				throw new \Exception(implode("\n", $this->failed));
			}

			return false;
		}

		return $this;
	}

	public static function handler($name)
	{
		if (isset(self::$allhandlers[$name]))
		{
			return self::$allhandlers[$name];
		}
		else
		{
			$paths = PATH_TO_AUTHENTICATION;
			$file = deepScan($paths, [$name.'.php', $name.'.auth.php']);

			$fname = $name;

			if (strlen($file) > 20)
			{
				include_once($file);
				$name = ucwords(str_replace('.', ' ', $name));
				$name = str_replace(' ', '', $name);

				if (!preg_match('/(auth)/i', $name))
				{
					$name .= 'Auth';
				}

				$args = func_get_args();

				$other = array_splice($args, 1);

				if (class_exists($name))
				{
					$const = [];
					Bootloader::$instance->getParameters($name, '__construct', $const, $other);

					$ref = new \ReflectionClass($name);

					$instance = $ref->newInstanceArgs($const);

					self::$allhandlers[$fname] = $instance;

					return $instance;
				}
			}
		}

		throw new \Exception("Authentication Handler '$name' not loaded.");
	}

	public function __call($meth, $args)
	{
		if ($this->disallow)
		{
			return false;
		}
	}
	
}
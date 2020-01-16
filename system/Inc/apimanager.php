<?php

use Moorexa\Bootloader;
use Moorexa\DB;
use Moorexa\Event;
use WekiWork\Http;

/**
 * @package Moorexa API Manager
 * @author Ifeanyi Amadi
 * @version 0.0.1
 */

class ApiManager
{
	public static $listener;
	public static $config;
	public static $instance;
	private $json;
	public static $services = "";
	public static $requestApi = "";
	public static $json_sent = false;
	public static $serving = false;
	public static $csmanager = [];
	public static $tables = [];
	public $provider = null;
	public static $handler = null;
	public static $getUrl = [];
	public static $providerWaiting = [
		'boot' => false,
		'willEnter' => false
	];
	public static $storage = [];
	public static $activeTable = null;
	private static $pathScanned = false;

	public function __construct($config = [])
	{
		ApiManager::$config = $config;
	}

	public function listen()
	{
		// listen for api request
		if (function_exists('getallheaders') && ApiManager::$listener === null)
		{
			// include http class
			include_once 'lab/Http.php';

			$headers = Http::getHeaders();

			if (count($headers) > 0)
			{
				// load config
				if (file_exists(HOME . 'api/config.xml'))
				{
					$config = simplexml_load_file(HOME . 'api/config.xml');

					if ($config !== false)
					{
						$arr = toArray($config);

						if (isset($arr['request']))
						{
							if (isset($arr['request']['identifier']))
							{
								$arr = $arr['request']['identifier'];
								
								if (is_array($arr))
								{
									$success = 0;

									// convert keys to lowercase
									array_each(function($val, $key) use (&$headers){
										// remove
										unset($headers[$key]);
										// add again
										$headers[strtolower($key)] = $val;
									}, $headers);

									$failed = [];

									if (isset($arr[0]))
									{
										array_map(function($a) use (&$headers, &$failed, &$success){
											if (isset($a['header']))
											{
												$header = trim(strtolower($a['header']));
												if (isset($headers[$header]))
												{
													// get value
													$valSent = strtolower(trim($headers[$header]));
													// default value
													$valStored = strtolower(trim($a['value']));

													// match
													if ($valSent == $valStored)
													{
														// good
														$success++;
													}
													else
													{
														$failed[$header] = 'Requirement failed. Incorrect value for \''.$header.'\'';
													}
												}
												else
												{
													$failed[$header] = 'Request header missing';
												}
											}
										}, $arr);
									}
									else
									{
										$header = trim(strtolower($arr['header']));

										if (isset($headers[$header]))
										{
											$valueSent = strtolower(trim($headers[$header]));
											$valStored = strtolower(trim($arr['value']));

											if ($valueSent == $valStored)
											{
												$success++;
											}
											else
											{
												$failed[$header] = 'Requirement failed. Incorrect value for \''.$header.'\'';
											}
										}
										else
										{
											$failed[$header] = 'Request header missing';
										}
									}

									if ($success > 0)
									{
										// check if $success is less than headers
										if ($success < count($arr))
										{
											// build trace
											$trace = [];

											foreach ($failed as $key => $error)
											{
												$trace[] = ['header' => $key, 'error' => $error];
											}

											// show errors
											$this->status('Error')->cause('Request headers')->trace($trace);
										}

										ApiManager::$listener = true;
									}
									else
									{
										ApiManager::$listener = false;
									}
								}
							}
						}
						$config = null;
						$arr = null;
					}
				}
				else
				{
					Event::emit('api.error', "Config.xml not found");
				}
			}
		}
	}

	private static function isloggedin()
	{
		if (isset($_COOKIE['api_token']))
		{
			$header = Http::getHeaders();
			$token_cookie = $_COOKIE['api_token'];
			$token_header = isset($header['api_token']) ? $header['api_token'] : false;


			if ($token_header)
			{
				if ($token_header == $token_cookie)
				{
					if (isset($config['authentication-table']))
					{
						$table = $config['authentication-table'];
						$check = DB::table($table)->get('token = ?')->bind($token);

						if ($check->rows > 0)
						{
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	public static function requestPermission()
	{
		$config = ApiManager::$config;
		$api = new ApiManager;
		\MoorexaErrorContainer::$apirunning = true;

		if ($config['authentication'] !== NONE)
		{
			if ($config['api-db'] === true)
			{
				// use current db
				DB::serve();
			}
			else
			{
				DB::apply($config['api-db']);
			}

			$auth = $config['authentication'];

			ApiManager::$config = $config;

			if (is_array($auth))
			{

			}
			else
			{
				if (is_string($auth) && $auth == md5('default'))
				{
					if(isset($config['authentication-table']) && isset($config['columns']))
					{
						$header = Http::getHeaders();

						if (!self::isloggedin())
						{
							if (isset($header['Authorization']))
							{
								$auth = utf8_decode($header['Authorization']);

								if (strpos($auth, 'Basic') >= 0)
								{
									$auth = trim(str_replace("Basic", '', $auth ));

									$auth = base64_decode( $auth );
									$auth = explode( ":", $auth);

									if (isset($auth[0]) && isset($auth[1]))
									{
										$user = $auth[0];
										$key = $auth[1];
										
										$table = $config['authentication-table'];
										$where = [];

										foreach ($config['columns'] as $i => $col)
										{
											if (isset($auth[$i]) && !empty($auth[$i]))
											{
												if (isset($config['encrypt']) && isset($config['encrypt'][$col]))
												{
													$func = $config['encrypt'][$col];
													$data = call_user_func($func, $auth[$i]);

													$where[$col] = $data;
												}
												else
												{
													$where[$col] = $auth[$i];	
												}
												
											}
										}


										if (count($where) == count($config['columns']))
										{

											if (!Table::exists($table))
											{
												Table::create($table, function($schema) use ($config){
													$id = $table .'id';
													$columns = $config['columns'];

													$schema->{$id}->increment();

													foreach($columns as $i => $col)
													{
														$schema->{$col}->string();
													}

													$schema->token->string(300);
												});
											}

											$check = DB::table($table, 'get', $where);

											$userAgent = sha1(encrypt($header['User-Agent']));

											if ($check->num_rows > 0)
											{
												if (isset($config['revalidate']) && trim(strtolower($config['revalidate'])) == 'token')
												{

													$token = encrypt(implode(',', $where));

													$userAgent = sha1(encrypt($header['User-Agent']) . $token); 

													$update = [];
													$update['token'] = $userAgent;

													$updated = DB::table($table, 'update', $where);

													if ($update)
													{
														setcookie('api_token', $userAgent, time() * (isset($config['expire']) ? abs($config['expire']) : 60 * 2));
													}

													header('api_token: '.$userAgent);
												}
												
												return true;
												
											}
											else
											{
												$api->status('Authentication failed')
												->reason('Invalid account! '.implode(' or ', $config['columns']).' not valid.')->ok();
												return 202;
											}
										}
										else
										{
											$sub = count($config['columns']) - count($where);

											$api->status('Authentication failed')
											->reason($sub.' column '. (($sub > 1) ? 's are': 'is').' missing Hint *('.implode(',', $config['columns']).'). Please check Api authentication requierment.');

											return 202;
										}
									}
									else
									{
										$api->status('Authentication failed')
										->reason('Username or password not provided.');

										return 202;
									}

								}
								else
								{
									$api->status("Authentication failed")
									->reason('Gateway only supports Basic Auth');

									return 202;
								}
							}
							else
							{
								$api->status('Request failed')->
								reason('Authorization header not sent with this request.');

								return 202;
							}
						}
						else
						{
							header('api_token: '.$_COOKIE['api_token']);
						}

						return true;
					}
					else
					{
						// failed;
						$api->status('Authentication failed.')
						->reason('Authentication table and columns not set.');

						return 202;
					}
				}
				else
				{
					return true;
				}
			}
		}
		else
		{
			return true;
		}

		return false;	
	}

	public static function openGateway($url)
	{
		$scheme = $_SERVER['REQUEST_METHOD'];

		$method = strtolower($scheme).'Method';

		$api = new ApiManager;

		if (method_exists($api, $method))
		{
			$table = array_shift($url);
			$api->{$method}($table, $url);
		}
	}

	// serve api request
	public static function serve($sys)
	{
		// set default content type
		header('Content-Type: Application/json');

		// allow control orgin for all. 
		header('Access-Control-Allow-Origin: *');

		// get url
		$url = $sys->system->refUrl;

		// get handler
		list($handler) = $sys->system->cleanUrl($url);

		// get view
		$view = isset($url[1]) ? $url[1] : null;
		$view = !is_null($view) ? $sys->system->cleanUrl($url)[0] : null;

		// create a new url, starting from index 1
		$getUrl = $sys->system->cleanUrl(array_splice($url, 1));

		// get api path
		$apiPath = HOME .'api/'. ucfirst($handler) . '/main.php';

		// set current controller to api handler.
		BootLoader::$helper['get_controller'] = $handler;
		BootLoader::$helper['c_controller'] = $handler;

		// not necessar, but we check if view has a question mark
		if (is_string($view) && strpos($view, '?') > 0)
		{
			$view = substr($view, 0, strpos($view, '?'));
		}

		// get request method
		$requestmethod = strtolower($_SERVER['REQUEST_METHOD']);

		// build class Method
		$method = $requestmethod . ucfirst($handler);

		self::$serving = true;
		self::$requestApi = $handler;

		$flip = array_flip(get_declared_classes());
		$id = isset($flip[ucfirst($handler)]) ? $flip[ucfirst($handler)] : null;

		if ($id == null)
		{
			// set handler
			self::$handler = ucfirst($handler);
			
			// include middlewares
			include_once HOME . 'kernel/middleware.php';

			// include autoloader
			include_once HOME . 'api/autoloader.php';

			//read api handler
			$handler = ucfirst($handler);

			// handler not found
			$handlerNotFound = true;

			// check if file exists.
			if (file_exists($apiPath))
			{
				// get bootloader instance
				$instance = Moorexa\Bootloader::$instance;

				// define path
				self::definePaths();

				// include handler
				include_once $apiPath;

				// check if class exists with handler name
				if (class_exists($handler))
				{
					// reset handler
					$handlerNotFound = false;

					// call constructor
					$const = [];

					// get constructor params
					$instance->getParameters($handler, '__construct', $const, $getUrl);
					$ref = new \ReflectionClass($handler);
					
					// create an instance of api handler.
					$class = $ref->newInstanceArgs($const);

					// build request for view
					$viewRequest = $method;

					// get table
					if ($ref->hasProperty('table'))
					{
						self::$activeTable = $class->table;
					}
					else
					{
						self::$activeTable = $handler;
					}

					if (is_string($view))
					{
						// we create a temp variable to check of view existance
						$temp = $requestmethod.ucfirst($view);
						// now we check
						if ($ref->hasMethod($temp))
						{
							$viewRequest = $temp;
							// shift cursor 1 step forward.
							$getUrl = array_splice($getUrl, 1);
						}
						$temp = null;
					}

					// set get url for model
					self::$getUrl = $sys->system->cleanUrl($getUrl);

					// so we continue.
					// make assets class avialiable
					$class->assets = new Moorexa\Assets();

					// load vars from middleware
					$active = Moorexa\Middleware::$active;

					if (count($active) > 0)
					{
						foreach ($active as $a => $bus)
						{
							$class->{$a} = $bus;
						}
					}

					// set active table for queries
					Moorexa\DB::$activeTable = $class->table;

					// set active db
					if ($class->switchdb !== null)
					{
						Moorexa\DB::apply($class->switchdb);
					}

					// set current class for external classes.
					Moorexa\BootLoader::$currentClass = $class;

					$continue = true; // this would be false if csrf verification fails.
					$error = ''; // record error occured during csrf verification

					// verify CSRF TOKEN for requests with post data
					if (count($_POST) > 0)
					{
						if (Moorexa\View::$packagerJson['activate_csrf_token'] === true)
						{
							if (isset($sys->csrf_verify))
							{
								// verification failed. 
								if ($sys->csrf_verify === false)
								{
									$continue = false;
								}
							}
						}
					}

					if ($continue)
					{
						$handler = ucfirst($handler);

						// load boot
						self::loadProviderBoot($handler, $class, $viewRequest, $instance, $getUrl);

						// load middlewares awaiting processing.
						$waiting = Moorexa\Middleware::$waiting;

						// Create closure handler function
						$callfunc = function() use ($class, $getUrl, $viewRequest, $instance)
						{
							$const = [];

							$instance->getParameters($class, $viewRequest, $const, self::$getUrl);
							// request ready
							
							// get method
							preg_match('/([a-z]+?)([A-Z])/', $viewRequest, $match);
							$meth = $match[1];

							// call $methDidEnter
							switch (method_exists($class->provider, $meth.'DidEnter'))
							{
								case true:
									// get params
									$instance->getParameters($class->provider, $meth.'DidEnter', $arg, $const);

									// call method
									call_user_func_array([$class->provider, $meth.'DidEnter'], $arg);
								break;
							}
			
							call_user_func_array([$class, $viewRequest], $const);
					
						};

						// check if method exists
						if (method_exists($class, $viewRequest))
						{
							$pw = (object) self::$providerWaiting;

							if ($pw->boot && $pw->willEnter)
							{
								// check if handler listens for a middleware
								if (isset($waiting[$viewRequest]))
								{
									Middleware::callWaiting($waiting[$viewRequest], $callfunc);
								}
								else
								{
									// just call method
									$callfunc();
								}
							}
						}
						else
						{
							if (ApiManager::$json_sent === false)
							{
								echo json_encode(['error' => 'Action '.$viewRequest.' not found. Please contact api provider.']);
							}
						}
					}
					else
					{
						if (class_exists('\CsrfVerify'))
						{
							$err = \CsrfVerify::$error;

							if ($err != null)
							{
								echo json_encode(['error' => $err]);
							}
						}
					}
				}
			}

			// handler not found
			if (ApiManager::$json_sent === false && $handlerNotFound)
			{
				echo json_encode(['error' => 'Api handler not found']);
			}
		}
		else
		{
			echo json_encode(['error' => 'Class '.$handler.' is reserved by the system and cannot be used.']);
		}

		// #clean up
		$url = null;
		$getUrl = null;
		$view = null;
		$ref = null;
	}

	// define paths
	private static function definePaths()
	{
		if (self::$pathScanned === false)
		{
			$dir = glob(HOME . 'api/*');

			if (is_array($dir))
			{
				// run
				array_map(function($dir){
					if ($dir != '.' && $dir != '..')
					{
						if (is_dir($dir))
						{
							$base = basename($dir);
							$constant = 'PATH_TO_'.$base;

							if (!defined($constant))
							{
								define($constant, $dir . '/', 1);
							}
						}
					}
				}, $dir);

				self::$pathScanned = true;
			}
		}
	}

	// load boot method for controller.
	public static function loadProviderBoot(&$handler, &$class, &$request, &$instance, $arguments)
	{
		// provider path
		$path = HOME . 'api/'. $handler .'/provider.php';

		// include provider
		include_once $path;

		// get class.
		$providerClass = $handler.'Provider';

		// check if class exists.
		if (class_exists($providerClass))
		{
			// load instance without argument.
			$class->provider = new $providerClass;

			//  check if boot method exists
			if ( method_exists($class->provider, 'boot') )
			{
				$next = new class
				{
					public function __invoke()
					{
						ApiManager::$providerWaiting['boot'] = true;
					}
				};

				$copy = $arguments;
				array_unshift($copy, $instance);
				array_unshift($copy, $next);

				// get arguments.
				Bootloader::$instance->getParameters($class->provider, 'boot', $const, $copy);
				
				// call boot method
				call_user_func_array([$class->provider, 'boot'], $const);
			}

			// load request provider.
			$path = HOME . 'api/'. $handler . '/Providers/'. $request . '.php';

			// the check.
			switch (file_exists($path))
			{
				// skip loading onInit method from controller provider.
				case true:
					// load view provider.
					include_once $path;
					
					// call viewWillLoad method if class exists.
					$providerClass = $handler.ucfirst($request).'Provider';

					// check if class exists
					if (class_exists ($providerClass))
					{
						// load reflection class so we can inspect.
						$ref = new \ReflectionClass($providerClass);

						// check if constructor exists
						if ($ref->hasMethod('__construct'))
						{
							// get instance argument of constructor
							Bootloader::$instance->getParameters($providerClass, '__construct', $const, $arguments);
							$class->provider = $ref->newInstanceArgs($const);
						}
						else
						{
							$class->provider = new $providerClass;
						}

						// get method
						preg_match('/([a-z]+?)([A-Z])/', $request, $match);
						$meth = $match[1];

						// load $methWillEnter if exists
						if (method_exists($class->provider, $meth.'WillEnter'))
						{
							$next = new class
							{
								public function __invoke()
								{
									ApiManager::$providerWaiting['willEnter'] = true;
								}
							};

							array_unshift($arguments, $next);

							// get parameters
							Bootloader::$instance->getParameters($class->provider, $meth.'WillEnter', $const, $arguments);
							// call method
							call_user_func_array([$class->provider, $meth.'WillEnter'], $const);
						}
						
						// clean up
						$ref = null;
					}
					else
					{
						self::$providerWaiting['willEnter'] = true;
					}

					// clean up
					$providerClass = null;

				break;

				// load onInit method in controller provider.
				case false:
					// check if provider can emit init event for view
					$method = 'on'.ucfirst($request).'Init';
					
					// check 
					if ( method_exists($class->provider, $method) )
					{
						$next = new class
						{
							public function __invoke()
							{
								ApiManager::$providerWaiting['willEnter'] = true;
							}
						};

						array_unshift($arguments, $next);

						// get arguments.
						Bootloader::$instance->getParameters($class->provider, $method, $const, $arguments);
						
						// call method
						call_user_func_array([$class->provider, $method], $const);
					}
					else
					{
						self::$providerWaiting['willEnter'] = true;
					}

					// clean up
					$method = null;
				break;
			}

		}
		
		// clean up
		$providerClass = null;
	}

	public function routeTo($path)
	{
		header_remove();
		header('content-type: text/html');

		if(isset($_SERVER['SERVER_TYPE']))
		{
			$_SERVER['REQUEST_QUERY_STRING'] = $path;
		}
		else
		{
			$_GET['__app_request__'] = $path;	
		}
		
		
		Bootloader::$helper['isonline']  = Bootloader::$instance->__isonline();
		Bootloader::$helper['staticurl'] = Bootloader::$instance->staticurl;

		Bootloader::$instance->keep_alive();
		
	}

	public function __call($meth, $pa)
	{
		if ($meth != 'get' && $meth != 'delete' && $meth != 'update' && $meth != 'insert')
		{
			$add = $meth;
			$val = $pa[0];

			if ($meth == 'onlyif')
			{
				if ($val)
				{
					$data = $pa[1];
					$keys = array_keys($data);
					$vals = array_values($data);

					if (count($keys) == 1)
					{
						$add = $keys[0];
						$val = $vals[0];
					}
					else
					{
						foreach ($data as $key => $valz)
						{
							$this->json[$key] = $valz;
						}
					}
				}
				else
				{
					if (count($pa) == 3)
					{
						$last = end($pa);

						if (is_string($last))
						{
							if (isset($pa[1]) && is_array($pa[1]))
							{
								// get keys
								$keys = array_keys($pa[1]);
								$add = $keys[0];
							}
							else
							{
								$add = 'message';
							}

							$val = $last;
						}
						else
						{
							if (is_array($last) || is_object($last))
							{
								foreach ($last as $key => $valz)
								{
									$this->json[$key] = $valz;
								}
							}
							else
							{
								$add = '';
							}
						}
					}
					else
					{
						$add = '';
					}
				}
			}

			if ($this->__lastMethod($meth) == $meth)
			{
				if ($add != '')
				{
					$this->json[$add] = $val;
				}

				$this->ok;
			}
			else
			{
				if ($add != '')
				{
					$this->json[$add] = $val;
				}
			}

			return $this;
		}
		else
		{
			$table = DB::table(self::$activeTable);

			return call_user_func_array([$table, $meth], $pa)->send();
		}
	}

	public function __get($name)
	{
		if ($name == 'ok' && !ApiManager::$json_sent)
		{
			echo json_encode($this->json);

			ApiManager::$json_sent = true;
		}
		elseif (isset(self::$tables[$name]))
		{
			return self::$tables[$name];
		}
		elseif (isset(self::$storage[$name]))
		{
			return self::$storage[$name];
		}
		elseif ($name == 'table' || $name == 'switchdb')
		{
			// do noting
			return null;
		}
		else
		{
			self::$tables[$name] = DB::table($name);
			// use a copy
			$copy = self::$tables[$name];
			return $copy;
		}
	}

	// get last method chain
	private function __lastMethod($m)
	{
		 $trace = debug_backtrace()[1];
		 $line = $trace['line'];
		 $file = $trace['file'];
		 $trace = null;
		 $getFile = file($file);
		 $file = null;
		 $getLine = trim($getFile[$line-1]);
		 $line = null;
		 $getFile = null;
		 $calledin = 0;
 
		 preg_match_all("/($m)/", $getLine, $cnt);
		 $length = 0;
		 
		 if (count($cnt[0]) > 1)
		 {
			 $calledin += 1;
			 $length = count($cnt[0]);
		 }
 
 
		 if ($length == 0)
		 {
			 $split = preg_split("/(->)($m)/", $getLine);
			 $getLine = null;
			
			 if (isset($split[1]))
			 {
				if (!preg_match('/[)](->)(\S)/', $split[1]) && preg_match('/[;]$/', $split[1]))
				{
					$split = null;
					// last method called.
				
					return $m;
				}
			 }
		 }
		 else
		 {
			 if ($calledin == $length)
			 {
				 $split = preg_split("/(->)($m)/", $getLine);
				 $getLine = null;
 
				 if (!preg_match('/[)](->)(\S)/', $split[$length]) && preg_match('/[;]$/', $split[$length]))
				 {
					 $split = null;
					 $calledin = 0;
					 $length = 0;
					 // last method called.
				 
					 return $m;
				 }
			 }
		 }
 
		 return null;
	}

	public function json($array)
	{
		if (!is_array($array))
		{
			$array = ['message' => $array];
		}

		echo json_encode($array, JSON_PRETTY_PRINT);
	}

	public function services($name, $opt = null)
	{
		if (is_null($opt))
		{
			if ($name === KILL)
			{
				ApiManager::$services = KILL;
			}	
		}
		else
		{
			if ($name === KILL && !is_null($opt))
			{
				ApiManager::$services = $opt;	
			}
			else
			{
				ApiManager::$services = $name;
			}
		}

		if (ApiManager::$requestApi == $opt)
		{
			return false;
		}

		return true;
		
	}

	public function http_response($name, $value = null)
	{
		if (is_array($name))
		{
			foreach ($name as $key => $val)
			{
				header("{$key}:{$val}");
			}
		}
		else
		{
			header("{$name}:{$value}");
		}
	}
}
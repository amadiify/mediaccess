<?php

namespace Moorexa;

use ApiManager;
use utility\Classes\BootMgr\Manager as BootMgr;

/**
 * Bootloader class
 *
 * @package Moorexa Bootloader Application back-bone.
 * @author  Amadi Ifeanyi <www.wekiwork.com> 
 **/


class Bootloader extends DatabaseHandler
{
	// Memory box
	private $_memory_box = [];

	// Helper
	public static $helper;

	// Current page
	private static $current_page;

	// Current Controller
	public static $currentClass = null;

	// Boot array
	private $boot = [];

	// Push errors here
	public static $errors = [];

	// Push Success here
	public static $success = [];

	// Push Warnings here
	public static $warning = [];

	// Model data
	public static $modelData;

	// History
	public static $history_c;

	// When box
	public $whenBox = [];

	// Get current route request
	public $router_requests = "";

	// Bool app online
	private $isonline = false;

	// Active database
	private $current_database;

	// Message box
	public static $messageBox = [];

	// Use App View Method
	public $loadAppViewMethod = "";

	// Database encountered an error
	public static $databaseError = false;

	// Save static url
	public $staticurl = "";

	// Database connection variables not correctly filled
	public static $dbvarsError = false;

	// Get secret key
	public $secret_key;

	// Would be true when app encounters errors
	public static $newError = false;

	// counter
	private static $counter = 0;

	// bootstrap 
	private static $bootstrap_info = [];

	// readonly static finder
	public static $finder = null;

	// page path
	public static $pagePath = "";

	// thirdparty path
	public static $thirdparty_path = "";

	// get request
	public static $getUrl = "";

	// csrf_verified 
	public static $csrfVerified = false;

	// manage redirected data
	public static $redirectedData = [];

	// bootloader instance
	public static $instance = null;

	// set url
	public $url = [];

	// csrf token generated
	public static $csrfToken = null;

	// request domain
	public $domainEntry = false;

	// constructor
	public function __construct()
	{
		Bootloader::$instance = $this; // set instance of bootloader.
	}

	// private method for listening for api request. 
	// This method takes an array of configuration for incoming requests.
	final private function c_api($config)
	{
		Bootloader::$helper['url'] = UrlConfig::$appurl;
		
		$apiManager = BootMgr::singleton(\ApiManager::class, $config);

		if (BootMgr::$BOOTMODE[\ApiManager::class] == CAN_CONTINUE)
		{
			return $apiManager;
		}

		return BootMgr::instance();
	}

	// finder
	final private function c_finder($config)
	{
		if (is_array($config))
		{
			$finder = function($find) use ($config)
			{
				if (isset($config[$find]))
				{
					return $config[$find];
				}

				return "Tray $find not found.";
			};

			Bootloader::$finder = $finder;
			
		}
	}

	// force https
	final private function c_forcehttps()
	{
		$config = env('bootstrap', 'force_https');

		// get protocol used
		$protocol = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : null;

		if ($this->c_isonline() === true)
		{
			$query = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;

			if (!is_null($protocol))
			{
				if (!preg_match('/(https)/i', $protocol))
				{
					$url = trim(url());
					$url = preg_replace('/^(http:)/', 'https:', $url);
					$url = rtrim($url, '/');

					$redirect = false;

					if ($config === '*')
					{
						if (!is_null($query))
						{
							$url .= '/' . ltrim($query, '/');
						}

						$redirect = true;
					}
					else
					{
						if (is_bool($config))
						{
							$redirect = $config;
						}
						else
						{
							if (is_string($config))
							{
								$exp = explode(',', $config);

								if (!is_null($query))
								{
									$queryCopy = ltrim($query, '/');
									foreach ($exp as $i => $uri)
									{
										$uri = ltrim($uri, '/');
										$quote = preg_quote($uri, '/');

										$quote = str_replace('\*', '([\S]*)', $quote);

										if (preg_match("/^($quote)/", $queryCopy))
										{
											$redirect = true;
											$url .= '/' . $queryCopy;
											break;
										}
									}
								}
							}
						}
					}

					if ($redirect)
					{
						ob_start();
						header('location: '.$url);
					}
				}
			}
		}
	}

	// check if app is live or offline
	final private function c_isonline()
	{
		return SysPath::appIsLive();
	}

	// development environment
	final private function c_development()
	{
		$this->__isdev = (object)[];

		// get config passed
		if (!$this->__isonline())
		{
			if (count($this->config) == 1 && isset($this->config['url']))
			{
				$this->url = $this->config['url'];

				if ($this->url != "")
				{
					$this->setdefaulturl($this->url);	
				}
			}
			else
			{	
				Adapter::$_config = $this->config;
				$this->db_config = $this->config['db_config'];
				$this->connect_with = $this->config['connect_with'];	
			}
		}
		else
		{
			$this->isonline = true;
		}
	}

	// live environment
	final private function c_live()
	{
		if ($this->__isonline() === true)
		{
			if (count($this->config) == 1 && isset($this->config['url' ]))
			{
				$this->scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] . '://' : "http://";
				$this->host   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
				$this->self   = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';

				$this->extra = '';

				if ($this->self != '')
				{
					$this->extra = str_replace('index.php', '', $this->self);
				}

				$this->url = !empty($url) ? $this->config['url'] : $this->scheme . $this->host . $this->extra;

				$this->setdefaulturl($this->url);
			}
			else
			{
					$this->db_config = $this->config['db_config'];
					$this->connect_with = $this->config['connect_with'];
					Adapter::$_config = $this->config;
					$this->golive = 1;	

					$this->url = '';

					//ok set default url
					if (isset($_SERVER['SCRIPT_URI' ]))
					{
						$this->url = $_SERVER['SCRIPT_URI'];
					}
					else
					{
						if (isset($_SERVER['HTTP_HOST' ]) && isset($_SERVER['REQUEST_SCHEME' ]))
						{
							$this->scheme = $_SERVER['REQUEST_SCHEME'];
							$this->host = $_SERVER['HTTP_HOST'];

							$this->url = $this->scheme."://".$this->host."/";
						}
					}
			}

			$this->isonline = true;
		}
		else
		{
			$this->isonline = false;
		}
	}

	/**
	 * @method keepAlive
	 * @return void
	 * 
	 * 
	 */
	final private function c_keepAlive()
	{
		Bootloader::$instance = $this; // set instance of bootloader.

		$this->boot['isonline'] = $this->isonline;

		$this->boot['staticurl'] = $this->staticurl;
		$this->boot['isonline']  = $this->__isonline();
		$this->boot['debugMode'] = Bootloader::boot('debugMode');
		$this->boot['get_controller'] = config('router.default.controller');
		$this->boot['settings'] = BootMgr::singleton(UrlConfig::class);
		$this->boot['url'] = null;

		if (BootMgr::$BOOTMODE[UrlConfig::class] == CAN_CONTINUE)
		{	
			$this->boot['url'] = UrlConfig::$appurl;
		}

		Bootloader::$helper = $this->boot;

		// http access control
		$access_control = $this->boot('http_access_control');

		if (count($access_control) > 0)
		{
			// set allow headers for API
			header('Access-Control-Allow-Headers: '.implode(',', $access_control));
		}
		// #free memory
		$access_control = null;

		// set timezone
		date_default_timezone_set(env('bootstrap', 'timezone'));

		// listen for ready event triggered by the system
		Middleware::System()->event('ready', function($sys){
			
			// Reset CSRF Token 
			Route::request('get', function(){
				// generates a new token for HTML forms.
				$this->c_antiCsrf();
			});

			// get current page request.
			$this->c_currentPage($_URL, $sys);

			// call force https method
			// applies when in production environment
			// it is turned off by default. see kernel/config.php 
			$this->c_forcehttps();

			// set ROOT_GET
			$this->boot['ROOT_GET'] = $this->url;

			// Set current page
			Bootloader::$current_page = $this->url;

			// listen for api request
			$listen = \ApiManager::$listener;

			// make url avaliable to external classes
			Bootloader::$pagePath = $_URL;
			

			// Serve incoming request as an API request
			switch ($listen)
			{	
				// manage request through the API channel
				case true:
					switch (\ApiManager::requestPermission())
					{
						case true:
							// serve api request
							\ApiManager::serve($sys);
						break;

						case false:
							if (\ApiManager::$json_sent === false)
							{
								echo json_encode(['error' => 'Access denied. Api Entry refused.']);
							}
						break;
					}
				break;

				// manage request by loading requested controller.
				case false:
					// serve controller
					Controller::serve($sys, Bootloader::$helper, function($error) use (&$sys)
					{
						// let's check for custom error handler
						$loadCustomHandler = true;

						// check configuration
						switch (config('error.handler'))
						{
							// default?
							case 'use.default':
								$loadCustomHandler = false; // use default.
							break;

							// we have a custom config?
							default:
								// get handler
								$handler = config('error.handler');
								// check if handler exists
								$path = HOME . 'pages/' . ucfirst($handler) . '/main.php';
		
								// set load custom 
								$loadCustomHandler = false;
								// check if controller exists
								if (file_exists($path))
								{
									// serve controller
									$method = trim(config('error.error-'.$error['code']));
									
									if (strlen($method) > 1)
									{
										// load custom handler.
										$loadCustomHandler = true;

										// get args
										Controller::$viewArgs = $sys->system->refUrl;

										// set url
										$sys->system->setUrl(array_merge([$handler, $method], $sys->system->refUrl));

										// Serve controller
										Controller::serve($sys, Bootloader::$helper, function($err) use (&$error, &$sys, &$loadCustomHandler)
										{
											// set url
											$sys->system->setUrl(Controller::$viewArgs);
											// load default handler.
											$loadCustomHandler = false;
											// pass error
											$error = $err;
										});
									}
								}
						}

						if (!$loadCustomHandler)
						{
							// this would be executed if any error occurs.
							env('hideShrinke', true); // turn off bundler.
							// set app title
							$sys->view->apptitle = $error['title'];
							// other config
							$sys->view->template = false;
							$sys->view->default = true;
							// reset css
							$sys->view->loadCss = ['wrapper.css', 'moorexa.css', 'error.css'];
							// render error to view
							$sys->view->render($error['code']);
						}
					});
				break;
			}
		});
	}

	// extract vars
	final static function extractVars($class)
	{
		// create new reflection class
		$ref = new \ReflectionClass($class);

		// get class name
		$className = get_class($class);

		// get properties
		$props = $ref->getProperties();

		// ilterate props
		array_walk($props, function($obj, $index) use (&$className, &$class){
			
			// check if declaring class is current class
			if ($obj->getDeclaringClass()->getName() == $className)
			{
				// check if property is public
				if ($obj->isPublic() && $obj->isDefault())
				{
					$class->loadModelNow = false;

					// save in dropbox.
					Controller::$dropbox[$obj->getName()] = $class->{$obj->getName()};
				}
			}
		});

		// #clean up
		$className = null;
		$ref = null;
		$props = null;
	}

	// redirected data
	// this method collects data sent via the $this->redir() method.
	final static function RedirectedData(&$class)
	{
		static $dataPacked = []; // tmp storage

		$session = new Session;

		if (count($dataPacked) == 0)
		{
			$dataPacked = $session->has('__RedirectDataSent') ? $session->get('__RedirectDataSent') : [];
		}

		if (is_string($dataPacked))
		{
			$object = json_decode($dataPacked);
			
			$view = isset(Bootloader::$helper['active_v']) ? Bootloader::$helper['active_v'].'Message' : 'message';

			if (is_object($object))
			{
				$dataPacked = [$view => $object];
			}
			else
			{
				$dataPacked = [$view => $dataPacked];
			}
		}

		if (is_array($dataPacked) && count($dataPacked) > 0)
		{
			$decode = is_string($dataPacked) ? json_decode($dataPacked) : false;

			if ($decode != false)
			{
				$dataPacked = $decode;
			}

			if (is_array($dataPacked) || is_object($dataPacked))
			{
				foreach($dataPacked as $key => $val)
				{
					if (is_string($key))
					{
						$class->{$key} = $val;
						Controller::$dropbox[$key] = $val;
					}
				}
			}
			else
			{
				$class->onair = $dataPacked;
			}


			$req = Bootloader::$pagePath;
			$destination = explode('/', $session->get('__RedirectDataDestination'));

			if (in_array($destination[0], $req))
			{
				$session->drop('__RedirectDataDestination', '__RedirectDataSent');
			}
		}
	}

	// get class method parameters
	public function getParameters($object, $method, &$bind=null, $unset = false)
	{
		$ref = new \ReflectionClass($object);
		
		if ($ref->hasMethod($method))
		{
			$meth = $ref->getMethod($method);
			$params = $meth->getParameters();

			$all = $this->url;

			if (is_array($all))
			{
				$url = array_splice($all, 2);
			}
			else
			{
				$url = [];
			}

			if ($unset !== false)
			{
				if (is_array($unset))
				{
					$url = $unset;
				}
				else
				{
					if (is_array($url) && is_numeric($unset))
					{
						unset($url[$unset]);
					}
				}
			}

			$ref = null;
			$meth = null;
			$newarr = [];
		
			if (count($params) > 0)
			{
				foreach ($params as $i => $obj)
				{
					$newarr[$i][] = $obj->name;

					$ref = new \ReflectionParameter([$object, $method], $i);

					try
					{
						$class = $ref->getClass();

						if ($class !== null)
						{
							if ($class->isInstantiable())
							{
								$getclass = BootMgr::singleton($class->name);

								if ($class->name == Structure::class)
								{
									// reset data
									$getclass->buildQuery = [];
									$getclass->queryInfo = [];
									$getclass->sqlString = '';
									$getclass->sqljob = [];
								}

								if (is_subclass_of($getclass, '\Moorexa\ApiModel'))
								{
									// get rules
									ApiModel::getSetRules($getclass);
								}

								$newarr[$i][] = $getclass;
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
		}

		$ref = null;
		$bind = is_null($bind) ? [] : $bind;
		
	}

	// manage csrf token
	final private function c_antiCsrf()
	{
		// generate token
		$token = uniqid(time());
		
		// get session id
		$sessionid = session_id();

		// get salt
		$salt = View::$packagerJson['csrf_salt'];

		// build token with app url
		$token = md5(url($sessionid)) . 'salt:'.$salt.'/token:'.$token.'/sessionid:'.$sessionid;

		// encrypt token with secret key
		$encrypt = encrypt($token);

		// save token
		self::$csrfToken = $encrypt;
	}

	/**
	 * @method currentPage
	 * @return void
	 * 
	 * Satisfies routing request by a user.
	 * @var $_URL reference var. would contain url path
	 * @var $sys reference var. contains system class
	 */
	final private function c_currentPage(&$_URL = [], &$sys)
	{
		// build GET
		if (!isset($_GET['__app_request__']) && isset($_SERVER['REQUEST_URI']))
		{
			// get uri
			$uri = $_SERVER['REQUEST_URI'];
			// remove leading '/'
			$uri = urldecode(ltrim($uri, '/'));

			$parse = parse_url($uri);

			if (isset($_SERVER['REQUEST_QUERY_STRING']))
			{
				$_GET['__app_request__'] = isset($parse['path']) ? $parse['path'] : '/';
			}
		}

		// get current url request
		$refUrl = $sys->system->getUrl();
		// $refUrl = $sys->system->cleanUrl($refUrl);
		
		// peharps a user sends home-page, we wanna understand this then 
		// convert to homePage. 
		$sys->system->urlHelper($refUrl);

		// the bootloader has requested for the url
		// so let's make it avaliable to her.
		Bootloader::$getUrl = $refUrl;


		// now that we have our url,
		// we wanna process for routes configuration
		if (!$sys->system->routeFulfilled($refUrl))
		{
			// where to begin
			$begin = 0;

			// manage request internally.
			if (is_array($refUrl) && count($refUrl) > 1)
			{
				$begin = 1;

				if(isset($refUrl[2]) || count($refUrl) >= 2)
				{
					$begin = 2;	
				}
			}

			// this would be useful to minimize checks
			$check_continue = false;
			$checkActive = true; // when we need to check active controller saved in the session
			// check default controller.
			$continue = true;


			// now where do we begin checking
			switch ($begin)
			{
				// a controller and a view requested.
				case 2:
					// unpack ref url
					list($first, $second) = $sys->system->cleanUrl($refUrl);
					
					// check $first is a controller
					$path = env('bootstrap', 'controller.basepath') .'/'. ucfirst($first) .'/main.php';

					// check
					if (file_exists($path))
					{
						// ok we have something.
						// check for function implementation
						if (stristr(file_get_contents($path), 'function '.$second) !== false)
						{
							$checkActive = false;
							$continue = false;
						}
					}

				break;

				// start from the begining. try auto find.
				case 0:
				  // unpack ref URL
				  list($first) = $sys->system->cleanUrl($refUrl);

				  // set continue as true
				  $continue = true;

				  // check if session saved an active controller 
				  if (session()->has('__active__controller'))
				  {
						$active_c = session()->get('__active__controller');

						if (strtolower($active_c) == strtolower($first))
						{
							$path = env('bootstrap', 'controller.basepath') .'/'. ucfirst($first) .'/main.php';

							if (file_exists($path))
							{
								if (stristr(file_get_contents($path), 'function '.$sys->system->view) !== false)
								{
									$checkActive = false;
									$refUrl[1] = $sys->system->view;
									$continue = false;
								}
							}
						}
				  }

				  // now $continue comes handy.
				  // will execute only if session check failed.
				  if ($continue)
				  {
					  // check if $first is a controller
					  $path = env('bootstrap', 'controller.basepath') .'/'. ucfirst($first) .'/main.php';

					  // check
					  if (file_exists($path))
					  {
						  if (stristr(file_get_contents($path), 'function '.$sys->system->view) !== false)
						  {
							 $checkActive = false;
							 $refUrl[1] = $sys->system->view;
							 $continue = false;
						  }
					  }
				  }

				  // #clean up
				  $path = null;

				break;
			}
			


			// check active controller saved in session
			if ($checkActive)
			{
				// get path
				$path = env('bootstrap', 'controller.basepath') .'/'. ucfirst($sys->system->cont) .'/main.php';

				// check if controller exist
				if (file_exists($path) && isset($refUrl[0]))
				{
					// check if method exists
					if (stristr(file_get_contents($path), 'function '.$refUrl[0]) !== false)
					{	
						$continue = false;
						array_unshift($refUrl, $sys->system->cont); // push controller to the begining of array
						$refUrl = array_unique($refUrl);
					}
				}

			}

			// lastly check if $continue is true
			// then check all list of controllers.
			if ($continue)
			{
				// check if no request was sent
				if (is_null($refUrl))
				{
					// create a new array with default routing settings.
					$refUrl = [$sys->system->cont, $sys->system->view];
				}
				else
				{
					// unpack array
					list($first) = $sys->system->cleanUrl($refUrl);

					// check first
					$this->c_checkcontrollers($first, $refUrl, $sys);
				}
			}
		}

		// make url globally avaliable
		$this->url = $refUrl;

		// push configuration to boot array
		$this->boot['currentPath'] = $refUrl;

		// #free memory
		$refUrl = null;

		// pass _URL
		$_URL = $this->url;

		// pass to system
		$sys->system->push('refUrl', $this->url);
	}


	/**
	 * @method checkcontrollers
	 * 
	 * checks pages/, scans for available controllers then loads 
	 * a controller that implements a requested view.
	 * 
	 * This method isn't always called. would be called if the default route fails,
	 * 
	 * @return url path or an empty string.
	 */
	final private function c_checkcontrollers($first, &$refUrl, &$sys)
	{
		// vars
		$controller = null;
		$seen = false;
		$scanAll = true;
		$addCont = true;

		// check default controller
		$default = env('bootstrap', 'controller.basepath') . '/'. ucfirst($sys->system->cont) . '/main.php';

		// check if file exits
		if (file_exists($default))
		{
			$first = lcfirst(preg_replace('/\s{1,}/','',ucwords(preg_replace('/[-]/',' ', $first))));

			// check if method exists in default controller
			if (stristr(file_get_contents($default), 'function '.$first) !== false)
			{
				$seen = true;
				$controller = $sys->system->cont;
				$scanAll = false;
				$addCont = false;
			}
		}

		// check active controller
		if (!$seen)
		{
			if (session()->has('__active__controller', $controller))
			{
				// create controller path
				$controllerPath = env('bootstrap', 'controller.basepath') . '/'. ucfirst($controller) . '/main.php';

				if (file_exists($controllerPath))
				{
					$first = lcfirst(preg_replace('/\s{1,}/','',ucwords(preg_replace('/[-]/',' ', $first))));

					// check if method exists in active controller
					if (stristr(file_get_contents($controllerPath), 'function '.$first) !== false)
					{
						$seen = true;
						$scanAll = false;
						$addCont = false;
					}
				}
			}
		}

		// should we scan all
		if ($scanAll)
		{
			$controllers = glob(env('bootstrap', 'controller.basepath').'/*');
			// run through list of controllers
			if (count($controllers) > 0)
			{
				array_walk($controllers, function($dir) use (&$seen, &$first, &$controller, &$scanAll, &$addCont)
				{
					// only execute if $seen is false
					if ($seen === false)
					{
						// replace double forward slash with a single one
						$dir = preg_replace("/[\/]{1,}/", '/', $dir);
						// check if path is a valid directory
						if (is_dir($dir))
						{
							// get controller path
							$file = $dir . '/main.php';

							// get classname
							$base = basename($dir);
							$class = explode('/', $dir);

							// class name recieved.
							$class = end($class);
							
							// check identity
							if (strtolower($class) == strtolower($first))
							{
								$controller = ucfirst($class);
								$seen = true;
							}
							else
							{
								if (file_exists($file))
								{
									if (!preg_match('/[^a-zA-Z0-9_]/', $first))
									{
										if (stristr(file_get_contents($file), 'function '.$first) !== false)
										{
											$seen = true;
											$controller = ucfirst($class);
											$addCont = false;
										}
									}
								}
							}
						}
					}
				});
			}
		}

		// seen
		if ($seen)
		{
			session()->set('__current__page__', $controller . '/@@/' . $this->boot['url']);

			if (!$addCont)
			{
				array_unshift($refUrl, $controller);
			}
		}
	}


	public static function boot($key, $value = null)
	{
		if ($value !== null)
		{
			$bootstrapInfo = count(Bootloader::$bootstrap_info) > 0 ? Bootloader::$bootstrap_info : View::$packagerJson;
			$bootstrapInfo[$key] = $value;

			Bootloader::$bootstrap_info = $bootstrapInfo;
		}	
		else
		{
			$bootstrapInfo = count(Bootloader::$bootstrap_info) > 0 ? Bootloader::$bootstrap_info : View::$packagerJson;

			if (isset($bootstrapInfo[$key]))
			{
				return $bootstrapInfo[$key];
			}

			return false;
		}
	}

	// registry method
	private function c_registry($registry)
	{
		$boot = $registry['boot'];

		if (count($boot) > 0)
		{
			foreach ($boot as $class => $meth)
			{
				if (is_string($class))
				{
					$className = '\\'.ltrim($class, '\\');

					if (!class_exists($className))
					{
						$className = $className.'Provider';
					}

					if (class_exists($className))
					{
						$ref = new \ReflectionClass($className);

						if ($ref->hasMethod($meth))
						{
							$this->getParameters($className, $meth, $const, [&$this]);

							$clas = BootMgr::assign($className, $ref->newInstanceWithoutConstructor());
							
							BootMgr::method($className.'@'.$meth, call_user_func_array([$clas, $meth], $const));

							$clas = null;
						}
					}
				}
				else
				{
					$className = '\\'.ltrim($meth, '\\');

					if (!class_exists($className))
					{
						$className = $className.'Provider';
					}
					
					if (class_exists($className))
					{
						$ref = new \ReflectionClass($className);

						if ($ref->hasMethod('__construct'))
						{
							$this->getParameters($className, '__construct', $const, [&$this]);
							$clas = $ref->newInstanceArgs($const);

							BootMgr::assign($className, $clas);
						}
						else
						{
							$clas = BootMgr::singleton($className);
						}

						if ($ref->hasMethod('boot') && !isset(Route::$loadedProviders[$className]))
						{
							$this->getParameters($className, 'boot', $const, [&$this]);

							BootMgr::method($className.'@boot', call_user_func_array([$clas, 'boot'], $const));

							$clas = null;
						}
					}
				}
			}
		}
	}

	// authentication method
	private function c_auth(\Closure $callback)
	{
		static $auth = null;

		if (is_null($auth))
		{
			// create only 1 instance.
			$auth = BootMgr::singleton(\Authenticate::class);
		}

		if (BootMgr::$BOOTMODE[\Authenticate::class] == CAN_CONTINUE)
		{
			// listen for load event
			Event::on('authentication.load', function($routes) use (&$callback, &$auth){
				
				// get controller
				$controller = Bootloader::$helper['active_c'];
				// get view
				$view = Bootloader::$helper['active_v'];

				// push routes used
				$auth->routes = $routes;

				$fullpath = env('bootstrap', 'controller.basepath') .'/'. $controller . '/main.php';

				$continue = false;

				if (file_exists($fullpath))
				{
					$class =& $controller;

					include_once $fullpath;

					if (class_exists($class))
					{
						$ins = BootMgr::singleton($class, [], false);

						if (BootMgr::$BOOTMODE[$class] == CAN_CONTINUE)
						{
							if (method_exists($ins, $view))
							{
								$continue = true;
							}
						}

						// clean up
						$ref = null;
						$ins = null;
					}
				}

				// clean up
				$fullpath = null;
				$view = null;

				if ($continue)
				{
					// call closure.
					call_user_func($callback, $auth);
				}
			});
		}
	}

	/**
	 * @method kernel.shortcuts
	 * @return void
	 * 
	 * Helper method for defining shortcuts to paths
	 */
	private function c_shortcuts(array $config)
	{
		// set shortcuts 
		SET::$shortcuts = $config;
	}

	/**
	 * @method __call magic method
	 * 
	 * opens a gateway to private methods that cannot be directly called
	 * through an instance or by a sub class.
	 * 
	 * @return void
	 */
	public function __call( $method, $params)
	{
		// @var $method := string
		// @var $params := array

		switch ($method)
		{
			/**
			 * @method isonline
			 * @return true or false 
			 * checks if app is online or still running on development environment
			 */
			case '__isonline':
				return $this->c_isonline();
		

			/**
			 * @method bootstrap
			 * @return void
			 * 
			 * loads configuration set in kernel/config.php 
			 */
			case 'bootstrap':
				// check if a specific configuration is requested.
				if (count($params) > 1)
				{	
					// get config packed in packagerJson
					$config = View::$packagerJson;
					// set config with key and value
					$config[$params[0]] = $params[1];
					// save config in packager
					View::$packagerJson = $config;
					// make config available to external methods.
					$this->config = $config;
					Bootloader::$bootstrap_info = $config;
				}
				else
				{
					// set config to first argument passed to the bootstrap method
					$this->config = $params[0];
					// make config available to external methods.
					View::$packagerJson = $this->config;
					Bootloader::$bootstrap_info = $this->config;
				}

			break;

			/**
			 * @method development
			 * @return void
			 * 
			 * Helps determine if app is running in development environment
			 */
			case 'development':
				// set config to the first argument passed
				$this->config = $params[0];
				$this->c_development();
			break;
			

			/**
			 * @method live
			 * @return void
			 * 
			 * Helps determine if app is running in production environment
			 */
			case 'live':
				// set config to the first argument passed
				$this->config = $params[0];
				$this->c_live();
			break;

			/**
			 * @method registry
			 * @return void
			 * 
			 * Helper method for loading providers just before staging app to
			 * request for a controller.
			 */
			case 'registry':
				$this->c_registry($params[0]);
			break;

			/**
			 * @method finder
			 * @return void
			 * 
			 * Helper method for loading a specific configuration
			 */
			case 'finder':
				$this->c_finder($params[0]);
			break;


			/**
			 * @method keepAlive
			 * @return void
			 * 
			 * Application entry
			 */
			case 'keep_alive':
				$this->c_keepAlive();
			break;

			/**
			 * @method checkcontrollers
			 * @return void
			 * 
			 * Helper method for loading a controllers, manage routes efficiently also.
			 */
			case 'checkcontrollers':

				$this->check = (object)[];
				$this->check->cont = $params[0];
				$this->c_checkcontrollers();

			break;

			/**
			 * @method database.channel
			 * @return object
			 * 
			 * Helper method for listening for any database query
			 */
			case 'channel':
				return call_user_func_array([$this, 'channel'], $params);

			/**
			 * @method database.domain
			 * @return object
			 * 
			 * Helper method for switching database configuration on air over a domain
			 */
			case 'domain':
				return call_user_func_array([$this, 'domain'], $params);


			/**
			 * @method database.tables
			 * @return void
			 * 
			 * Helper method for preloading database tables for quick access.
			 */
			case 'tables':
				call_user_func_array([$this, 'preloadTables'], $params);
			break;

			/**
			 * @method kernel.shortcuts
			 * @return void
			 * 
			 * Helper method for defining shortcuts to paths
			 */
			case 'shortcuts':
				call_user_func_array([$this, 'c_shortcuts'], $params);
			break;



			case 'db':

				$this->dbvars = (object)[];
				$this->dbvars->vars = $params[0];
				$this->dbHandler();

				return $this;


			case 'default':

				$this->dbvars->default = $params[0];
				$this->dbvars->isonline = $this->c_isonline();
				$this->dbHandlerDefault();
				$this->domainEntry = true;

				return $this;

			case 'anti_csrf':
				$this->c_antiCsrf();
			break;

			case 'api':
				return $this->c_api($params);

			case 'authentication':
				$this->c_auth($params[0]);
			break;

			case 'directives':
				if (class_exists('\Moorexa\Rexa'))
				{
					$directives = Rexa::$directives;
					Rexa::$directives = array_merge(Rexa::$directives, $params[0]);

					$next = isset($params[1]) ? $params[1] : null;

					if (!is_null($next))
					{
						// inject directives
						if (is_callable($next))
						{
							$directive = BootMgr::singleton(Directive::class);
							
							if (BootMgr::$BOOTMODE[Directive::class] == CAN_CONTINUE)
							{
								call_user_func($next, $directive);
							}
						}
					}
				}
			break;

			default:
				throw new \Exception($method .'() cannot be called in '.get_class().'. Not permitted!');
				
		}

		\env::$config_env[$method] = isset($params[0]) ? $params[0] : null;
	}
	
	// Handle how we set class properties
	public function __set( $var, $value)
	{	

		if (isset(get_class_vars(get_class())[$var]))
		{
			$this->{$var} = $value;
		}
		else
		{
			$this->_memory_box[$var] = $value;
		}
			
	}

	// Handle how we get class properties
	public function __get( $var)
	{
		if (isset($this->_memory_box[$var]))
		{
			return $this->_memory_box[$var];	
		}

		if (isset($this->{$var}))
		{
			return $this->{$var};
		}
		
		return null;
	}

	// get protected vars
	final static function getProtected($var)
	{
		if ($var == '_vars')
		{
			return parent::$_vars;
		}
	}
} 
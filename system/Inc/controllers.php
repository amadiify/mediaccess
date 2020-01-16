<?php
	
namespace Moorexa;

use Moorexa\Bootloader;
use Moorexa\View;
use Moorexa\DB;
use Moorexa\DB\Table;
use utility\Classes\BootMgr\Manager as BootMgr;

/**
 * @package Moorexa Controller
 * @version 0.0.1
 * @author  Ifeanyi Amadi
 */

if (!class_exists('Moorexa\Controller'))
{
	class Controller extends ApiModel
	{
		public static $dropbox = [];
		public $noview = false;
		public static $continue = false;
		public static $appInstance;
		public static $_auth = [];
		public static $rendering = false;
		public static $actionAccess = [];
		public $model = null;
		public static $csmanager = [];
		public $thirdpartyPath = "";
		public $servingInstaller = false;
		public $noModel = false;
		public  static $platform = ['ready' => [], 'waiting' => []];
		private static $exceptFailed = false;
		protected static $noRenderPlease = false;
		protected $controller = null;
		public static $controllerReady = false;
		public $provider = null; // view provider loaded..
		// header config
		private static $config = [];
		// set args
		public static $viewArgs = [];
		// model packed
		public static $modelPacked = [];
		// assets instance
		public $assets = null;
		// controller instance
		public static $ControllerInstance = null;
		// load model
		public $loadModelNow = true;
		// provider waiting
		public static $providerWaiting = [
			'boot' => false,
			'willEnter' => false
		];
		// path scanned
		private static $pathScanned = false;
		// state changed
		private $stateChanged = false;
		// require list
		public static $requireList = ['js'=>[], 'css'=>[]];
		// redirect called
		public static $redirCalled = false;
		// asset preloader
		public static $assetPreloader = [];
		// page props
		public static $pageProps = [];

		public function __construct()
		{
			if (self::$ControllerInstance == null)
			{
				self::$ControllerInstance = $this;
			}
		}

		// render to view.
		protected function render($path, $arg = "", $arg2 = "")
		{
			switch ( Controller::$rendering === false && $this->stateChanged == false)
			{
				// render view
				case true:
					// app would render now
					self::$appInstance->rendering = true;

					// controller
					$controller = Bootloader::$helper['active_c'];
					// view
					$view = Bootloader::$helper['active_v'];
					// boot mgr
					$request = $controller . '@' . $view . '@render';

					BootMgr::method($request, null);

					if (BootMgr::$BOOTMODE[$request] == CAN_CONTINUE)
					{
						// get vars from class if properties gets updated
						$this->getVarsFromClassIfUpdated();

						// call the render method/
						return BootMgr::methodGotCalled($request, self::$appInstance->render($path, $arg, $arg2));
					}
				break;
			}
		}

		// do not create a model
		protected function noModel()
		{
			$this->noModel = true;
			return $this;
		}

		// get vars
		private function getVarsFromClassIfUpdated()
		{
			$dropbox = &Controller::$dropbox;
			$currentClass = Bootloader::$currentClass;

			// get class Name
			$className = get_class($currentClass);

			// create reflection class
			$reflection = new \ReflectionClass($className);

			// get properties
			$properties = $reflection->getProperties();

			array_map(function($property) use (&$className, &$dropbox, &$currentClass)
			{
				if ($property->class == $className)
				{
					if (!$property->isPrivate() && !$property->isProtected())
					{
						if ($currentClass->{$property->name} != null)
						{
							// add to dropbox
							$dropbox[$property->name] = $currentClass->{$property->name};
						}
					}
				}
			}, $properties);

		}

		// redirect method. 
		protected function redir($path, $data = null)
		{
			switch ( !(self::$noRenderPlease) && !$this->stateChanged )
			{
				// call redirection.
				case true:
					if (!self::$redirCalled)
					{
						self::$redirCalled = true;

						// controller
						$controller = Bootloader::$helper['active_c'];

						// view
						$view = Bootloader::$helper['active_v'];
						
						// boot mgr
						$request = $controller . '@' . $view . '@redir';

						BootMgr::method($request, null);
						
						if (BootMgr::$BOOTMODE[$request] == CAN_CONTINUE)
						{
							// call the renderNew method/
							return BootMgr::methodGotCalled($request, self::$appInstance->renderNew($path, $data));
						}
					}
				break;

				// failed. return app instance.
				case false:
					return self::$appInstance;
				break;
			}

		}

		public function __set($key, $value)
		{	
			if (!is_object($this->model))
			{
				Model::$storageBox[$key] = $value;
			}

			// store in dropbox
			Controller::$dropbox[$key] = $value;

			// pass data to the view class;
			if (is_object(self::$appInstance))
			{
				self::$appInstance->{$key} = $value;
			}
		}

		public function set($key, $value)
		{
			// store in dropbox
			Controller::$dropbox[$key] = $value;
		}

		// call model
		public function getModel()
		{
			// get args
			$args = func_get_args();

			// get model request
			if (count($args) > 0)
			{
				// get requested model
				$model = explode('@', $args[0]);
				// get params
				$params = array_splice($args, 1);
				// get controller
				switch (count($model))
				{
					case 2:
						// has controller
						$controller = $model[0];
						// get current controller
						$current = Bootloader::$helper['active_c'];
						// set current controller
						Bootloader::$helper['active_c'] = $controller;
						// call model
						$model = call_user_func_array('\\Moorexa\Model::'.$model[1], $params);
						// reset current controller
						Bootloader::$helper['active_c'] = $current;
					break;

					// single call
					case 1:
						$model = call_user_func_array('\\Moorexa\Model::'.$model[0], $params);
					break;
				}

				// return model
				return $model;
			} 

			// return current model
			return $this->model;
		}

		// call provider 
		public function getProvider()
		{
			// get arguments
			$args = func_get_args();
			// ensure provider class name was sent
			if (count($args) > 0)
			{
				// argument 1 should be the Controller and view name
				$class = $args[0];
				// explode, so we can check if that provider exists.
				list($controller, $view) = explode('@', $class);
				// build path
				$path = env('bootstrap', 'controller.basepath') .'pages/'. ucfirst($controller) . '/Providers/' . lcfirst($view) . 'Provider.php';
				// check if provider exists
				if (file_exists($path))
				{
					// include controller provider
					$controllerProvider = env('bootstrap', 'controller.basepath') . 'pages/' . ucfirst($controller) . '/provider.php';
					include_once $controllerProvider;

					// load view provider
					include_once $path;
					// build class name
					$className = ucfirst($controller).ucfirst($view).'Provider';
					// return instance
					// #clean up first
					$class = null;
					$controllerProvider = null;
					$other = array_splice($args, 1);

					// ensure class exists.
					if (class_exists($className))
					{
						$provider = BootMgr::singleton($className, $other);

						if (BootMgr::$BOOTMODE[$className] == CAN_CONTINUE)
						{
							return $provider;
						}

						return BootMgr::instance();
					}
					else
					{
						// throw error
						throw new \Exceptions\Providers\ProviderException('Provider Class '.$className.' doesn\'t exists. Please check '.$path);
					}

					// clean up
					$path = null;
				}
				else
				{
					// throw error
					throw new \Exceptions\Providers\ProviderException('Provider '. lcfirst($view).'Provider, doesn\'t exists in '.$path.'. Ensure controller \''.ucfirst($controller).'\' exists also.');
				}

				// #clean up
				$args = null;
				$class = null;
				$classArray = null;	
			}

			return $this->provider;
		}

		// call package
		public function getPackage()
		{
			// get arguments
			$args = func_get_args();
			// ensure package class name was sent
			throw_unless(count($args) == 0, ['\Exceptions\Packages\PackageException', 'Invalid number of arguments. Could not load package.']);
			// load package
			// argument 1 should be the Controller and package name
			$class = $args[0];
			// set default controller and package
			$controller = Bootloader::$helper['active_c'];
			$package = $class;

			// explode, so we can check if that package exists.
			if (strpos($class, '@') !== false)
			{
				list($controller, $package) = explode('@', $class);
			}

			// build path
			$path = env('bootstrap', 'controller.basepath') .'/'. ucfirst($controller) . '/Packages/' . ucfirst($package) . '.php';
			
			// package exists?
			throw_unless(!file_exists($path), ['\Exceptions\Packages\PackageException', [$controller.'@'.$package]]);

			// get arguments for package
			$other = array_splice($args, 1);
			// load package
			return call_user_func_array('\\Moorexa\Packages::loadPackage', [$controller, $package, $other]);
		}

		protected function except()
		{
			$continue = false;
			
			$pages = func_get_args();

			$current = Bootloader::$helper['get_view'];

			if (is_array($pages) && count($pages) > 0)
			{
				$pages = array_flip($pages);

				if (!isset($pages[$current]))
				{
					$continue = true;
				}
			}
			else
			{
				if (!in_array($current, func_get_args()))
				{
					$continue = true;
				}
			}

			if ($continue)
			{
				Controller::$continue = true;
			}
			else
			{
				Controller::$exceptFailed = true;
			}

			return $this;
		}

		// change state
		public function changeState($view)
		{
			$args = func_get_args();

			$cont = isset(BootLoader::$helper['active_c']) ? BootLoader::$helper['active_c'] : null;

			if (count($args) > 1)
			{
				// clean up
				session()->remove('__active__controller');

				BootLoader::$helper['active_c'] = $args[0];
				$view = $args[1];
			}

			$stateUrl = page($view);

			$bin = $this->jsExport(['stateUrl' => $stateUrl])->jsbin(function()
			{
				$vars = import_variables();
				$json = new Object();
				$json.$html = $document.$body.innerHTML;
				$json.$pageTitle = $document.title;
				// set state
				$window.$history.pushState($json,"", $vars.stateUrl); 
			});

			$view = ucwords(str_replace('-',' ',$view));
			$view = lcfirst(str_replace(' ','',$view));

			// fallback for server
			$_SESSION['HISTORY_STATE'] = $view;

			// set current view
			BootLoader::$helper['active_v'] = $view;
		}

		public function switchState()
		{
			$args = func_get_args();

			// manually set the app request
			$_GET['__app_request__'] = count($args) > 1 ? implode('/', $args) : $args[0];

			// extracted data
			$data = Controller::getDropbox();

			// attach lifecycle
			lifecycle('controllers.serve')->attach('view', function() use ($args, $data){
				// change state
				call_user_func_array([$this, 'changeState'], $args);
				Controller::$dropbox = $data;
			});

			$_POST = [];

			// call ready event.
			Middleware::System()->eventCallback('ready');

		}

		public function __get($name)
		{	
			$model =& $this->model;

			// configuration ?
			switch ($name == 'config')
			{
				case true:
					// check if not previously created
					if (!isset(self::$config['__set']))
					{
						self::$config['__set'] = (object) [];
					}
					// return config.
					return self::$config['__set'];
			

				case false:
					// we check our dropbox for loaded variables.
					if (array_key_exists($name, Controller::$dropbox))
					{
						return Controller::$dropbox[$name];
					}

					$active = Middleware::$active;

					switch (is_array($active))
					{
						case true:
							if (count($active) > 0)
							{
								array_walk($active, function($bus, $a) use (&$model)
								{
									$model->middlewareData($a,$bus);
								});
							}
						break;
					}
					
					// finally, we check the service manager
					if (is_object($model))
					{
						switch (count(Controller::$csmanager) > 0)
						{
							// check variables defined.
							case true:
								$cntvars = Controller::$csmanager;

								if (array_key_exists($name, $cntvars))
								{
									return $cntvars[$name];
								}
							break;

							// try load vars from sm-model and sm-controller
							case false:
								$vars = ServiceManager::loadvars('sm-controller.php');

								// get page props
								$props = Controller::$pageProps;

								if (count($props) > 0)
								{
									$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
									$func = $trace['function'];
									$cont = Bootloader::$helper['active_c'];

									$build = strtoupper($cont . '/' . $func);

									if (isset($props[$build]) || isset($props[strtoupper($cont)]))
									{
										// get callback
										$callback = isset($props[$build]) ? $props[$build] : $props[strtoupper($cont)];
										$object = (object) [];

										call_user_func($callback, $object);

										$array = (array) $object;

										if (isset($array[$name]))
										{
											return $array[$name];
										}
									}

									$build = null;
									$trace = null;
									$props = null;

								}

								if (array_key_exists($name, $vars))
								{
									return $vars[$name];
								}
							break;
						}
					}

					if (is_object($model))
					{
						if (is_object($model->modelObject) && is_array($model->modelObject->tableData))
						{
							if (array_key_exists($name, $model->modelObject->tableData))
							{
								return $model->modelObject->tableData[$name];
							}
						}
					}

					if ($this->loadModelNow)
					{
						$model = Model::getModelInstance($name, [],$this);
						return $model;
					}
					
					return null;
			}
			
		}

		public function __call($meth, $params)
		{
			$cont = Bootloader::$helper['active_c'];

			if ($meth == 'env')
			{
				return $this->__env($params[0]);
			}

			if ($cont == $meth)
			{
				session()->drop('history.url');

				return $this;
			}
			elseif ($meth == 'jsbin')
			{
				return call_user_func_array([self::$appInstance, 'jsbin'], $params);
			}
			elseif ($meth == 'jsExport')
			{
				return call_user_func_array([self::$appInstance, 'jsExport'], $params);
			}
			elseif (isset(Controller::$dropbox[$meth]))
			{
				if (is_callable(Controller::$dropbox[$meth]))
				{
					$call = Controller::$dropbox[$meth];
					return call_user_func_array($call, $params);
				}
			}
			else
			{
				$vars = ServiceManager::loadvars('sm-controller.php');

				// get page props
				$props = Controller::$pageProps;

				if (count($props) > 0)
				{
					$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
					$func = $trace['function'];
					$cont = Bootloader::$helper['active_c'];

					$build = strtoupper($cont . '/' . $func);

					if (isset($props[$build]) || isset($props[strtoupper($cont)]))
					{
						// get callback
						$callback = isset($props[$build]) ? $props[$build] : $props[strtoupper($cont)];
						$object = (object) [];

						call_user_func($callback, $object);

						$array = (array) $object;

						if (isset($array[$meth]))
						{
							$data = $array[$meth];

							if (is_callable($data))
							{
								// get arguments
								Route::getParameters($data, $const, $args);

								return call_user_func_array($data, $const);
							}
						}
					}

					$build = null;
					$trace = null;
					$props = null;

				}
				
				if (isset($vars[$meth]) && is_callable($vars[$meth]))
				{
					return call_user_func_array($vars[$meth], $params);
				}
			}

			return Model::getModelFunc($meth, $params, $this->model);
		}

		// environment
		protected function __env($name)
		{
			if ($name == 'app')
			{
				return $this->app;
			}
			
			return $this->{$name};
		}

		// set current path
		public static function setCurrentPath(&$controller, $config)
		{
			if ($controller != '@starter')
			{
				$path = env('bootstrap', 'controller.basepath');

				// read controllers
				$dirs = glob($path . '/*');
				// loop through
				array_walk($dirs, function($dir) use (&$config){
					// only do this if directory
					if (is_dir($dir) && (basename($dir) != '.' && basename($dir) != '..'))
					{
						// define paths
						array_walk($config, function($path, $key) use (&$dir)
						{
							$path = rtrim($path, '/');
							// check if directory exists
							if (is_dir($dir . $path))
							{
								// is defined?
								$constant = strtoupper(basename($dir)) . '_' . strtoupper($key);

								if (!defined($constant))
								{
									// define
									define($constant, $dir . $path . '/');
									
								}
							}
						});
					}
				});
			}
		}

		// get props
		public static function getArgs(&$providerClass, &$class)
		{
			// load properties
			$ref = new \ReflectionClass($providerClass);
			// get properties
			$props = $ref->getProperties();
			// get properties for this class only
			array_walk($props, function($obj) use (&$class){
				// check if class is current class.
				if ($obj->getDeclaringClass()->getName() == get_class($class->provider))
				{
					// check if property has a public visibility
					if ($obj->isPublic())
					{
						$class->config->{$obj->getName()} =& $class->provider->{$obj->getName()};
						// make avaliable to dropbox
						Controller::$dropbox[$obj->getName()] =& $class->provider->{$obj->getName()}; 
					}
				}
			});

			// delete ref
			$ref = null;
		}

		// load boot method for controller.
		public static function loadProviderBoot(&$controller, &$class, &$view=null, &$modelProviderData=[], &$instance=null, &$arguments=[])
		{
			// provider path
			$path = env('bootstrap', 'controller.basepath') . '/'. $controller .'/provider.php';

			// include provider
			include_once $path;

			// get class.
			$providerClass = $controller.'Provider';

			// check if class exists.
			if (class_exists($providerClass))
			{
				// load packed args.
				if (count(Controller::$viewArgs) > 0)
				{
					array_unshift($arguments, Controller::$viewArgs);
				}

				// load instance without argument.
				$class->provider = BootMgr::singleton($providerClass);

				if (BootMgr::$BOOTMODE[$providerClass] == CAN_CONTINUE)
				{
					// make model avaliable
					$class->provider->model = $class->model;

					// load properties
					self::getArgs($providerClass, $class);

					//  check if boot method exists
					if ( method_exists($class->provider, 'boot') )
					{
						$next = new class 
						{
							public function __invoke()
							{
								Controller::$providerWaiting['boot'] = true;
							}
						};

						// create copy of argumenets
						$copy = $arguments;
						array_unshift($copy, $instance);
						array_unshift($copy, $next);

						// get arguments.
						$instance->getParameters($class->provider, 'boot', $const, $copy);
						
						// call boot methodK
						BootMgr::method($providerClass.'@boot', null);

						if (BootMgr::$BOOTMODE[$providerClass.'@boot'] == CAN_CONTINUE)
						{
							BootMgr::methodGotCalled($providerClass.'@boot', call_user_func_array([$class->provider, 'boot'], $const));
						}

						// load properties
						self::getArgs($providerClass, $class);
					}
				}
			}
			
			// clean up
			$providerClass = null;
		}

		// load provider for controller view
		public static function loadProvider($controller, &$class=null, &$view='_called', &$modelProviderData=[], &$instance=null, $arguments=[])
		{
			
			// provider path
			$path = env('bootstrap', 'controller.basepath') . '/'. $controller .'/provider.php';
			
			// load provider if it exists.
			switch (file_exists($path))
			{
				// provider exists. try work with provider.
				case true:

					// check if view provider exists. then load
					$path = env('bootstrap', 'controller.basepath') . '/'. $controller . '/Providers/'. $view . 'Provider.php';

					// the check.
					switch (file_exists($path))
					{
						// skip loading onInit method from controller provider.
						case true:
							// load view provider.
							include_once $path;
							
							// call viewWillLoad method if class exists.
							$providerClass = $controller.ucfirst($view).'Provider';

							// check if class exists
							if (class_exists ($providerClass))
							{
								$class->provider = BootMgr::singleton($providerClass, $arguments);

								if (BootMgr::$BOOTMODE[$providerClass] == CAN_CONTINUE)
								{
									// make model avaliable
									$class->provider->model = $class->model;

									// load viewWillEnter if exists
									if (method_exists($class->provider, 'viewWillEnter'))
									{
										$next = new class 
										{
											public function __invoke()
											{
												Controller::$providerWaiting['willEnter'] = true;
											}
										};

										array_unshift($arguments, $next);

										// get parameters
										$instance->getParameters($class->provider, 'viewWillEnter', $const, $arguments);

										// call method
										BootMgr::method($providerClass . '@viewWillEnter', null);

										if (BootMgr::$BOOTMODE[$providerClass . '@viewWillEnter'] == CAN_CONTINUE)
										{
											BootMgr::methodGotCalled($providerClass . '@viewWillEnter', call_user_func_array([$class->provider, 'viewWillEnter'], $const));
										}
									}
									else
									{
										self::$providerWaiting['willEnter'] = true;
									}

									$ref = new \ReflectionClass($providerClass);

									// get properties
									$props = $ref->getProperties();
									// get properties for this class only
									array_walk($props, function($obj) use (&$class){
										// check if class is current class.
										if ($obj->getDeclaringClass()->getName() == get_class($class->provider))
										{
											// check if property has a public visibility
											if ($obj->isPublic())
											{
												$class->config->{$obj->getName()} =& $class->provider->{$obj->getName()};
												// make avaliable to dropbox
												Controller::$dropbox[$obj->getName()] =& $class->provider->{$obj->getName()}; 
											}
										}
									});
								}
								
								// clean up
								$ref = null;
							}

							// clean up
							$providerClass = null;

						break;

						// load onInit method in controller provider.
						case false:

							if ($view == '_called')
							{
								$instance = Bootloader::$instance;
								$view = null;
								$path = env('bootstrap', 'controller.basepath') . '/'. $controller .'/main.php';
								if (file_exists($path))
								{
									include_once $path;
									self::loadProviderBoot($controller, $class, $view, $modelProviderData, $instance, $arguments);
								}

								return $class;
							}
							else
							{
								// check if provider can emit init event for view
								$method = 'on'.ucfirst($view).'Init';
								
								// check 
								if ( method_exists($class->provider, $method) )
								{
									$next = new class 
									{
										public function __invoke()
										{
											Controller::$providerWaiting['willEnter'] = true;
										}
									};

									array_unshift($arguments, $next);

									// get arguments.
									$instance->getParameters($class->provider, $method, $const, $arguments);

									// call method
									BootMgr::method(get_class($class->provider).'@'.$method, null);

									if (BootMgr::$BOOTMODE[get_class($class->provider).'@'.$method] == CAN_CONTINUE)
									{
										BootMgr::methodGotCalled(get_class($class->provider).'@'.$method, call_user_func_array([$class->provider, $method], $const));
									}
								}
								else
								{
									self::$providerWaiting['willEnter'] = true;
								}

								// clean up
								$method = null;
							}
						break;
					}

					
				break;
			}
			// #clean up
			$path = null;
		}

		// register model
		public function registerModel()
		{
			// arguments
			$args = func_get_args();
			// modelPacked
			array_map(function($model){
				$model = explode('@', $model);
				list($modelName, $view) = $model;
				self::$modelPacked[$view] = $modelName;
			}, $args);
		}

		// serve controller
		public static function serve($sys, &$helper, $callback=null)
		{
			// get bootloader instance
			$instance =& Bootloader::$instance;

			// filter incoming GET and POST request to controller, model and views
			$sys->system->filterRequest($instance);

			// unpack url
			$sys->system->unpackUrl($controller, $view);
			

			// copy url
			$url = $sys->system->refUrl;
			list($controller, $view) = $sys->system->cleanUrl($controller, $view);

			// set controller
			$controller = ucfirst($controller);

			// set current path
			self::setCurrentPath($controller, [
				'path' => '/',
				'chs' => '/Chs',
				'custom' => '/Custom',
				'model' => '/Models',
				'package' => '/Packages',
				'provider' => '/Providers',
				'static' =>  '/Static',
				'partial' =>  '/Partials',
				'view' => '/Views'
			]);
			
	
			// include middleware
			include_once PATH_TO_SERVICES . 'middleware.php';

			// check if not starter template requested for
			switch ( ($controller != '@starter') )
			{
				
				// ok load controller
				case true:					
					// set controller recieved by the request handler
					$helper['get_controller'] = $controller;
					$helper['c_controller'] = $controller;
					$helper['active_c'] = $controller;

					// set active view
					$helper['active_v'] = $view;
					$helper['get_view'] = $view;

					$url[0] = $controller;
					$url[1] = $view;

					// set location.url
					$helper['location.url'] = $url;

					// trigger authentication handler
					Event::emit('authentication.load', $url);
					

					// check if app is on coming soon or maintainance mode
					$loadController = true;

					if ( ($sys->packager['comingSoon']) || ($sys->packager['maintainceMode']) )
					{
						// do not load requested controller
						$loadController = false;

						// determine mode
						$mode = ($sys->packager['comingSoon']) ? 'comingsoon' : 'onmaintanance';

						// load template for starter
						$content = file_get_contents("help/Starter/default-starter.html");

						// extract section by mode
						$str = preg_quote($mode);
						preg_match("/(<\!)[-]{1,}\s*(@$str)\s*[-]*[>]/", $content, $match);
						preg_match("/(<\!)[-]{1,}\s*(@end$str)\s*[-]*[>]/", $content, $match2);

						// get begining
						$begin = strstr($content, $match[0]);
						// extract string.
						$string = substr($begin, 0, strpos($begin, $match2[0]));

						// set current controller
						BootLoader::$currentClass = BootMgr::singleton(Controller::class);

						if (BootMgr::$BOOTMODE[Controller::class] == CAN_CONTINUE)
						{
							Bootloader::$helper['c_controller'] = 'Moorexa\Controller';

							// load to view
							$sys->view->default = true;
							$sys->view->loadCss = ['wrapper.css','moorexa.css'];
							$sys->view->loadJs = ['http.js', 'Rexajs/moorexa.min.js'];
							$sys->view->render($string);
						}

						// #clean up
						$mode = null;
						$content = null;
						$str = null;
						$begin = null;
						$string = null;
					}
					
					// should we load controller
					if ( $loadController )
					{
						// yes!
						// get path
						$path = env('bootstrap', 'controller.basepath') . '/' . $controller . '/main.php';

						//has error
						$hasError = ['title' => 'Page not found', 'code' => 404];

						// check if path exists
						switch (file_exists($path))
						{
							// load model, provider, render view
							case true:
								// load paths
								self::definePaths();
								// set active controller
								session()->set('__active__controller', $controller);
								// include controller main file
								include_once $path;
								// do $path contain a valid controller class?
								switch (class_exists($controller))
								{
									case true:
										// create a reflection class
										$ref = new \ReflectionClass($controller);

										// ensure view isn't the same with controller
										if (strtolower($controller) == strtolower($view))
										{
											// check if controller has defaultView prop
											if ($ref->hasProperty('defaultView'))
											{
												$view = $controller::$defaultView;
											}
											else
											{
												$view = config('router.default.view');
											}
										}

										// check if view method exists in controller
										if ($ref->hasMethod($view))
										{
											// no error so far
											$hasError = null;

											// get arguments for views
											$arguments = array_splice($url,2);

											// get constructor arguments
											$instance->getParameters($controller, '__construct', $argument, $arguments);

											// create constructor instance
											$class = BootMgr::singleton($controller, $argument);

											if (BootMgr::$BOOTMODE[$controller] == CAN_CONTINUE)
											{
												// load controller public properties
												self::bindToController(Route::$controllerVars, $class);

												// extract properties from cllass
												Bootloader::extractVars($class);

												// load assets
												self::$ControllerInstance->assets = $sys->loadAssets;

												// set controller
												$sys->view->controller = $controller;

												// bind app to controllr
												self::$appInstance = $sys->view; // useful for rendering views and redirection.

												// load boot
												self::loadProviderBoot($controller, $class, $view, $modelProviderData, $instance, $arguments);

												// set controller for model
												$sys->model->controllerName = $controller;
													
												// create view model
												$model = $view;

												// check if model has been packed
												if (isset(self::$modelPacked[$view]))
												{
													$model = self::$modelPacked[$view];
												}

												// set model to default.
												$setModelToDefault = false;

												// model instance
												$modelInstance = null;

												// get parameter for view method
												$instance->getParameters($class, $view, $const, $arguments);

												$ref = new \ReflectionClass($class);
												$meth = $ref->getMethod($view);
												$param = $meth->getParameters();
												
												// make params avalibale to views.
												if (count($param) > 0)
												{
													array_walk($param, function($obj, $i) use (&$class, &$arguments){
														$class->{$obj->name} = isset($arguments[$i]) ? $arguments[$i] : null;
													});
												}

												// #clean up
												$ref = null;
												$param = null;
												$meth = null;

												// set current class without model and provider
												Bootloader::$currentClass = $class;

												// copy view
												$viewCopy = $view;

												// provider waiting
												$pw = (object) self::$providerWaiting;
												
												// view ready
												lifecycle('controllers.serve')->breakpoint('view');

												if ($pw->boot)
												{	
													// load provider for controller
													self::loadProvider($controller, $class, $view, $modelProviderData, $instance, $arguments);

													// load model action
													Model::loadModelAction($controller, $model, $instance, $setModelToDefault, $sys, $modelInstance);

													// reassign view
													$view = $viewCopy;

													// update current class
													Bootloader::$currentClass = $class;

													// make model available to controller
													switch (is_null($modelInstance))
													{
														// model loaded
														case false:
															$class->model = $modelInstance;
														break;

														// load default model class
														case true:
															$class->model = $sys->model;
														break;
													}

													$pw = (object) self::$providerWaiting;
													
													if ($pw->willEnter)
													{
														// unpack loaded css and javascripts
														$sys->view->unpack();

														// set view
														$sys->view->view = $view;

														// make provider avaliable to app
														$sys->view->controllerProvider = $class->provider;

														// set current class for app
														Bootloader::$currentClass = $class;
														
														// render view.
														$render = true;
														$waiting = Middleware::$waiting;

														// get redirected data
														Bootloader::RedirectedData($class);

														if (count(Route::$channelData) > 0)
														{
															$key = $controller . '/' . $view;
															
															if (isset(Route::$channelData[$key]))
															{
																$data = Route::$channelData[$key];
																$request = $data['request'];
																unset($data['request']);

																$response = $data;

																$render = false;

																$callfunc = function() use (&$request, &$class, &$view, &$response, &$const, &$instance)
																{
																	$nr = [$request, $response];
																	$const = array_merge($nr, $const);

																	if (count(Controller::$viewArgs) > 0)
																	{
																		array_unshift($const, Controller::$viewArgs);
																	}

																	// call viewDidEnter
																	switch (method_exists($class->provider, 'viewDidEnter'))
																	{
																		case true:
																			// get params
																			$instance->getParameters($class->provider, 'viewDidEnter', $arg, $const);

																			// load args
																			if (count(Controller::$viewArgs) > 0)
																			{
																				array_unshift($arg, Controller::$viewArgs);
																			}
																			
																			// call method
																			BootMgr::method(get_class($class->provider).'@viewDidEnter', null);

																			if (BootMgr::$BOOTMODE[get_class($class).'@'.$view] == CAN_CONTINUE)
																			{
																				BootMgr::methodGotCalled(get_class($class).'@'.$view, call_user_func_array([$class->provider, 'viewDidEnter'], $arg));
																			}

																		break;
																	}

																	BootMgr::method(get_class($class).'@'.$view, null);

																	if (BootMgr::$BOOTMODE[get_class($class).'@'.$view] == CAN_CONTINUE)
																	{
																		BootMgr::methodGotCalled(get_class($class).'@'.$view, call_user_func_array([$class, $view], $const));
																	}

																};

																if (isset($waiting[$view]))
																{
																	Middleware::callWaiting($waiting[$view], $callfunc);
																}
																else
																{
																	$callfunc();
																}
																
															}
														}

														if ($render)
														{
															$callfunc = function() use (&$const, &$class, &$view, &$instance)
															{
																if (count(Controller::$viewArgs) > 0)
																{
																	array_unshift($const, Controller::$viewArgs);
																}

																// call viewDidEnter
																switch (method_exists($class->provider, 'viewDidEnter'))
																{
																	case true:
																		// get params
																		$instance->getParameters($class->provider, 'viewDidEnter', $arg);
																		// load view args
																		if (count(Controller::$viewArgs) > 0)
																		{
																			array_unshift($arg, Controller::$viewArgs);
																		}

																		// call method
																		BootMgr::method(get_class($class->provider). '@viewDidEnter', null);

																		if (BootMgr::$BOOTMODE[get_class($class->provider). '@viewDidEnter'] == CAN_CONTINUE)
																		{
																			BootMgr::methodGotCalled(get_class($class->provider). '@viewDidEnter', call_user_func_array([$class->provider, 'viewDidEnter'], $arg));
																		}
																	break;
																}

																// keep tab
																Route::track();

																// call view
																BootMgr::method(get_class($class).'@'.$view, null);

																if (BootMgr::$BOOTMODE[get_class($class).'@'.$view] == CAN_CONTINUE)
																{
																	BootMgr::methodGotCalled(get_class($class).'@'.$view, call_user_func_array([$class, $view], $const));
																}
															};

															if (isset($waiting[$view]))
															{
																Middleware::callWaiting($waiting[$view], $callfunc);
															}
															else
															{
																$callfunc();
															}
														}
													}
												}

											}
										}
										else
										{
											$hasError['code'] = 204;
										}

										// clean up
										$ref = null;
									break;
								}
							break;
						}
						
						// error encountered
						if (!is_null($hasError))
						{
							BootMgr::method('http_error@'.$hasError['code'], null);

							if (BootMgr::$BOOTMODE['http_error@'.$hasError['code']] == CAN_CONTINUE)
							{
								BootMgr::methodGotCalled('http_error@'.$hasError['code'], call_user_func($callback, $hasError));
							}
						}
					}
					
				break;
				

				// load starter template
				case false:
					// make assets avaliable
					$assets = $sys->loadAssets;
					// include starter file
					include_once HOME . 'help/Starter/index.html';	
					// #clean up
					$assets = null;
				break;
			}
		}

		// define paths
		private static function definePaths()
		{
			if (self::$pathScanned === false)
			{
				$dir = glob(env('bootstrap', 'controller.basepath') . '/*');

				if (is_array($dir))
				{
					// run
					array_map(function($dir){
						if ($dir != '.' && $dir != '..')
						{
							if (is_dir($dir))
							{
								$base = basename($dir);
								$constant = 'PATH_TO_'.strtoupper($base);

								if (!defined($constant))
								{
									define($constant, $dir . '/');
								}
							}
						}
					}, $dir);

					self::$pathScanned = true;
				}
			}
		}

		// load config if set on runtime
		public static function LoadConfig(&$package)
		{
			// get config
			$config = self::$config;
			// check if we have __set assigned.
			if (isset($config['__set']))
			{
				// get values
				foreach ($config['__set'] as $key => $val)
				{
					// unpack
					$package->{$key} = $val;
				}
			}
		}

		// bind data to controller.
		protected static function bindToController($data, &$class)
		{
			// get type
			switch (gettype($data))
			{
				// array
				case 'array':
					// bind array value to controller
					array_walk($data, function($val, $key) use (&$class){
						$class->{$key} = $val;
					});
				break;
			}
		}

		// check if a variable exists in dropbox storage
		protected function has($name)
		{
			if (isset(Controller::$dropbox[$name]) || isset($this->{$name}))
			{
				return true;
			}

			return false;
		}

		protected function free()
		{
			$args = func_get_args();

			if (count($args) > 0)
			{
				foreach ($args as $a => $val)
				{
					session($val, false);
				}
			}

			return $this;
		}

		// middleware
		public function getMiddleware()
		{
			$args = func_get_args();
			$middleware = $args[0];
			$other = array_splice($args,1);
			$middleware = explode('@', $middleware);
			$class = $middleware[0];
			$method = isset($middleware[1]) ? $middleware[1] : null;

			// get view
			$view = $this->view;

			$file = deepScan(PATH_TO_MIDDLEWARE, [$class . '.php', ucfirst($class) . '.php', lcfirst($class) . '.php']);

			if (strlen($file) > 2)
			{
				// include middleware handler.
				include_once($file);

				$className = ucfirst($class);

				if ($method == null)
				{
					$class = BootMgr::singleton($className, $other);

					if (BootMgr::$BOOTMODE[$className] == CAN_CONTINUE)
					{
						Middleware::$waiting[$view][] = $class;

						return $class;
					}

					return BootMgr::instance();
				}
				else
				{
					$class = BootMgr::singleton($className, $other);
					
					if (BootMgr::$BOOTMODE[$className] == CAN_CONTINUE)
					{
						if (method_exists($class, $method))
						{
							Bootloader::$instance->getParameters($class, $method, $const, $other);

							BootMgr::method(get_class($class).'@'.$method, null);

							if (BootMgr::$BOOTMODE[get_class($class).'@'.$method] == CAN_CONTINUE)
							{
								Middleware::$waiting[$view][] = $class;

								return BootMgr::methodGotCalled(get_class($class).'@'.$method, call_user_func_array([$class, $method], $const));
							}

							return BootMgr::instance();
						}
						
						throw new \Exceptions\Middleware\MiddlewareException("Middleware Method '$className'->'$method' doesn't exist.");
					}

					return BootMgr::instance();
				}
			}
				
			throw new \Exceptions\Middleware\MiddlewareException("Middleware '$class' doesn't exist.");
		}

		// controller
		protected function browser($section, $callback)
		{
			$section = explode('.', strtolower($section));
			
			if (count($section) == 2)
			{
				$request = $section[0];
				$event = $section[1];

				$manager = function(&$content) use ($event, $callback){
					$this->browserManager($content, $event, $callback);
				};

				if (isset(self::$platform[$event]))
				{
					self::$platform[$event][$request] = $manager;
				}
			}
		}

		// access controller
		public static function __callStatic($cont, $args)
		{
			if ($cont == 'set')
			{
				Controller::$dropbox[$const] = $args[0];
			}
			else
			{
				$cont = ucfirst($cont);
				$path = env('bootstrap', 'controller.basepath') . '/' . $cont . '/main.php';

				if (file_exists($path))
				{
					$current = Bootloader::$currentClass;

					if (get_class($current) == $cont)
					{
						$class = $current;
					}
					else
					{
						include_once($path);

						$class = BootMgr::singleton($cont, $args);
					}

					$cs = BootMgr::singleton(\Controller\ControllerService::class, $class);
					
					if (BootMgr::$BOOTMODE[\Controller\ControllerService::class] == CAN_CONTINUE)
					{
						self::$noRenderPlease = true;

						return $cs;
					}

					return BootMgr::instance();
				}
				
				throw new \Exception("Controller '$cont' doesn't exist. Please check and try again.");
				
			}
		}

		// configure header and footer
		protected function customConfig($config = false)
		{
			// get type of config
			switch (gettype($config))
			{
				case 'array':
					// set custom config
					array_walk($config, function($val, $key){
						self::$appInstance->{$key} = $val;
					});
				break;

				case 'boolean':
					// set templete = true || false
					self::$appInstance->template = $config;
				break;
			}
		}

		// sitemap
		protected final function sitemap($settings = [])
		{
			$sitemap = Bootloader::boot('sitemap');
			
			if (is_callable($sitemap))
			{
				$reflection = new \ReflectionFunction($sitemap);
				$params = $reflection->getParameters();

				$config = [];

				// sitemap configuration
				array_map(function($obj) use (&$config, $sitemap){
					$param = new \ReflectionParameter($sitemap, $obj->name);
					$value = $param->getDefaultValue();
					$param = null;
					$config[$obj->name] = $value;
				}, $params);

				if (isset($config['activate']) && $config['activate'] === true)
				{
					$database = isset($config['database']) ? $config['database'] != 'default' ? $config['database'] : null : null;
					($database !== null) && DB::apply($database);

					$table = isset($config['db_table']) ? $config['db_table'] : 'generated_sitemap';
					$sitemap = $sitemap();

					if (!Table::exists($table))
					{
						Table::create($table, $sitemap['schema']);
					}

					if (Table::exists($table))
					{
						// ok manage
						$GET = Bootloader::$helper['ROOT_GET'];
						$map = isset($settings['map']) ? $settings['map'] : $GET[1];

						// check if exists
						$site = DB::get('map = ?')->bind($map)->table($table);

						$get = $_GET;
						$req = (isset($get['__app_request__']) && strlen($get['__app_request__']) > 1) ? $get['__app_request__'] : null;

						if (isset($get['__app_request__']))
						{
							unset($get['__app_request__']);
						}
						else
						{
							if(isset($_SERVER['REQUEST_QUERY_STRING']))
							{
								$_SERVER['REQUEST_QUERY_STRING'] = str_replace(url(), '', $_SERVER['REQUEST_QUERY_STRING']);
								$_SERVER['REQUEST_QUERY_STRING'] = trim($_SERVER['REQUEST_QUERY_STRING']);
								$_SERVER['REQUEST_QUERY_STRING'] = ltrim($_SERVER['REQUEST_QUERY_STRING'], '/');

								$req = rtrim(strip_tags(urldecode($_SERVER['REQUEST_QUERY_STRING'])), '/ ');
							}
						}

						if ($req === null)
						{
							$req = config('router.default.controller') . '/' . config('router.default.view');
						}

						// check if we have additional get request
						if (count($get) > 0)
						{
							$req .= '?' . http_build_query($get);
						}

						$url = isset($sitemap['url']) ? $sitemap['url'] : url();
						$baseurl = rtrim($url, '/') . '/' . $req;
						$loc = isset($settings['loc']) ? $settings['loc'] : $baseurl;

						$build = ['loc' => $loc, 'map' => $map, 'lastmod' => date('c',time()), 'priority' => '0.8', 'changefreq' => 'monthly'];
						array_each(function($val, $key) use (&$build){
							$build[$key] = $val;
						},$settings);

						if ($site->rows == 0)
						{
							// fresh
							$site->insert($build)->table($table);
						}
						else
						{
							// exists. update
							$build2 = $build;
							unset($build2['lastmod']);

							$check = $site->get($build2)->table($table);
							if ($check->rows == 0)
							{
								$build2 = null;

								// check if map exists
								$mapExists = $site->get('map = ?')->bind($map)->table($table);

								if ($mapExists->row > 0)
								{
									// update
									$mapExists->update($build)->where('map = :map')->bind($map)->table($table);
								}
								else
								{
									// add as a new row
									$mapExists->insert($build)->table($table);
								}
							}
						}
					}
				}
			}
		}

		protected function authentication($data, $params = [])
		{
			// get authentication class and method
			$data = explode('@', $data);
			// class and method. unpack
			list($handler, $method) = $data;

			// can we continue
			switch (Controller::$continue)
			{
				// yes
				case true:	
					// push to view
					Controller::$_auth = [$handler, $method, $params];
				break;

				// no
				case false:
					if (!Controller::$exceptFailed)
					{
						Controller::$continue = false;
					}
				break;
			}

			// call handler
			return Controller::$appInstance->authentication(implode('@',$data), $params);
		}

		protected function cache()
		{
			$this->app->cache();
			
			return $this;
		}

		// private platform manager
		private function browserManager(&$content, $type, $callback)
		{
			$tag = new Tag();
			$tag->domDocument = $content;
			call_user_func($callback, $tag);
			$content = $tag->domDocument;
		}
		
		// load css to view
		protected function loadCss($css)
		{
			$assets = self::$ControllerInstance->assets;
			$app = self::$appInstance;

			if (is_array($css) && count($css) > 0)
			{
				$cssArray = [];
				foreach ($css as $index => $fname)
				{
					if (preg_match('/^((http|https)[:](\/\/))|((\/\/))/i', $fname))
					{
						$cssArray[] = $fname;
					}
					else
					{
						$path = $assets->css($fname);
						if ($path != '')
						{
							$cssArray[] = $path;
						}
					}
				}

				if (count($cssArray) > 0)
				{
					self::$appInstance->liveStaticFiles['css'] = $cssArray;
				}
			}

			$app = null;
			$assets = null;

			return $this;
		}

		// load js to view
		protected function loadJs($js)
		{
			$assets = self::$ControllerInstance->assets;
			$app = self::$appInstance;

			if (is_array($js) && count($js) > 0)
			{
				$jsArray = [];
				foreach ($js as $index => $fname)
				{
					$config = [];

					if (is_array($fname))
					{
						Assets::$jsLoadConfig[$index] = $fname;
						$fname = $index;
					}

					if (preg_match('/^((http|https)[:](\/\/))|((\/\/))/i', $fname))
					{
						$jsArray[] = $fname;
					}
					else
					{
						$path = $assets->js($fname);
						if ($path != '')
						{
							$jsArray[] = $path;
						}
					}
				}

				if (count($jsArray) > 0)
				{
					self::$appInstance->liveStaticFiles['js'] = $jsArray;
				}
			}
			
			$app = null;
			$assets = null;

			return $this;
		}

		// load controller
		protected function load($controller)
		{
			$cln = get_class($this);

			if (strtolower($cln) != strtolower($controller))
			{
				$path = HOME . 'pages/' . ucfirst($controller) . '/main.php';
				
				// check if we are good
				if (file_exists($path))
				{
					// include class
					include_once($path);

					if (class_exists(ucfirst($controller)))
					{
						return BootMgr::singleton($controller, [], false);
					}

					throw new Exception("Class {$controller} doesn't exists.");
					
				}
					
				throw new Exception("Controller {$controller} wasn't found!");
				
			}

			return $this;
		}

		// load header
		public function loadHeader($controller=null)
		{
			if (is_null($controller))
			{
				$controller = get_parent_class($this->provider);
			}

			$path = env('bootstrap', 'controller.basepath') . '/' . $controller . '/Custom/header.php';

			if (file_exists($path))
			{
				View::$customHeaderPath = $path;
			}

			return $this;
		}

		// load footer
		public function loadFooter($controller=null)
		{
			if (is_null($controller))
			{
				$controller = get_parent_class($this->provider);
			}
			
			$path = env('bootstrap', 'controller.basepath') . '/' . $controller . '/Custom/footer.php';

			if (file_exists($path))
			{
				View::$customFooterPath = $path;
			}

			return $this;
		}

		// load custom
		public function loadCustom($controller=null)
		{
			if (is_null($controller))
			{
				$controller = get_parent_class($this->provider);
			}

			$this->loadHeader($controller)->loadFooter($controller);

			return $this;
		}

		// load static
		public function loadStatic()
		{
			$class = get_parent_class($this->provider);
			$args = func_get_args();

			if (count($args)==0)
			{
				// load css and js
				$path = env('bootstrap', 'controller.basepath') . '/' . $class . '/Static/' . $class;
				$css = $path . '.css';
				$js = $path . '.js';

				// add css
				app('app.css')->register($css);

				// add js
				app('app.js')->register($js);

			}
			else
			{
				array_walk($args, function($file) use ($class){
					
					// get extenstion
					$ext = extension($file);

					if (file_exists($file))
					{
						app('app.'.strtolower($ext))->register($file);
					}
					else
					{
						// search for it
						$dir = env('bootstrap', 'controller.basepath') . '/' . $class . '/Static/';
						$scan = deepScan($dir, $file);

						if (!is_null($scan) && strlen($scan) > 10)
						{
							app('app.'.strtolower($ext))->register($scan);
						}
					}

				});	
			}
		}

		// require js
		public function requirejs($js, $position='bottom')
		{
			$args = func_get_args();
			$position = is_array($position) ? (isset($args[2]) ? $args[2] : 'bottom') : $position;

			if (!is_callable($js))
			{
				if (isset($args[1]) && is_array($args[1]))
				{
					$base = basename($js);
					Assets::$jsLoadConfig[$base] = $args[1];
				}

				$this->loadRequire('js', $js, $position);
			}
			else
			{
				self::$assetPreloader[] = function() use ($js)
				{
					$scriptTag = [];
					$scriptTag[] = '<script type="text/javascript">';
					$controller = $this;

					$script = new class($scriptTag, $controller)
					{
						// scripttag
						private $scriptTag;
						// controller
						private $controller;
						// added vars
						private $varsAdded = false;

						// constructor
						public function __construct(&$scriptTag, $controller)
						{
							$this->scriptTag = &$scriptTag;
							$this->controller = $controller;
						}

						// insert
						public function insert($js, $export = [])
						{
							$path = $this->controller->loadRequire('js', $js, 'bottom', false);
							
							if (is_array($path))
							{
								// get content
								$content = file_get_contents($path['file']);

								if ($this->varsAdded === false)
								{
									// add php vars
									$dropbox = Controller::getDropbox();

									switch (count($export) > 0)
									{
										case true:

											// export data
											$exportData = [];

											foreach ($export as $key => $val)
											{
												if (is_int($key) && isset($dropbox[$val]))
												{
													$exportData[$val] = $dropbox[$val];
												}

												if (is_string($key))
												{
													$exportData[$key] = $val;
												}
											}

											$dropbox = $exportData;

										break;

										case false:
											unset($dropbox['packager'],
											$dropbox['loadAssets'],
											$dropbox['session'],
											$dropbox['model'],
											$dropbox['post'],
											$dropbox['system']);
										break;
									}

									$json = json_encode($dropbox);

									$content = 'var phpvars = '.$json.';'. $content;
									$this->varsAdded = true;
								}

								$packer = BootMgr::singleton(\Tholu\Packer\Packer::class, [$content, 'Normal', true, false, true]);
								
								if (BootMgr::$BOOTMODE[\Tholu\Packer\Packer::class] == CAN_CONTINUE)
								{
									$this->scriptTag[] = $packer->pack();
								}
								
							}
						}
					};

					call_user_func($js, $script);

					$scriptTag[] = '</script>';
					Assets::$jsScripts[] = implode("", $scriptTag);
				};
			}
			return $this;
		}

		// require js
		public function requirecss($css, $position='bottom')
		{
			$this->loadRequire('css', $css, $position);
			return $this;
		}

		// load require
		protected function loadRequire($type, $file, $position, $insert = true)
		{
			$req = null;

			// check if is a file
			if (preg_match('/^(http(s:|:))/', $file) || file_exists($file))
			{
				$req = ['position' => strtolower($position), 'file' => $file];
			}
			else
			{
				// controller
				$controller = ucfirst(Bootloader::$helper['active_c']);

				// check static 
				$static = env('bootstrap', 'controller.basepath') . '/' . $controller . '/Static/' . $file;

				if (file_exists($static))
				{
					$req = ['position' => strtolower($position), 'file' => $static];
				}
				else
				{
					// check public/assets/js
					$path = app('system.loadAssets')->{$type}($file);

					if ($path != '')
					{
						$req = ['position' => strtolower($position), 'file' => $path];
					}
				}
				
			}

			if (!is_null($req))
			{
				if ($insert)
				{
					self::$requireList[$type][] = $req;
				}
				else
				{
					return $req;
				}
			}
		}

		// model action
		protected function modelAction(string $action, $callback = null)
		{
			$action = strtolower($action);

			if (isset(Model::$http_raw_data[$action]))
			{
				$return = Model::$http_raw_data[$action];

				$args = !is_array($return) ? [$return] : $return;

				if (is_callable($callback))
				{
					call_user_func_array($callback, $args);
				}

				return $return;
			}

			return false;
		}

		// fix js position
		protected function fixJsPosition(string $javascript, array $configuration)
		{
			Assets::$changePosition['js'][$javascript] = $configuration;

			return $this;
		}

		// fix css position
		protected function fixCssPosition(string $css, array $configuration)
		{
			Assets::$changePosition['css'][$css] = $configuration;

			return $this;
		}

		// export dropbox as script
		public static function exportDropboxAsScript()
		{
			// get dropbox
			$dropbox = Controller::getDropbox();

			// remove some system data
			unset($dropbox['packager'], $dropbox['loadAssets'],
				  $dropbox['session'],	$dropbox['model'],
				  $dropbox['post'],		$dropbox['system'],
				  $dropbox['__js'],     $dropbox['cookie']);

			// create json string
			$dropboxAsJson = json_encode($dropbox);

			// build content line
			$content = 'var phpvars = '.$dropboxAsJson.';';

			// script tag
			$script = '<script>';

			// load packer
			$packer = BootMgr::singleton(\Tholu\Packer\Packer::class, [$content, 'Normal', true, false, true]);
								
			if (BootMgr::$BOOTMODE[\Tholu\Packer\Packer::class] == CAN_CONTINUE)
			{
				$script .= $packer->pack();
			}

			// end
			$script .= '</script>';

			// return script
			return $script;
		}

		// get dropbox
		public static function getDropbox()
		{
			// dropbox
			$dropbox = Controller::$dropbox;

			// get internal
			$dropboxInternal = dropbox();

			if (is_array($dropboxInternal))
			{
				// merge both
				$dropbox = array_merge($dropbox, $dropboxInternal);
			}

			return $dropbox;
		}
	}
}
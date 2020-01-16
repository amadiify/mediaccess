<?php

namespace Moorexa;

use Moorexa\DB as db;
use Moorexa\DB\Table;
use Moorexa\Event;
use utility\Classes\BootMgr\Manager as BootMgr;

/**
 * @package Moorexa Model Manager
 * @version 0.0.1
 * @author Ifeanyi Amadi
 */

// Model class for Data Management
class Model extends Controller
{
	public 		   $EH = null;
	public 		   $activedb = null;
	public 		   $modelData;
	public 		   $boot;
	public 		   $post = false;
	public 		   $caller = "Model";
	public  static $lastPost = [];
	private 	   $cachePreg = [];
	public 		   $table = "";
	public 	   	   $model;
	public 	   	   $modelObject = [];
	public  static $adb;
	public  static $_table;
	public 	static $middlewareArr = [];
	public 		   $structureTable = null;
	public 		   $structureStatus = 'failed';
	public  static $thirdparty_path = "";
	private 	   $options = ['get','delete','update','insert'];
	private 	   $optionsTriggered = false;
	private static $structure = null;
	public  static $cont = "";
	private static $instances = [];
	public 		   $switchdb = null;
	public  static $methods = ['delete', 'put', 'get', 'post'];
	private 	   $tempMemory = [];
	public 	   	   $instance = null;
	public 	   	   $controller = null;
	public		   $constructor = null;
	public  	   $tableData = []; 
	public	static $storageBox = [];	
	public		   $processID = 0;
	public  static $http_raw_data = [];
	protected 	   $usingRule = false;
	public  static $modelProps = [];


	public function __call($meth, $args)
	{
		// no argument passed?
		$NO_ARGS = count($args) == 0 ? true : false;

		//run update for a row
		if ($meth == 'update' and $NO_ARGS)
		{
			return $this->__update();
		}
		

		// run select for a row
		if ($meth == 'fetch' or $meth == 'get' and $NO_ARGS)
		{
			return $this->__fetch();
		}


		// run delete for a row
		if ($meth == 'remove' and $NO_ARGS)
		{
			return $this->__remove();
		}
		
		// get rules 
		if (isset($this->__rules__[$meth]))
		{
			if (!isset($args[0]))
			{
				return $this->getRule($meth);
			}
			else
			{
				$this->__rules__[$meth]['value'] = $args[0];

				// revalidate and remove from required
				$this->revalidate($meth, $args[0]);
			}
		}
		else
		{
			if (is_object($this->modelObject))
			{
				// return table data for model if pushed.
				if ($meth == $this->modelObject->table)
				{
					return (object) $this->tableData;
				}

				// check if closure function exists in temp storage
				if (isset($this->modelObject->tempMemory[$meth]) && is_callable($this->modelObject->tempMemory[$meth]))
				{
					$call = $this->modelObject->tempMemory[$meth];
					return call_user_func_array($call, $args);
				}
				else
				{
					if ($this->modelObject->optionsTriggered !== false || in_array($meth, $this->modelObject->options))
					{
						if ($this->modelObject->optionsTriggered === false)
						{	
							$instance = $this->modelObject->activedb;

							$instance->table = \Moorexa\DB::getTableName($this->table);

							$newClass = \Moorexa\DB\ORMReciever::getInstance($instance, 3);

							return call_user_func_array([$newClass, $meth], $args);
						}
					}
				}
			}

			$vars = ServiceManager::loadvars('sm-model.php');

			// get model props
			$props = Model::$modelProps;

			if (count($props) > 0)
			{
				$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
				$func = $trace['function'];
				$view = Bootloader::$helper['active_v'];

				$build = strtoupper($view . '/' . $func);

				if (isset($props[$build]))
				{
					// get callback
					$callback = $props[$build];
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
	}

	// check if a variable exists in temp storage
	public function has($name)
	{
		if (isset($this->modelObject->tempMemory[$name]))
		{
			return true;
		}

		return false;
	}

	// database schema
	protected function schema($callback)
	{
		$table = Table::create($this->structureTable, $callback);

		if ($table)
		{
			$this->structureStatus = 'Operation Successful.';
		} 
	}

	// get model variable
	public static function getModelVar($var)
	{
		$ins = self::$instances;

		if (count($ins) > 0)
		{
			foreach ($ins as $tag => $class)
			{
				$val = $class->{$var};

				if (!is_null($val))
				{
					return $val;
				}
			}
		}
	}

	// get model function
	public static function getModelFunc($meth, $args, $instance)
	{
		$ins = self::$instances;

		if (is_object($instance))
		{
			if (is_object($instance->modelObject))
			{
				$options = array_flip($instance->modelObject->options);

				if (isset($options[$meth]))
				{
					BootMgr::method(get_class($instance) . '@' . $meth, null);

					if (BootMgr::$BOOTMODE[get_class($instance) . '@' . $meth] == CAN_CONTINUE)
					{
						return BootMgr::methodGotCalled(get_class($instance) . '@' . $meth, call_user_func_array([$instance, $meth], $args));
					}
				}
			}
		}
		
		foreach ($ins as $key => $class)
		{
			if (method_exists($class, $meth))
			{
				Bootloader::$instance->getParameters($class, $meth, $const, $args);

				BootMgr::method(get_class($class) . '@' . $meth, null);

				if (BootMgr::$BOOTMODE[get_class($class) . '@' . $meth] == CAN_CONTINUE)
				{
					return BootMgr::methodGotCalled(get_class($class) . '@' . $meth, \call_user_func_array([$class, $meth], $const));
				}
			}
		}

		return null;
	}

	public static function __callStatic($meth, $args)
	{
		return self::getModelInstance($meth, $args);
	}

	// get model instance
	public static function getModelInstance($meth, $args, $ins=null)
	{
		$first = isset($args[0]) ? $args[0] : null;
		$cont = isset(BootLoader::$helper['active_c']) ? BootLoader::$helper['active_c'] : null;

		if (!is_null($ins) && is_object($ins))
		{
			$cont = get_class($ins);
		}

		if ($cont == null && self::$cont != "")
		{
			$cont = self::$cont;
		}

		if ($first !== null && is_string($first) && $first{0} == '\\')
		{
			$cont = substr($first, 1);
		}

		if ($cont == null && is_string($first))
		{
			$cont = $first;
		}

		$root = HOME;
		$cont = ucfirst($cont);

		if (strlen(Model::$thirdparty_path) > 0)
		{
			$root = Model::$thirdparty_path;
		}

		$path = deepScan(env('bootstrap', 'controller.basepath') . '/' . $cont . '/Models/', [$meth.'.php', ucfirst($meth).'.php']);

		$class = "Moorexa\\".ucfirst($meth);

		if (file_exists($path))
		{
			$model = BootMgr::singleton(Model::class);

			if (BootMgr::$BOOTMODE[Model::class] == CAN_CONTINUE)
			{
				$meth = basename($path);
				$meth = str_replace('.php','',$meth);

				$const = [];

				if (count(self::$storageBox) > 0)
				{
					$model->tempMemory = array_merge($model->tempMemory, self::$storageBox);
				}

				if ($first == LOAD_MODEL_CONSTRUCT)
				{
					unset($args[0]);
					$args = array_values($args);
				}

				include_once $path;

				if (\class_exists($class))
				{
					Bootloader::$instance->getParameters($class, '__construct', $const, $args);

					if (!isset(self::$instances[$class]))
					{
						$modelClass = Bootmgr::singleton($class, [], false);

						if (BootMgr::$BOOTMODE[$class] == CAN_CONTINUE)
						{
							$ref = new \ReflectionClass($class);
							

							$arr = toArray($modelClass);
											
							if (is_array($arr) && !isset($arr['table']))
							{
								$modelClass->table = db::getTableName($meth);
							}

							$db = isset($arr['switchdb']) && strlen($arr['switchdb']) > 1 ? $arr['switchdb'] : null;
							if ($db !== null)
							{
								$model->switchdb = $db;
							}

							$table = isset($arr['table']) && strlen($arr['table']) > 1 ? $arr['table'] : $meth;

							$dbClass = new db;
							$dbClass->table = db::getTableName($table);
							$dbClass->setConnectWith($db);

							$dbInstance = $dbClass->apply($db);

							$model->activedb = $dbInstance;
							$vars = \System::$local_vars;

							// create a page class instance
							$modelClass->activedb = $dbInstance;
							$modelClass->table = db::getTableName($table);
							$model->table = db::getTableName($table);
							$model->processID = uniqid();
							$model->controller = Bootloader::$currentClass;
							$model->tableData = $model->pullTableData();
							$modelClass->controller = $model->controller;
							$modelClass->modelObject = $model;

							Bootloader::extractVars($modelClass);

							if (count($vars) > 0)
							{
								foreach ($vars as $key => $val)
								{
									$modelClass->{$key} = $val;
								}
							}

							if (isset(Route::$controllerVars[$cont]))
							{
								$vars = Route::$controllerVars[$cont];

								foreach ($vars as $key => $val)
								{
									$modelClass->{$key} = $val;
								}
							}

							// set model class;
							self::$instances[$class] = $modelClass;
						}
					}
					else
					{
						$modelClass = self::$instances[$class];
					}

					// load setRules
					ApiModel::getSetRules($modelClass);

					$loadConstructor = false;

					if ($first !== null && $first == LOAD_MODEL_CONSTRUCT)
					{
						$loadConstructor = true;
					}
					elseif ($first !== null && $first != LOAD_MODEL_CONSTRUCT)
					{
						$loadConstructor = true;
					}

					if ($loadConstructor)
					{
						\call_user_func_array([&$modelClass, '__construct'], $const);

						$ref = new \ReflectionClass($modelClass);
						$class = get_class($modelClass);

						$props = $ref->getProperties();

						foreach($props as $i => $obj)
						{
							$name = $obj->getName();
							$class = $obj->getDeclaringClass();
							if ($obj->isPublic())
							{
								if ($class->getName() == get_class($modelClass))
								{
									Controller::$dropbox[$name] = $modelClass->{$name};
								}
							}
						}
					}
					
					return $modelClass;
				}

				return $model;

			}

			return BootMgr::instance();
		}
		else
		{
			Event::emit('model.error', "Invalid Model (${meth}.php) file requested for in ". 'pages/' . $cont . '/models/');
		}

		return false;
	}

	// match post data against table structure.
	public function getStructure($data)
	{
		$validData = [];

		$struct = Model::$structure;
		if (is_array($struct) || is_object($struct))
		{
			foreach ($data as $k => $v)
			{
				if (isset($struct[$k]) && !isset($validData[$k]))
				{
					$validData[$k] = $v;
				}
			}
		}
		$struct = null;

		return $validData;
	}

	public function middlewareData($key, $value)
	{
		Model::$middlewareArr[$key] = $value;
	}

	public function loadvars($path, $caller = false)
	{
		// if ($caller == 2)
		// {
		// 	$csm = new CSM();
		// 	$msm = new MSM();  
		// }

		$mw = Model::$middlewareArr;

		if (!isset($this->cachePreg[$path]))
		{
			include_once $path;

			// get variables
			$file = file_get_contents($path);
			preg_match_all('/([\n])[\$]{1}([^=]+)\s{0,}[=]{1}/', $file, $matches);
			
			$vars = [];

			if (count($matches) > 0)
			{
				if (isset($matches[1]))
				{
					foreach ($matches[2] as $in => $m)
					{
						$var = trim($m);
						$vars[$var] = $$var;
					}
				}
			}

			$this->cachePreg[$path] = $vars;
			return $vars;
		}
		else
		{
			return $this->cachePreg[$path];
		}
	}

	// get magic method
	public function __get($name)
	{
		// get rule
		$getRule = $this->getRule($name);
		
		if ($getRule !== null)
		{
			return $getRule;
		}

		if (is_object($this->modelObject))
		{
			if (array_key_exists($name, $this->modelObject->tempMemory))
			{
				return $this->modelObject->tempMemory[$name];
			}

			if (is_array($this->modelObject->tableData))
			{
				if (array_key_exists($name, $this->modelObject->tableData))
				{
					return $this->modelObject->tableData[$name];
				}
			}
		}

		if (in_array($name, $this->options) && $this->optionsTriggered === false)
		{
			$this->optionsTriggered = $name;

			return $this;
		}
		else
		{
			$vars = ServiceManager::loadvars('sm-model.php');

			// get model props
			$props = Model::$modelProps;

			if (count($props) > 0)
			{
				$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
				$func = $trace['function'];
				$view = Bootloader::$helper['active_v'];

				$build = strtoupper($view . '/' . $func);

				if (isset($props[$build]))
				{
					// get callback
					$callback = $props[$build];
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

			if (isset($vars[$name]))
			{
				return $vars[$name];
			}

			return null;
		}
	}

	// set
	public function __set($name, $val)
	{
		if (isset($this->__rules__[$name]))
        {
			$this->__rules__[$name]['value'] = $val;
			
			// revalidate and remove from required
            $this->revalidate($name, $val);
		}
		else
		{
			if ($this->usingRule)
			{
				$this->__rules__[$name]['default'] = $val;
				$this->__rules__[$name]['value'] = $val;
				$this->__rules__[$name]['rule'] = null;
			}
		}
		
		$this->tempMemory[$name] = $val;
		Controller::$dropbox[$name] = $val;
	}

	// @var $default : return size and type
	public function pullTableData($default = false)
	{
		$dir = HOME . 'Lab/Tables/' . $this->table . '.php';

		if (file_exists($dir))
		{
			include_once $dir;

			if (class_exists($this->table))
			{
				// create class instance
				$instance = BootMgr::singleton($this->table);

				if (method_exists($instance, 'up'))
				{
					$structure = BootMgr::singleton(Structure::class);

					if (BootMgr::$BOOTMODE[Structure::class] == CAN_CONTINUE)
					{
						$structure->queryInfo = [];

						$instance->up($structure);

						$info = $structure->queryInfo;

						$tableKeys = array_keys($info);

						if (!$default)
						{
							$flip = array_flip($tableKeys);
							array_each(function($val, $key) use (&$flip){
								$flip[$key] = '';
							},$flip);

							return $flip;
						}

						$table = [];

						foreach ($info as $key => $config)
						{
							$table[$key] = ['size' => $config[1], 'type' => $config[0]];
						}

						$instance = null;

						return $table;
					}

					return BootMgr::instance();
				}

				$instance = null;
			}
		}
		else
		{
			// get table structure
			$table = DB::getTableInfo($this->activedb);
			
			$tableKeys = array_keys($table);

			if (!$default)
			{
				$flip = array_flip($tableKeys);
				array_each(function($val, $key) use (&$flip){
					$flip[$key] = '';
				},$flip);

				return $flip;
			}

			return $table;
			
			
		}

		return [];
	}

	// set data for models
	// @var $data is an array
	public static function setData($data)
	{
		if (is_array($data))
		{
			foreach ($data as $key => $val)
			{
				self::$storageBox[$key] = $val;
			}
		}
	}

	// load model action
	public static function loadModelAction(&$controller, &$model, &$instance, &$setmodeltodefault, &$sys, &$modelInstance=null)
	{
		// check for form_request in session
		if (session()->has('form_request', $data))
		{
			$url = $sys->system->refUrl;

			if (is_array($data) && isset($data['on']))
			{
				$count = count(explode('/', $data['on']))-1;
				$toString = implode('/', $url);
				$matched = false;

				switch (strtolower($toString) == strtolower($data['on']))
				{
					case true:
						$matched = true;
					break;

					case false:
						$slice = array_splice($url, $count);
						$sliced = implode('/', $slice);

						if (strtolower($sliced) == strtolower($data['on']))
						{
							$matched = true;
						}
					break;
				}

				if ($matched)
				{
					// process
					$_SERVER['REQUEST_METHOD'] = $data['method']; // set request method
					if ($data['data'] > 0)
					{
						$_POST = $data['data'];
					}

					if ($data['query'] > 0)
					{
						$_GET = $data['query'];
					}

					session()->drop('form_request');
				}
			}
		}

		// check if post has REQUEST_VIEWMODEL
		// simply implies that the programmer wishes to handle this request by another model
		if ($sys->post->has('REQUEST_VIEWMODEL', $model)) {
			// remove it
			unset($_POST['REQUEST_VIEWMODEL']);
		}

		// check if view model is set to default
		if ($sys->post->has('SET_VIEWMODEL_DEFAULT')) {
			// ok
			$setmodeltodefault = true;
			// remove it
			unset($_POST['SET_VIEWMODEL_DEFAULT']);
		}

		// model class name
		$modelClass = ucfirst($model);

		// define model path
		$modelPath = env('bootstrap', 'controller.basepath') . '/' . $controller . '/Models/' . $modelClass . '.php';

		// check if controller model exists
		if (!file_exists($modelPath))
		{
			$controllerPath = env('bootstrap', 'controller.basepath') . '/' . $controller . '/Models/' . $controller . '.php';
			if (file_exists($controllerPath))
			{
				$modelPath = $controllerPath;
				$controllerPath = null;
				$modelClass = ucfirst($controller);
				$model = $controller;
			}
		}
		
		// load model if file exists
		switch (file_exists($modelPath))
		{
			case true:
				// include model 
				include_once $modelPath;

				// get request method
				// default is 'get'
				$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'get';

				switch (strtolower($method))
				{
					case 'get':
					case 'post':
					case 'delete':
					case 'put':
					case 'options':
					case 'patch':
						$method = strtolower($method);
					break;
				}
				
				// check for REQUEST_METHOD in POST data
				if ($sys->post->has('REQUEST_METHOD', $method))
				{
					$method = strtolower(strip_tags($method));
					// remove from post array
					unset($_POST['REQUEST_METHOD']);
				}

				// get url
				$url = $sys->system->refUrl;
				$url[1] = $model;

				$requests = array_splice($url, 1);
				$ended = count($requests)-1;

				// build request
				$build = '';

				// run request
				foreach ($requests as $i => $r)
				{
					// build request.
					if (preg_match('/([a-zA-Z_])/', $r))
					{
						$build .= $r . ' ';
					}
					else
					{
						$ended = $i;
						break;
					}
				}

				$build = ucwords(rtrim($build, ' '));
				$build = preg_replace("/\s*/", '',$build);

				// get all middleware waiting.
				$waiting = Middleware::$waiting;

				// get argument.
				$argument = isset($requests[1]) ? $requests[1] : null;

				// get third argument.
				$args = function($bind=null) use (&$method, &$argument)
				{
					if (!is_null($argument))
					{
						$return = $method;

						if ($bind !== null)
						{
							$args = func_get_args();
							$args = implode(" ", $args);
							$args = ucwords($args);
							$args = str_replace(" ",'',$args);

							$return .= $args;

							// #clean up
							$args = null;
						}

						$return .= ucfirst($argument);

						return $return;
					}

					return null;
				};

				// load any request.
				$any = function($title){
					if ($title != null)
					{
						return 'any'.ucfirst($title);
					}
				};	

				$applyArgument = function() use ($argument, $model)
				{
					if (is_string($argument) && strlen($argument) > 2)
					{
						return $argument.ucfirst($model);
					}
					
					return null;
				};

				// actions.
				
				/**
				 * examples:
				 * view => Home, argument => Ap, method => send, controller => App
				 * 
				 * anyAp
				 * anyHome
				 * send
				 * sendHome
				 * sendHomeAp
				 * sendAp
				 * sendHomeAp
				 * sendAppHomeAp
				 * sendAppAp
				 */

				$options = [
					$applyArgument(),
					$any($argument),
					$any($model),
					$any(uri()->view),
					$method,
					$method.ucfirst($model),
					$method.$build,
					$method.ucfirst(uri()->view),
					$args(),
					$args($model),
					$args(uri()->view),
					$args($controller,$model),
					$args($controller,uri()->view),
					$args($controller)
				];

				if ($argument == null)
				{
					array_unshift($options, ($method . 'Create' . ucfirst($model)));
					array_unshift($options, ($method . 'Create' . ucfirst(uri()->view)));
				}

				$options = array_unique($options);

				// using triggers
				$usingTriggers = false;

				// trigger func
				$triggerFuncArray = [];

				// get instance
				$modelInstance = Model::{$model}(LOAD_MODEL_CONSTRUCT);

				// check for triggers
				if (property_exists($modelInstance, 'triggers'))
				{
					$triggers = $modelInstance->triggers;
					if (is_array($triggers))
					{
						// check if there is a config for current view
						if (isset($triggers[uri()->view]))
						{
							$trigger = $triggers[uri()->view];

							if (is_array($trigger))
							{
								$paths = uri()->paths();
								$paths = array_flip(array_splice($paths, 2));

								if (count($paths) > 0)
								{
									foreach ($trigger as $key => $val)
									{
										if (is_string($key))
										{
											if (isset($paths[$key]))
											{
												$func = $val;
												$val = explode(':', $val);

												if (count($val) > 1)
												{
													list($methods, $func) = $val;

													// get methods
													$methods = explode('|', $methods);
													foreach ($methods as $mx => $pm)
													{
														$pm = strtolower($pm);

														if ($pm == $method)
														{
															// set triggers to true
															$usingTriggers = true;

															// set func array
															$triggerFuncArray[$func] = true;

															// push func to options
															array_push($options, $func);
															break;
														}
													}
												}
												else
												{
													// set triggers to true
													$usingTriggers = true;

													// set func array
													$triggerFuncArray[$func] = true;

													// push func to options
													array_push($options, $func);
												}
											}
										}
										else
										{
											if (isset($paths[$val]))
											{
												$val = uri()->view . ucfirst($val);

												// set func array
												$triggerFuncArray[$val] = true;

												// push func to options
												array_push($options, $val);
											}
										}
									}
								}
							}
						}
					}
				}

				// load option
				$anySeen = false;
				$loadOptions = true;

				// listen for when csrf.token error doesn't occur, then manage view model
				Event::on('csrf.error', function() use (&$loadOptions)
				{
					$loadOptions = false;
				});

				if ($loadOptions)
				{
					$httpMethod = $method;

					foreach ($options as $index => $method)
					{
						if ($method !== null)
						{
							if (method_exists($modelInstance, $method))
							{
								$canContinue = true;

								// now check for requestRule property
								if (property_exists($modelInstance, 'requestRule'))
								{
									if (isset($modelInstance->requestRule[$method]))
									{
										if (strtoupper($httpMethod) != strtoupper($modelInstance->requestRule[$method]))
										{
											// call model
											$canContinue = false;
										}
									}
								}

								
								// load model
								if ($canContinue)
								{
									$callfunc = function() use (&$method, &$model, &$instance, &$modelInstance, &$argument, &$sys, &$triggerFuncArray)
									{
										$args = [];

										$url = $sys->system->refUrl;
										$url[1] = $model;
										$start = 2;
										
										if (!is_null($argument))
										{
											if (stripos($method, $argument) !== false)
											{
												$start = 3;
											}
										}

										if (isset($triggerFuncArray[$method]))
										{
											$start = 3;
										}
										
										$other = array_splice($url, $start);
										$url = null;

										$instance->getParameters($modelInstance, $method, $args, $other);

										BootMgr::method(get_class($modelInstance) . '@' . $method, null);

										if (BootMgr::$BOOTMODE[get_class($modelInstance) . '@' . $method] == CAN_CONTINUE)
										{
											self::$http_raw_data[strtolower($method)] = call_user_func_array([&$modelInstance, $method], $args);
											
											// this method was called
											BootMgr::methodGotCalled(get_class($modelInstance) . '@' . $method, self::$http_raw_data[strtolower($method)]);
										
											$ref = new \ReflectionClass($modelInstance);

											$props = $ref->getProperties();

											// load properties
											array_walk($props, function($obj, $i) use (&$modelInstance){

												$name = $obj->getName();
												$class = $obj->getDeclaringClass();
												
												if ($obj->isPublic())
												{
													if ($class->getName() == get_class($modelInstance))
													{
														Controller::$dropbox[$name] = $modelInstance->{$name};
													}
												}
											});
										}

										//clean up
										$props = null;
										$ref = null;
										$args = null;

									};

									if (isset($waiting[$method]))
									{
										Middleware::callWaiting($waiting[$method], $callfunc);
									}
									else
									{
										$call = true;

										if (preg_match('/^(any)/', $method) !== 0)
										{
											if ($anySeen === false)
											{
												$call = true;
												$anySeen = true;
											}
											else
											{
												$call = false;
											}
										}

										if ($call)
										{
											$callfunc();
										}
									}

									if (preg_match('/^(any)/', $method) === 0)
									{
										break;
									}
								}
								else
								{
									break;
								}
							}
						}
					}
				}

				// #clean up
				$url = null;
				$requests = null;
				$ended = null;
				$any = null;
				$args = null;
				$argument = null;
				$options = null;

			break;
		}

		// #clean up
		$modelPath = null;
	}

	public function pushTableData()
	{
		$args = func_get_args();

		// get data
		$key = isset($args[0]) ? $args[0] : null;
		$binds = array_splice($args,1);

		$isArray = false;

		if (! ($key instanceof DBPromise))
		{
			if (is_array($key) || is_object($key))
			{
				$run = is_array($key) ? toObject($key) : $key;
				$isArray = true;
			}
			else
			{
				// table instance
				if (count($binds) > 0)
				{
					$run = $this->get()->where($key, $binds)->limit(0,1);
				}
				else
				{
					$run = $this->get($key)->limit(0,1);
				}
			}
		}
		else
		{
			$run = $key;
		}

		if ($isArray === false)
		{
			if ($run->rows > 0)
			{
				$this->tableData = $run->getPacked;
				$this->modelObject->tableData = $run->getPacked;
			}
		}
		else
		{
			$array = (array) $run;
			$this->tableData = $array;
			$this->modelObject->tableData = $array;
		}
	}

	// check for model table rows
	public function rows()
	{
		if (is_object($this->modelObject))
		{
			// current table
			$table = $this->modelObject->table;

			// check if table exists
			if (strlen($table) > 1)
			{
				$obj = $this->modelObject;

				$get = $this->get();

				return $get->rows;
			}
		}

		return 0;
	}

	// switch model
	public function model($name)
	{
		$name = explode('/', $name);

		if (count($name) == 2)
		{
			list($cont, $model) = $name;
			$defcont = BootLoader::$helper['active_c'];
			BootLoader::$helper['active_c'] = ucfirst($cont);
			$model = ucfirst($model);
			$model = self::getModelInstance($model,[]);
			BootLoader::$helper['active_c'] = $defcont;
		}
		else
		{
			$name = ucfirst(implode('/',$name));
			$model = self::getModelInstance($name,[]);
		}

		return $model;
	}

	// listen for state
	public function on($request, &$data=null)
	{
		$raw_data = self::$http_raw_data;
		// check if raw data exists
		if (isset($raw_data[$request]))
		{
			// get data
			$data = $raw_data[$request];

			// return data
			switch (gettype($data))
			{
				// boolean
				case 'boolean':
					return $data;
				break;

				// default
				default:
					return true;
			}
		}
		return false;
	}

	// generate tag
	protected function makeFromTag($tag, $config=[])
	{
		$line = '';
		$options = [];
		
		if (is_object($config))
		{
			unset($config->tag, $config->label);

			foreach ($config as $key => $val)
			{
				$options[] = $key.'="'.$val.'"';
			}
		}

		switch (strtolower($tag))
		{
			case 'input':
				$line = '<input '.implode(' ', $options).'/>';
			break;

			case 'textarea':
				$val = $config->value;
				unset($config->value);
				$line = '<textarea '.implode(' ', $options).'>'.($val).'</textarea>';
			break;

			case 'select':
				$line = '<select></select>';
			break;
		}

		return $line;
	}

	// generate form rule
	private function generateFormRule($callback, &$form, &$config)
	{
		$rules = $this->__rules__;

		if (count($rules) > 0)
		{
			foreach ($rules as $name => $data)
			{
				$object = (object)['name' => $name, 'value' => $this->getRule($name)];
				$object->tag = 'input';
				$object->type = 'text';
				$object->label = $name;

				// get rule
				$rule = $data['rule'];

				// get type
				if (is_string($rule))
				{
					if (strpos($rule, 'email'))
					{
						$object->type = 'email';
					}
					elseif (strpos($rule, 'number'))
					{
						$object->type = 'number';
					}
				}

				if (isset($config[$name]))
				{
					if (isset($config[$name]['tag']))
					{
						$object->tag = $config[$name]['tag'];
					}

					if (isset($config[$name]['type']))
					{
						$object->type = $config[$name]['type'];
					}

					if (isset($config[$name]['label']))
					{
						$object->label = $config[$name]['label'];
					}
				}

				if (!is_null($callback) && is_string($callback))
				{
					$call = [$this, $callback];

					if (function_exists($callback) && !method_exists($this, $callback))
					{
						$call = $callback;
					}

					$form[] = call_user_func($call, $object);
				}
				else
				{
					$line = '<section>'.PHP_EOL;
					$line .= '<label for="'.$object->name.'">'.$object->label.'</label>'.PHP_EOL;
					$line .= $this->makeFromTag($object->tag, $object).PHP_EOL;
					$line .= '</section>'.PHP_EOL;

					$form[] = $line;
				}
			}
		}
	}

	// get form
    public function createForm($callback = null, $config = [])
    {
		$form = [];	

		if (!is_null($callback))
		{
			if (is_string($callback) && (method_exists($this, $callback) || function_exists($callback)))
			{
				$this->generateFormRule($callback, $form, $config);
			}
			elseif (is_array($callback))
			{
				$this->generateFormRule(null, $form, $callback);
			}
		}
		else
		{
			$this->generateFormRule(null, $form);
		}
		
		return implode(PHP_EOL, $form);
	}
	
	// clear rules
	public function clear($key=null)
	{
		$rules = $this->__rules__;
		$data = null;
		
		if (count($rules) > 0)
		{
			if ($key === null)
			{
				foreach ($rules as $name => $config)
				{
					if (isset($config['default']))
					{
						$rules[$name]['default'] = null;
					}

					if (isset($config['value']))
					{
						$rules[$name]['value'] = null;
					}
				}
			}
			else
			{
				if (isset($rules[$key]))
				{
					$data = $this->getRule($key);

					// remove
					$rules[$key]['default'] = null;
					$rules[$key]['value'] = null;
				}
			}

			$this->__rules__ = $rules;
		}

		return $data;
	}
}
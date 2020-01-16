<?php

namespace Moorexa;

use Moorexa\Template;
use utility\Classes\BootMgr\Manager as BootMgr;
/**
 *
 * @package Moorexa App Views
 * @author  wekiwork <www.wekiwork.com>
 * @version 0.0.1
 * Contains
 * - HTML minifier
 * - javascript minifier
 * - css minifier
 * - view renderer & generator
 * - static compiler
 **/

class View extends Bootloader
{
	public 			$model;
	public  static  $render = 0;
	public  static  $javascripts = [];
	public  static  $outputJar = "";
	public  static  $cssfiles = [];
	public  static  $packagerJson = [];
	public 			$package;
	private static  $cachedContent = null;
	public  static  $hideheader = 0;
	public 			$header = true;
	public 			$footer = true;
	public 			$template = true;
	public  static  $output  = 0;
	public  static  $map = [];
	public  static  $cssbuild = [];
	public  static  $jsbuild = [];
	public 			$rendering = false;
	public 	static 	$eventStorage = [];
	public 	static 	$simulation = false;
	private static 	$minified = [];
	public 			$apptitle = "";
	public 			$external_path = "";
	public  static  $external_config = "";
	public  static  $modalbox = [];
	private 		$blankPage = false;
	private static	$jsbindata = [];
	private static	$jsbinCalled = 0;
	private static  $jsbinImport = [];
	public  $_default = false;
	public  $defaultHeader = false;
	public  $defaultFooter = false;
	public  static  $exceptionHandled = false;
	public  $loadCss = []; // sends a different css to the view. Ignores shrinke.map config.
	public  $loadJs = []; // sends a different js to the view. Ignores shrinke.map config.
	private $js_minified = [];
	// instance
	public  static $instance;

	// Memory box
	private $_memory_box = [];
	private $InterpolateContent = null;
	private $outputOriginal = null;
	private $cacheArray = [];
	public  $bundle = null;
	public  $errorTriggred = false;
	public	$controllerProvider = null;
	private $system = null;
	private $renderOutput = false;
	// current controller and view
	public $controller = null;
	public $view = null;
	// loaded partials 
	public static $partials = [];

	// live css, js
	public $liveStaticFiles = [];

	// save partials
	public $partialArray = [];

	// save csss
	public $cssArray = [];

	// custom header
	public static $customHeaderPath = null;

	// custom footer
	public static $customFooterPath = null;

	// add bucket
	public static $bucket = ['css' => [], 'js' => []];

	// live static
	private static $liveStatic = ['css' => [], 'js' => []];

	// view props
	public static $viewProps = [];

	/**
	 *
	 * @return void
	 * @author joesphi <www.joesphi.com>
	 * @param  none
	 *
	 **/

	public function __construct($sys=null)
	{
		// if $sys is not null then assign to $system
		$this->system = $sys;
		// set instance
		self::$instance = &$this;
		// load package.json to class var $this->package
		self::loadPackage();

		// jsbin directive
		Rexa::directive('preparejsbin', function(){
			// check if jsbin data was sent.
			if (count(self::$jsbindata) > 0)
			{
				// get jsbin data
				$jsbindata = 'window.onload = function(){let __importID = 0; '. implode('',self::$jsbindata) . '};';
				// load packer, from composer. Minify js first
				$packer = new \Tholu\Packer\Packer($jsbindata, 'Normal', true, false, true);
				// replace </body> with new script
				$js = '<script>'.$packer->pack().'</script></body>';
				// clean up
				$packer = null;
				$jsbindata = null;
				self::$jsbinImport = [];
				return $js;
			}
		});
	}

	// load package
	public static function loadPackage()
	{
		self::$instance->package = loadPackage();
	}

	// Handle Returned modelData
	final function getModelData()
	{
		$modelData = Bootloader::$modelData;

		return Model::modelData($modelData);
	}

	// RenderNew function
	final function renderNew($path, $data = null)
	{
		if (!preg_match("/(:\/\/)/", $path))
		{
			$c_path = explode("/", $path);
			$boot = BootLoader::$helper;
			
			$sim = View::$simulation;

			if (isset($boot['url']))
			{
				$boot = (object) $boot;
			}
			else
			{
				$boot = (object) ['url' => url()];
			}

			if (!isset(Bootloader::$helper['location.url']))
			{
				$get = isset($_GET['__app_request__']) ? $_GET['__app_request__'] : (isset($_SERVER['REQUEST_QUERY_STRING']) ? $_SERVER['REQUEST_QUERY_STRING'] : '/');
				Bootloader::$helper['location.url'] = explode('/', $get);
			}
			
			$uri = $_SERVER['REQUEST_URI'];
			$uri = ltrim($uri, '/');

			if ($uri != $path)
			{
				if(count($c_path) > 1)
				{
					$npath = "{$boot->url}/{$path}";
					$npath = str_replace("//$path", "/$path", $npath);

					$current = implode("/", Bootloader::$helper['location.url']);

					if ($current != $path)
					{
						if (!is_null($data))
						{
							session()->set('__RedirectDataSent', (is_object($data) || is_array($data) ? json_encode(toArray($data)) : $data));
							session()->set('__RedirectDataDestination', $path);
						}

						$url = Bootloader::$helper['location.url'];
						$url = is_array($url) ? implode('/', $url) : null;

						if (!is_null($url))
						{
							if ($path == $url)
							{
								$sim = true;
							}
						}

						if(!View::$simulation)
						{
							header("location: $npath");
						}

						View::$eventStorage['redirected'] = true;
						View::$eventStorage['redirected_path'] = $npath;
					}
				}
				else
				{	
					$npath = rtrim($boot->url,'/')."/$path";

					$url = Bootloader::$helper['location.url'];
					$url = is_array($url) ? in_array($path, $url) : null;

					if (!is_null($url))
					{
						if ($url)
						{
							$sim = true;
						}
					}

					if (!View::$simulation)
					{
						if (!is_null($data))
						{
							session()->set('__RedirectDataSent', (is_object($data) || is_array($data) ? json_encode(toArray($data)) : $data));
							session()->set('__RedirectDataDestination', $path);
						}

						header("location: {$npath}");
					}

					View::$eventStorage['redirected'] = true;
					View::$eventStorage['redirected_path'] = $npath;
				}
			}
			
		}
		else
		{
			header('location: '.$path);
		}
		
	}

	// js export
	final function jsExport($config)
	{
		$this->jsExportData = $config;

		return $this;
	}

	// jsbin function
	public function jsbin($data)
	{
		if (is_callable($data))
		{
			$func = new \ReflectionFunction($data);
			$filename = $func->getFileName();
			$startline = $func->getStartLine() - 1;
			$endline = $func->getEndLine();
			$length = $endline - $startline;

			$params = $func->getParameters();

			$source = file($filename);
			$func = implode("", array_slice($source, $startline, $length));

			$dropbox = Controller::$dropbox;
			$export = $this->jsExportData;

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
					$this->jsExportData = [];

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

			$begin = strpos($func, '{');
			$json = json_encode($dropbox);
			self::$jsbinImport[] = $json;

			$import = 'function import_variables(){let data = ['.implode(',', self::$jsbinImport).'][__importID]; __importID++; return data;}';
			
			$body = substr($func, $begin);
			if ($this->jsbinCalled == 0)
			{
				$body = "$import;(function({%caller})". trim($body);
			}
			else
			{
				$body = ";(function({%caller})". trim($body);
			}
			
			$body = rtrim($body, ';');
			$end = $body[strlen($body)-1];

			if ($end != ')')
			{
				$body .= ")({%caller});";
			}
			else
			{
				$body .= "({%caller});";
			}
			
			$body = preg_replace('/[$]\s{0}([\S]*?)(->)([\S]+)/', '$1.$3', $body);
			$body = preg_replace('/[.][$]\s{0}([\S]*?)/', '.$1', $body);
			preg_match_all('/[$]([a-zA-Z_]+)\s{0,}[=]{1}\s{0,}([\S]+)/', $body, $ma);
			if (count($ma) > 0 && count($ma[0]) > 0)
			{
				foreach ($ma[0] as $i => $x)
				{
					if (preg_match('/(==|\*=|\+=|===|!=|!==|\/=|-=|%=)/', $x))
					{
						$x = trim($x);
						$n = preg_replace('/^[$]/', '', $x);
						$body = str_replace($x, $n, $body);
					}
					else
					{
						$x = trim($x);
						$n = preg_replace('/^[$]/', 'var ', $x);
						$eq = strpos($n, '=');
						$vars = substr($n, 0, $eq+1);
						$quote = preg_quote($vars, '/');
						
						if (!preg_match("/($quote)/", $body))
						{
							$body = str_replace($x, $n, $body);
						}
						else
						{
							$n = preg_replace('/^[$]/', '', $x);
							$len = strlen(preg_replace('/^[$]/', 'var ', $x));
							$last = strrpos($body, $vars);

							$body = substr_replace($body, $n, $last, $len);
							
						}
					}
				}
			}

			$body = preg_replace('/[(]\s{0,}[$]{1}([^$|\s]+)/', '($1', $body);

			preg_match_all('/[(]\s{0,}[$]{2}([^$|\s|.|-]+)/', $body, $matches);
			
			$body = $this->jsbinImport($matches, $func, $filename, $body);

			$body = preg_replace('/[,]\s{0,}[$]{1}([^$|\s]+)/', ',$1', $body);

			preg_match_all('/[,]\s{0,}[$]{2}([^$|\s|.|-]+)/', $body, $matches);

			$body = $this->jsbinImport($matches, $func, $filename, $body);

			$body = preg_replace('/([\S]*?)(->)([\S]+)/', '$1.$3', $body);
			$body = preg_replace('/([}|\)])(->)/', '$1.', $body);
			$body = preg_replace('/([\s|\S]{1})[$]([a-zA-Z_]+)/', '$1$2', $body);

			preg_match_all('/([\S]*?)(->)([^\s|(|{|\[]+)/', $body, $ma);
			if (count($ma) > 0 && count($ma[0]) > 0)
			{
				foreach ($ma[0] as $i => $a)
				{
					$with = str_replace('->', '.', $a);
					$body = str_replace($a, $with, $body);
				}
			}

			if (count($params) > 0)
			{
				$names = [];
				foreach ($params as $i => $v)
				{
					$names[] = ltrim($v->name, '$');
				}

				$params = implode(',', $names);
				$body = str_replace('{%caller}', $params, $body);
			}
			else
			{
				$body = str_replace('{%caller}', '', $body);
			}

			self::$jsbindata[] = $body;
			self::$jsbinCalled++;

			return $body;
		}
	}

	// manages jsbin import for direct access.
	private function jsbinImport($matches, $func, $filename, $body)
	{
		if (count($matches) > 0 && count($matches[0]) > 0)
		{
			$view = Bootloader::$helper['active_v'];

			$controller = Bootloader::$helper['active_c'];

			if (method_exists($controller, $view))
			{
				$meth = new \ReflectionMethod($controller, $view);

				$startline = $meth->getStartLine() - 1;
				$endline = $meth->getEndLine();
				$length = $endline - $startline;

				$source = file($filename);
				$meth = implode("", array_slice($source, $startline, $length));

				$stop = strpos($meth, $func);

				$beforeFunc = substr($meth, 0, $stop);
				
				$chs = new CHS();

				$class = new $controller;

				foreach ($matches[1] as $i => $x)
				{
					$x = '$'.preg_replace('/[^$|a-zA-Z|0-9|-]/','',$x);
					// get last index
					$last = strrpos($beforeFunc, $x);
					
					if ($last > 0)
					{
						$last = trim(substr($beforeFunc, $last));
						$q = preg_quote($x);

						$last = preg_replace('/(=>)/','@@>',$last);
						$exp = explode('=', $last);
						$val = $exp[1];
						$val = str_replace('@@>', '=>', $val);

						// get data allocated to this memory
						$valEnd = strrpos($val, ';');
						$val = trim(substr($val, 0, $valEnd));

						if (preg_match('/^(\$this->)/', $val))
						{
							$string = $chs->loadThis($val, $class);
						}
						else
						{
							if (is_string($val) && strlen($val) > 0 && $val[0] == '$')
							{
								$var = ltrim($val, '$');

								if (isset(Controller::$dropbox[$var]))
								{
									$string = Controller::$dropbox[$var];
								}
								else
								{
									$string = null;
								}
							}
							else
							{
								if (preg_match('/([\S]*?)\s{0,}[\(]/', $val))
								{
									$string = $chs->loadFunc($val, $class);
								}
								else
								{
									$string = $val;
								}
							}
						}

						$data = null;

						if (is_string($string))
						{
							$string = preg_replace('/^[\'|"]/','',$string);
							$string = preg_replace('/[\'|"]$/','',$string);

							if (substr($string,0,1) != '[')
							{
								$data = '"'.$string.'"';
							}
							else
							{	
								if (preg_match('/(=>)/',$string))
								{
									$arr = stringToArray($string);
									$data = json_encode($arr);
								}
								else
								{
									$data = $string;
								}
								
							}
						}
						elseif (is_array($string))
						{
							$data = [$string];
						}
						elseif (is_object($string))
						{
							$data = json_encode(toArray($string));
						}
						else
						{
							$data = $string;
						}

						$string = null;

						
						$first = $matches[0][$i];
						$next = '$'.$x;
						$new = str_replace($next, $data, $first);
						$body = str_replace($first, $new, $body);
						$new = null;
						$next = null;
						$first = null;
					}
				}

				$chs = null;
				$class = null;
			}
		}

		return $body;
	}

	// load static json
	public function loadBundle()
	{
		if (is_null($this->bundle))
		{
			$loadStatic = config('loadStatic');
			
			$this->bundle = $loadStatic;

			if (is_null($loadStatic))
			{
				$path = PATH_TO_KERNEL . 'loadStatic.json';
				
				if (file_exists($path))
				{
					$content = file_get_contents($path);
					$json = json_decode(trim($content));
					$this->bundle = $json;
				}
			}
			
		}
	}

	// load header
	private function loadHeader(&$__css = [], &$assets, &$controller, &$headerFile = PATH_TO_HELPER .'noheader.php')
	{
		// can we include header
		switch ((
			$this->header && $this->template &&
			!$this->blankPage && !$this->default && !$this->defaultHeader
		))
		{
			case true:
				// load css
				$__css = $this->app_css($this->bundle->stylesheet);

				
				// get header path
				$headerFile = !empty($this->external_path) ? $this->external_path . 'pages/'.$controller.'/Custom/header.html' : env('bootstrap', 'controller.basepath') . '/'.$controller.'/Custom/header.html';

				// load custom header if found
				if (!is_null(self::$customHeaderPath))
				{
					$headerFile = self::$customHeaderPath;
				}

				// check if custom/header.php is set to default.
				if (file_exists($headerFile) && preg_match('/(@)\s{0}(setdefault)/m', file_get_contents($headerFile)) != true)
				{
					$headerFile = HOME . 'custom/header.html';
				}

				// get icon
				$this->package->icon = $assets->image($this->package->icon);

				// load template header if set
				if (!empty(Template::$header))
				{
					$headerFile = Template::$header;
				}
				
			break;

			// load noheader
			case false:
				if (!$this->blankPage && ($this->default || $this->defaultHeader) )
				{
					// load css
					$__css = $this->app_css($this->bundle->stylesheet);
					// set header
					$headerFile = PATH_TO_HELPER .'noheader.php';
				}
			break;
		}

		// always return true
		return true;
	}

	// load footer
	private function loadFooter(&$__js = [], &$controller, &$footerFile = PATH_TO_HELPER .'nofooter.php')
	{
		// can we include footer?
		switch ((
			$this->footer && $this->template &&
			!$this->blankPage && !$this->default && !$this->defaultFooter
		))
		{
			case true:
				// load js
				$__js = $this->app_js($this->bundle->scripts);
				// make js publicly availiable
				Controller::$dropbox['__js'] = $__js;
				// get header path
				$footerFile = !empty($this->external_path) ? $this->external_path . 'pages/'.$controller.'/Custom/footer.html' : env('bootstrap', 'controller.basepath') . '/'.$controller.'/Custom/footer.html';
				
				// load custom footer if found
				if (!is_null(self::$customFooterPath))
				{
					$footerFile = self::$customFooterPath;
				}

				// check if custom/header.php is set to default.
				if (file_exists($footerFile) && preg_match('/(@)\s{0}(setdefault)/m', file_get_contents($footerFile)) != true)
				{
					$footerFile = HOME . 'custom/footer.html';
				}

				// load template footer if set
				if (!empty(Template::$footer))
				{
					$footerFile = Template::$footer;
				}
				
				// push output for rendering
				$this->renderOutput = true;

			break;

			// load nofooter
			case false:
				if (!$this->blankPage && ($this->default || $this->defaultFooter) )
				{
					// load js
					$__js = $this->app_js($this->bundle->scripts);
					// make js publicly availiable
					Controller::$dropbox['__js'] = $__js;
					// set footer
					$footerFile = PATH_TO_HELPER .'nofooter.php';
				}
			break;
		}

		// always return true
		return true;
	}

	// load view
	private function loadView($render, &$controller, &$viewpath='', &$extension='.html', &$subpath='')
	{
		// get filename
		$fileArray = explode("/", $render);
		$filename = end($fileArray);

		// check if '.' exists in filename
		$filename = strrpos($filename, '.') !== false ? substr($filename, 0, strrpos($filename, '.')) : $filename;
		// get folder
		$folder = $controller;

		// determine size of fileArray, load subfolder if greater than 1
		switch (count($fileArray) == 1)
		{
			// load from <CONT>/views/
			case true:
				$filename = $fileArray[0];
			break;

			// load from <CONT>/views/<SUBPATH>/
			case false:
				// change folder
				if (substr($fileArray[0], 0, 2) == './')
				{
					$folder = preg_replace('/^(.\/)/', '', $fileArray[0]);
					unset($fileArray[0]);
				}
				// load filename
				$filename = implode('/', $fileArray);
			break;
		}

		// lets check for file extension on view
		if (strrpos($filename, '.') !== false)
		{
			$pos = strrpos($filename, '.');
			$extention = substr($filename, $pos);
			$filename = substr($filename, 0, $pos);

			// clean up
			$pos = null;
		}

		// get view path
		switch (!empty($this->external_path))
		{
			// versioning activated. view exists in utility/Version/
			case true:
				$directory = $this->external_path . '/pages/' . $folder . '/Views';
				// set path
				$viewpath = $directory. $subpath . '/' . $filename . $extension; 
			break;

			// view exists in pages/<CONT>/Views/
			case false:
				$directory = env('bootstrap', 'controller.basepath') . '/' . $folder . '/Views';
				// set path
				$viewpath = $directory . $subpath . '/' . $filename . $extension;
			break;
		}

		// here we can load subscribers.
		// #code!

		// lets check if view doesn't exists in path then create
		if (!file_exists($viewpath) && is_dir($directory) && strlen($filename) > 1)
		{
			// create directory or file
			$render = str_replace($filename, '', $render);
		
			// check if directory exists within $render
			if (strlen($render) > 1)
			{
				// right trim '/'
				$render = rtrim($render, '/');
				// define directory
				$directory = $directory . '/' . $render;
				// unformatted
				$directoryCopy = $directory;
				// format directory
				$directory = str_replace(' ', '/', ucwords(str_replace('/', ' ', $directory)));
				// create directory if doesn't exists
				if (!is_dir($directory) && strlen($directory) > 1)
				{
					// create directory
					mkdir($directory);
					// replace directory from view path to formatted path.
					$viewpath = str_replace($directoryCopy, $directory, $viewpath);
				}

				// clone render
				$subpath =& $render;
			}


			// create view if it doesn't exists
			if (!file_exists($viewpath))
			{
				// get view helper
				$helper = file_get_contents(PATH_TO_HELPER . 'bodyofview.txt');
				// set header, viewpath and extension
				$helper = str_replace("@@__path", $viewpath, $helper);
				$helper = str_replace("@@__view", Bootloader::$helper['active_v'], $helper);
				$helper = str_replace("@@__cont", $controller, $helper);
				$helper = str_replace("@@__filename", basename($filename), $helper);

				// check and create folder.
				$base = basename($viewpath);
				$dir = rtrim($viewpath, $base);

				if (!is_dir($dir))
				{
					mkdir($dir);
				}

				if (is_dir($dir))
				{
					// create view
					$fh = fopen($viewpath, 'w+');
					fwrite($fh, $helper);
					fclose($fh);
				}
				// clean up
				$fh = null;
				$helper = null;
			}
		}

		// clean up
		$filename = null;
		$folder = null;
		$fileArray = null;
		$directory = null;
		$directoryCopy = null;
	}

	// Render view
	public function render(&$render, &$flag = "", &$data = "", &$justView = false)
	{
		// render view only if render hasn't been called previously
		switch (!Controller::$rendering)
		{
			// render view
			case true:

			// check if json data passed or array sent for rendering
			$object = is_string($render) ? json_decode($render) : null;

			// the check.
			switch (is_object($object) || is_array($render) || is_object($render))
			{
				// render json data
				case true:
					// set content type
					header('Content-Type: application/json');
					// convert object to array
					$out = is_object($object) ? toArray($object) : $render;
					// render output
					echo json_encode($out, JSON_PRETTY_PRINT);
					// clean up
					$out = null;
					// prevent render being used again
					Controller::$rendering = true;
				break;

				// include header,footer and render view.
				case false:
					// set rendering to true
					Controller::$rendering = true;
				
					// check if error did not occur, then load view
					if (!$this->errorTriggred)
					{
						// load bundle
						$this->loadBundle();

						// get controller and view
						$this->system->system->unpackUrl($controller, $view);

						// make provider avaliable
						$thisProvider = $this->controllerProvider;

						// listen for viewWillLoad
						if (method_exists($thisProvider, 'viewWillLoad'))
						{
							Bootloader::getParameters($thisProvider, 'viewWillLoad', $___args);
							call_user_func_array([$thisProvider, 'viewWillLoad'], $___args);
						}

						// set page title
						$this->package->title = !empty($this->apptitle) ? $this->apptitle : $this->package->title;
					
						
						// make model available
						$thisModel = is_object(Bootloader::$currentClass) ? Bootloader::$currentClass->model : $this;

						
						// check if flag is an array of values
						Controller::$dropbox = is_array($flag) ? array_merge($flag, Controller::$dropbox) : Controller::$dropbox;

						// include view service manager
						$vars = ServiceManager::loadvars('sm-controller.php', 'sm-model.php', 'sm-view.php');

						if (count(View::$viewProps) > 0)
						{
							$props = View::$viewProps;
							$cont = strtoupper(Bootloader::$helper['active_c']);
							$rend = strtoupper($render);
							$build = $cont . '@' . $rend;

							if (isset($props[$build]) || isset($props[$cont]))
							{
								$callback = isset($props[$build]) ? $props[$build] : $props[$cont];

								$object = (object) [];

								call_user_func($callback, $object);

								$array = (array) $object;

								extract($array);
							}
						}

						// combine data
						Controller::$dropbox = array_merge(Controller::$dropbox, $vars);

						// extract flag
						if (is_array($flag))
						{
							Controller::$dropbox = array_merge(Controller::$dropbox, $flag);
						}

						
						// extract vars from dropbox..
						extract(Controller::$dropbox);


						// hide header and footer 
						switch (isset($_SERVER['RESPONSE_HIDE_TEMPLATE']))
						{
							// load blank page and hide template.
							case true:
								$this->template = false;
								$this->blankPage = true;
							break;
						}

						// load config from controller
						Controller::loadConfig($this->package);

						// get app title.
						$apptitle = $this->package->title;

						// get assets
						$assets = $this->system->loadAssets;

						// make asset avaliable to app
						$this->assets = $assets;

						// change path if requested.
						if (!empty($this->external_path))
						{
							// change path 
							$assets->change_paths(View::$external_config);
						}

						// hide template if error occured
						if (class_exists('\MoorexaErrorContainer') && count(\MoorexaErrorContainer::$errors) > 0)
						{
							$this->default = true;
							$this->blankPage = false;
						}

						
						// load static
						$__loadStatic = function() use ($render, $controller)
						{
							$base = basename($render);
							// remove extension
							if (strrpos($base, '.') !== false)
							{
								$base = substr($base, 0, strrpos($base, '.'));
							}

							// get path
							$controllerPath = env('bootstrap', 'controller.basepath') . '/' . $controller . '/Static/';

							// check if view is not equal to $render
							if (uri()->view != $render)
							{
								// load static css and javascript
								$staticCss = deepScan($controllerPath, [$base.'.css', ucfirst($base).'.css']);
								$staticJs = deepScan($controllerPath, [$base.'.js', ucfirst($base).'.js']);

								// Push Css file
								if (strlen($staticCss) > 10)
								{
									self::$liveStatic['css'][] = $staticCss;
								}

								// Push JS file
								if (strlen($staticJs) > 10)
								{
									self::$liveStatic['js'][] = $staticJs;
								}
							}
						};

						// call function
						$__loadStatic();

				
						// load header if $justView === false
						if (!$justView && $this->loadHeader($__css, $assets, $controller, $header))
						{
							// include header
							if (file_exists($header))
							{
								// make package avaliable
								$package = $this->package;
								$package->name = $apptitle;
								$package->url = implode('/', $this->system->system->refUrl);

								// cache path
								cacheOrLoadCache($header, $newpath, 'Headers');

								// header included.
								include $newpath;
							}
						}
						
						if (strpos($render, '<') === false)
						{
							// render error if occured.
							switch ((isset(env('error_codes')[$render])))
							{
								// render error
								case true:
									$error = env('error_codes', $render);
									// include error file.
									include_once PATH_TO_ERRORS."http_error.html";
									// #clean up
									$error = null;
								break;

								// render view
								case false:
									// load view
									$this->loadView($render, $controller, $loadViewPath);
									// view exists?
									if (file_exists($loadViewPath))
									{
										// cache path
										cacheOrLoadCache($loadViewPath, $newpath, 'Views');

										// view included.
										include_once $newpath;
									}
								break;
							}
						}
						else
						{
							echo $render;
						}
						
						// load footer if $justView === false
						if (!$justView && $this->loadFooter($__js, $controller, $footer))
						{
							// include footer
							if (file_exists($footer))
							{
								// cache path
								cacheOrLoadCache($footer, $newpath, 'Footers');

								// footer included.
								include_once $newpath;
							}
						}

						// clean up
						$thisModel = null;
						$thisProvider = null;

						// load authentication handler
						if (!$this->rendering && Controller::$continue)
						{
							$auth = Controller::$_auth;
							$this->auth($auth[0], $auth[1]);
							// clean up
							$auth = null;
						}
						
					}
				break;

			}
			// clean up
			$object = null;
			break;
		}

		return $this;
	}

	// unpack css
	final public function app_css($Css = "")
	{
		if ($Css == "")
		{
			$Css = $this->bundle->stylesheet;
		}

		if (count($this->loadCss) > 0)
		{
			$Css = $this->loadCss;
		}

		$assets = new Assets();

		$url = url();

		// get view
		$view = !is_null($this->view) ? $this->view : config('router.default.view');

		// get controller
		$cont = !is_null($this->controller) ? $this->controller : config('router.default.controller');

		$liveCss = [];

		if ($cont != null)
		{	
			$cont_css = deepScan(env('bootstrap', 'controller.basepath') . '/' . $cont . '/Static/', [$cont.'.css', $cont.'.cxx.css', ucfirst($cont).'.css']);
			
			if (file_exists($cont_css) && strlen(file_get_contents($cont_css)) > 3)
			{
				$liveCss[] = url(ltrim($cont_css, HOME));
			}
		}

		$viewCss = deepScan(env('bootstrap', 'controller.basepath') . '/' . $cont . '/Static/', [$view.'.css', $view.'.cxx.css', ucfirst($view).'.css']);

		$shrinkCont = is_dir(PATH_TO_CSS . $cont) ? PATH_TO_CSS . $cont : null;

		if (file_exists($viewCss))
		{
			if (strlen(file_get_contents($viewCss)) > 3)
			{
				if (strpos(basename($viewCss), '.cxx.css') === false)
				{
					$liveCss[] = url(ltrim($viewCss, HOME));
				}
				else
				{
					// cache
					$liveCss[] = url(ltrim($this->getCssCached($viewCss), HOME));
				}
			}
		}

		if (count(self::$liveStatic['css']) > 0)
		{
			$liveCss = array_merge($liveCss, self::$liveStatic['css']);
		}

		// apply bucket
		if (count(self::$bucket['css']) > 0)
		{
			$liveCss = array_merge(self::$bucket['css'], $liveCss);
		}

		if (isset($this->liveStaticFiles['css']))
		{
			$_css = $this->liveStaticFiles['css'];
			$_css = array_merge($_css, $liveCss);
			$liveCss = $_css;
		}

		$requireCss = Controller::$requireList['css'];
		$this->unpackRequire($requireCss, $liveCss);

		$bundle = 'stylesheet@bundle';

		// check bundle
		if (isset($this->bundle->{$bundle}))
		{
			$bundles = $this->bundle->{$bundle};

			if (count($bundles) > 0)
			{
				foreach ($bundles as $bundle)
				{
					View::$cssfiles[] = $assets->css($bundle);
				}
			}
		}
		else
		{
			if(isset($Css[0]) && !empty($Css[0]))
			{
				foreach($Css as $key => $val)
				{
					$val = trim($val);

					if (file_exists($val))
					{
						View::$cssfiles[] = $val;
					}
					else
					{

						if(preg_match("/^[http|https]+[:\/\/]/", $val) == true)
						{
							// lets get the filename
							View::$cssfiles[] = $val;
						}
						else
						{
							$path = $assets->css($val);

							if ($path != "")
							{
								View::$cssfiles[] = $path;
							}

						}
					}
				}

				$files = Linker::getFiles();

				$cssp = rtrim(PATH_TO_CSS, '/');

				if (isset($files[$cssp]))
				{
					$files = $files[$cssp];

					foreach ($files as $i => $fcss)
					{
						if (file_exists($fcss))
						{
							View::$cssfiles[] = $fcss;
						}
					}
				}
				
			}
		}

		View::$cssfiles = array_merge(View::$cssfiles, $liveCss);

		return View::$cssfiles;
	}

	// unpack require
	private function unpackRequire($require, &$data)
	{
		$top = [];
		$bottom = [];
		$before = [];

		// push to top and bottom
		array_walk($require, function($req) use (&$top, &$bottom, &$before){
			$pos = $req['position'];
			if ($pos == 'top')
			{
				$top[] = $req['file'];
			}
			elseif ($pos == 'bottom')
			{
				$bottom[] = $req['file'];
			}
			else
			{
				$pos = trim($pos);
				if (preg_match('/^(before)\s+([\S]*)/', $pos, $match))
				{
					$before[] = [$match[2] => $req['file']];
				}
				else
				{
					$bottom[] = $req['file'];
				}
			}
		});	

		// stack on top
		if (count($top) > 0)
		{
			$len = count($top)-1;

			for ($i=$len; $i != -1; $i--)
			{
				array_unshift($data, $top[$i]);
			}
		}

		// stack below
		if (count($bottom) > 0)
		{
			foreach ($bottom as $i => $fl)
			{
				array_push($data, $fl);
			}
		}

		// stack before
		if (count($before) > 0)
		{
			foreach ($before as $i => $pathData)
			{
				$keys = array_keys($pathData);

				// get position in data
				foreach ($data as $index => $line)
				{
					// get base
					$base = basename($line);
					// quote
					$quote = preg_quote($keys[0], '/');
					if (preg_match("/($quote)/i", $base) || ($keys[0] == $line))
					{
						array_splice($data,$index,1,[$pathData[$keys[0]],$line]);
						break;
					}
				}
			}
		}

		// clean
		$top = null;
		$bottom = null;
		$before = null;
	}

	// unpack js
	final public function app_js($Js)
	{
		if (count($this->js_minified) == 0)
		{
			$this->js_int();
		}

		return $this->js_minified;
	}

	// get javascripts paths.
	private function getJsPaths(&$clean=[], &$livejs=[])
	{
		$Js = [];

		if (isset($this->bundle->scripts) && is_array($this->bundle->scripts))
		{
			$Js = $this->bundle->scripts;
		}

		$url = url();

		// get view
		$view = !is_null($this->view) ? $this->view : config('router.default.view');

		// get controller
		$cont = !is_null($this->controller) ? $this->controller : config('router.default.controller');

		if (count($this->loadJs) > 0)
		{
			$Js = $this->loadJs;
		}

		$cont_js = deepScan( env('bootstrap', 'controller.basepath') . '/' . $cont . '/Static/', [$cont.'.js', ucfirst($cont).'.js']);

		if (file_exists($cont_js) && strlen(file_get_contents($cont_js)) > 2)
		{
			$livejs[] = url(ltrim($cont_js, HOME));
		}

		$url = BootLoader::$helper['url'];

		$viewJs = deepScan(env('bootstrap', 'controller.basepath') . '/' . $cont . '/Static/', [$view.'.js', ucfirst($view).'.js']);

		if (file_exists($viewJs))
		{
			if (strlen(file_get_contents($viewJs)) > 2)
			{
				$livejs[] = url(ltrim($viewJs, HOME));
			}
		}

		$livejs = is_null($livejs) ? [] : $livejs;

		if (count(self::$liveStatic['js']) > 0)
		{
			$livejs = array_merge($livejs, self::$liveStatic['js']);
		}

		// apply bucket
		if (count(self::$bucket['js']) > 0)
		{
			$livejs = array_merge(self::$bucket['js'], $livejs);
		}

		if (isset($this->liveStaticFiles['js']))
		{
			$_js = $this->liveStaticFiles['js'];
			$_js = array_merge($_js, $livejs);
			$livejs = $_js;
		}

		$requireJs = Controller::$requireList['js'];
		$this->unpackRequire($requireJs, $livejs);

		$minified = [];


		$this->assets->resetPath();

		$assets = $this->assets;

		$bundle = 'scripts@bundle';

		// check bundle
		if (isset($this->bundle->{$bundle}))
		{
			$bundles = $this->bundle->{$bundle};

			if (count($bundles) > 0)
			{
				foreach ($bundles as $bundle)
				{
					$minified[] = $assets->js($bundle);
				}

				return $minified;
			}
		}

		if(isset($Js[0]) && !empty($Js[0]))
		{
			foreach($Js as $key => $val)
			{
				$val = trim($val);

				if (file_exists($val))
				{
					$minified[] = url($val);
					$clean[] = url($val);
				}
				else
				{
					if(preg_match("/^[http|https]+[:\/\/]/", $val) == true)
					{
						// lets get the filename
						$filename = explode("/", $val);
						$filename = end($filename);

						$minified[] = $val;
					}
					else
					{

						$path = $assets->js($val);

						if ($path != '')
						{
							$minified[] = $path;
							$clean[] = $path;
						}
					}
				}

			}
		}

		$files = Linker::getFiles();

		$jsp = rtrim(PATH_TO_JS, '/');

		if (isset($files[$jsp]))
		{
			$files = $files[$jsp];

			foreach ($files as $i => $fjs)
			{
				if (file_exists($fjs))
				{
					$minified[] = $fjs;
					$clean[] = $fjs;
				}
			}
		}

		return $minified;
	}

	// load js
	public function js_int()
	{
		// lock file
		$lockfile = [];

		if (file_exists(PATH_TO_KERNEL . 'loadStatic.lock'))
		{
			$obj = json_decode(file_get_contents(PATH_TO_KERNEL . 'loadStatic.lock'));

			if (is_object($obj))
			{
				$lockfile = $obj->script;
			}
		}

		$javascripts = $this->getJsPaths($_javascripts, $livejs);
		
		if (is_array($livejs))
		{
			$javascripts = array_merge($javascripts, $livejs);
		}

		$this->js_minified = $javascripts;
	}

	// unpack assets
	public function unpack()
	{
		// unpack css
		switch (count(View::$cssfiles) > 0)
		{
			case true:
				$this->loadCss = View::$cssfiles;
				// clean
				View::$cssfiles = [];
			break;
		}

		// unpack javascript
		switch (count(View::$javascripts) > 0)
		{
			case true:
				$this->loadJs = View::$javascripts;
				// clean
				View::$javascripts = [];
			break;
		}
	}

	// load partials
	public static function partial($name, $___data=[])
	{
		// check from another controller
		$path = null;

		// partial directory
		$partialDirectory = null;

		$options = [];
		
		// get file name
		$filename = basename($name);
		$filename = preg_replace('/([^a-zA-Z\-\0-9\.])/', '', $filename);

		// get extension if sent.
		if (strpos($filename, '.') !== false)
		{
			// get extension
			$options = [$filename, ucfirst($filename)];
		}
		else
		{
			// load options
			$options = [$filename.'.html', ucfirst($filename). '.html'];
		}
			
		// check for an absolute path
		if (strlen($name) > 1 && $name[0] == '/')
		{
			$name = substr($name, 1);
			$path = $name;
		}
		else
		{
			if ($name != '' && strlen($name) > 1)
			{
				// check
				if (strpos($name, '@') !== false)
				{
					$controller = substr($name, 0, strpos($name, '@'));
				
					if (is_dir($controller))
					{
						// remove dir from string
						$name = strstr($name, '@');
						$name = ltrim($name, '@');
						
						// load path
						$path = deepScan($controller, $options);

						// save directory
						$partialDirectory = $controller;
					}
					else
					{
						// remove controller from string
						$name = strstr($name, '@');
						$name = ltrim($name, '@');
						
						$path = deepScan(env('bootstrap', 'controller.basepath') . '/'. ucfirst($controller) . '/Partials/', $options);
					}
				}
				else
				{
					if (file_exists($name))
					{
						$path = $name;
					}
					else
					{
						// get current controller
						$controller = !is_null(self::$instance->controller) ? self::$instance->controller : config('router.default.controller');
						// get path
						$path = deepScan(env('bootstrap', 'controller.basepath') . '/'. ucfirst($controller) . '/Partials/', $options);

						// check main partials
						if (is_null($path) || strlen($path) < 4)
						{
							$path = deepScan(PATH_TO_PARTIAL, $options);
						}
					}
				}
			}
		}
		
		if ($path != '' && strlen($path) > 1 && file_exists($path))
		{
			$th =& self::$instance;
			$output = file_get_contents($path);
			// read markdown
			if (strpos(basename($path), '.md') !== false)
			{
				// read markdown
				View::readMarkDown($output);
			}
			$useName = md5($path).'_'.basename($path);

			$other = null;

			// hash path
			if ($th->cachePartial($output, $getpath, $useName) === false)
			{
				$th->outputOriginal = $output;
				$th->interpolate($output, $content);
				$th->InterpolateContent = $content;

				// auto caching enabled?
				if (env("bootstrap", "enable.caching"))
				{
					$th->cachePartial(null, $other, $useName);
					$th->outputOriginal = null;
					$th->InterpolateContent;
				}
			}

			// load partial class if found.
			$__base = basename($path);
			$className = substr($__base, 0, strrpos($__base, '.'));
			$originalName = $className;
			$partialClassFile = rtrim($path, $__base) . $className . '.php';
			$className = ucwords(str_replace('-',' ',$className));
			$className = str_replace(' ','', $className);
			// partial class
			$partialClass = (object)['name' => $className, 'file' => $partialClassFile, 'originalName' => $originalName];

			if (file_exists($partialClass->file))
			{
				$_output = function() use ($___data, $th, $getpath, $partialClass)
				{
					BootMgr::method('partial@'.$partialClass->originalName, null);

					if (BootMgr::$BOOTMODE['partial@'.$partialClass->originalName] == CAN_CONTINUE)
					{
						// extract vars
						extract(Controller::getDropbox());
						
						if (is_string($___data))
						{
							// try convert to object
							$__data = preg_replace('/[\']/','"',$___data);
							$__obj = json_decode($__data);
							if (is_object($__obj))
							{
								$___data = toArray($__obj);
							}
							else
							{
								// try convert to array
								$__arr = stringToArray($__data);
								if (is_array($__arr))
								{
									$___data = $__arr;
								}
							}
						}

						if (is_array($___data))
						{
							extract($___data);
						}

						include_once $partialClass->file;

						// check for class
						if (class_exists($partialClass->name))
						{
							$__classname = strtolower($partialClass->name);
							$__classname2 = $partialClass->name;
							$ref = new \ReflectionClass($partialClass->name);
							
							$ref = $ref->newInstanceArgs($___data);

							// create instance
							$$__classname = $ref;
							$$__classname2 = $$__classname;
							$ref = null;
						}

						// make provider avaliable
						$thisProvider = $th->controllerProvider;

						// make model available
						$thisModel = is_object(Bootloader::$currentClass) ? Bootloader::$currentClass->model : $th;

						// make assets avaliable
						$assets = $th->assets;

						// make package avialiable
						$package = $th->package;

						$output = null;

						// load cached file.
						if ($getpath != null && file_exists($getpath))
						{
							ob_start();
							include($getpath);
							$output = ob_get_contents();
							ob_clean();
						}

						return $output;
					}

					return null;
				};

				$output = call_user_func($_output);
			}
			else
			{
				$_output = function() use ($___data, $th, $getpath, $partialClass)
				{
					BootMgr::method('partial@'.$partialClass->originalName, null);

					if (BootMgr::$BOOTMODE['partial@'.$partialClass->originalName] == CAN_CONTINUE)
					{
						// extract vars
						extract(Controller::getDropbox());
						
						if (is_string($___data))
						{
							// try convert to object
							$__data = preg_replace('/[\']/','"',$___data);
							$__obj = json_decode($__data);
							if (is_object($__obj))
							{
								$___data = toArray($__obj);
							}
							else
							{
								// try convert to array
								$__arr = stringToArray($__data);
								if (is_array($__arr))
								{
									$___data = $__arr;
								}
							}
						}

						if (is_array($___data))
						{
							extract($___data);
						}

						// make provider avaliable
						$thisProvider = $th->controllerProvider;

						// make model available
						$thisModel = is_object(Bootloader::$currentClass) ? Bootloader::$currentClass->model : $th;

						// make assets avaliable
						$assets = $th->assets;

						// make package avialiable
						$package = $th->package;

						$output = null;

						// load cached file.
						if ($getpath != null && file_exists($getpath))
						{
							
							ob_start();
							include($getpath);
							$output = ob_get_contents();
							ob_clean();
							
						}

						return $output;
					}

					return null;
				};

				$output = call_user_func($_output);
			
			}

			return $output;
		}
		else
		{
			if (strlen($name) > 1)
			{
				// throw exception
				$throw = true;

				if (Bootloader::boot('autogenerate.partials') === true)
				{	
					$nameCopy = $name;

					// get extension
					$ext = explode('.', basename($name));
					$ext = end($ext);
					if ($ext == basename($name))
					{
						$nameCopy .= '.html';
					}

					// create if it doesn't exists
					if ($nameCopy[0] == '/')
					{
						if (!file_exists($nameCopy))
						{
							//var_dump($nameCopy);
						}
					}
					else
					{
						// get current controller
						$controller = !is_null(self::$instance->controller) ? self::$instance->controller : config('router.default.controller');
							
						// current dir
						$_p = $partialDirectory != null ? $partialDirectory : env('bootstrap', 'controller.basepath') . '/' . $controller . '/Partials/';
						
						if (is_dir($_p))
						{
							$throw = false;

							// create partial.
							if ($name[0] != '$')
							{
								File::write('#Partial Created', $_p.$nameCopy);
								// load partial
								return self::partial($name, $___data);
							}
						}
						else
						{
							$throw = true;
						}
					}
				}

				if ($throw)
				{
					throw new \Exception('Partial '.$name.' doesn\'t exists.');
				}
			}
		}
	}

	// load partial as a directive
	public static function loadPartial($partialName)
	{
		// partial name
		$partialArgs = [];

		$args = func_get_args();

		$partialArgs = array_splice($args, 1);

		$newArgs = $partialArgs;

		if (count($partialArgs) > 0)
		{
			if (is_array($partialArgs[0]))
			{
				$newArgs = $partialArgs[0];
			}
		}

		$load = self::partial($partialName, $newArgs);

		// return partial
		return $load;
	}

	// cache partial
	final public function cachePartial($content = null, 
		&$cache_path = null,
		$cache_name)
	{
		$path = PATH_TO_STORAGE . 'Caches/Partials/partial.cache.php';

		$cache = include_once($path);

		$savepath = function($name) use (&$saveTo)
		{
			return PATH_TO_STORAGE . 'Caches/Partials/' . $name . '.cache'; 
		};

		if (is_array($cache))
		{
			$this->partialArray = $cache;
		}
		else
		{
			$cache = $this->partialArray;
		}

		$saveCache = function($name, $content) use ($savepath, $path){
			$hash = md5($this->outputOriginal);

			if (is_null($this->InterpolateContent))
			{
				$this->interpolate($content);	
			}

			$this->partialArray[$name] = $hash;

			ob_start();
			var_export($this->partialArray);
			$arr = '<?php'."\n";
			$arr .= 'return '. ob_get_contents() . ';'."\n";
			$arr .= '?>';
			ob_clean();

			File::write($arr, $path);
			File::write($content, $savepath(str_replace('/', '', $name)));
		};

		$cache_path = $savepath(str_replace('/','',$cache_name));

		if (!is_null($content))
		{
			$hash = md5($content);
			
			if (isset($cache[$cache_name]))
			{
				$hash2 = $cache[$cache_name];

				if ($hash2 == $hash)
				{
					$cache_path = $savepath(str_replace('/','',$cache_name));
					return true;
				}
				else
				{
					// save cache.
					if (strlen($content) > 0)
					{
						$saveCache($cache_name, $content);
					}
				}
			}
		}
		else
		{
			// save cache
			if (strlen($this->InterpolateContent) > 0)
			{
				$saveCache($cache_name, $this->InterpolateContent);
			}
		}
		

		return false;
	}

	// read markdown
	public static function readMarkDown(&$content)
	{
		// run parsedown
		$content = \Parsedown::instance()->text($content);
	}

	// get cached css path
	final private function getCssCached($cssfile)
	{
		$output = file_get_contents($cssfile);
		$other = null;

		// hash path
		if ($this->cacheCss($output, $getpath, basename($cssfile)) === false)
		{
			$this->outputOriginal = $output;
			CHS::interpolateText($output);
			$this->InterpolateContent = $output;

			$this->cacheCss(null, $other, basename($cssfile));
			$this->outputOriginal = null;
			$this->InterpolateContent;
		}

		$base = basename($cssfile);
		$file = PATH_TO_STORAGE . 'Caches/Css/'. substr($base, 0, strpos($base, '.')) . '.cache.css';
		if (!file_exists($file))
		{
			// create file
			file_put_contents($file, '');
		}
		
		// read getpath
		// start buffer
		ob_start();

		// extract vars
		extract(Controller::$dropbox);

		// make provider avaliable
		$thisProvider = $this->controllerProvider;

		// make model available
		$thisModel = is_object(Bootloader::$currentClass) ? Bootloader::$currentClass->model : $this;

		// make assets avaliable
		$assets = $this->assets;

		// load cached file.
		if ($getpath != null && file_exists($getpath))
		{
			include_once($getpath);
		}

		$output = ob_get_contents();
		ob_clean();

		// save css
		file_put_contents($file, $output);

		$base = null;

		return $file;
	}

	// cache css
	final private function cacheCss($content, &$cache_path=null, $cache_name=null)
	{
		$path = PATH_TO_STORAGE . 'Caches/Css/css.cache.php';

		$cache = include_once($path);

		$savepath = function($name)
		{
			return PATH_TO_STORAGE . 'Caches/Css/' . $name . '.php'; 
		};

		
		if (is_array($cache))
		{
			$this->cssArray = $cache;
		}
		else
		{
			$cache = $this->cssArray;
		}

		$saveCache = function($name, $content) use ($savepath, $path){
			$hash = md5($this->outputOriginal);

			if (is_null($this->InterpolateContent))
			{
				$this->interpolate($content);	
			}

			$this->cssArray[$name] = $hash;

			ob_start();
			var_export($this->cssArray);
			$arr = '<?php'."\n";
			$arr .= 'return '. ob_get_contents() . ';'."\n";
			$arr .= '?>';
			ob_clean();

			File::write($arr, $path);

			File::write($content, $savepath(str_replace('/', '', $name)));
		};

		
		$cache_path = $savepath(str_replace('/','',$cache_name));

		if (!is_null($content))
		{
			$hash = md5($content);
			
			if (isset($cache[$cache_name]))
			{
				$hash2 = $cache[$cache_name];

				if ($hash2 == $hash)
				{
					$cache_path = $savepath(str_replace('/','',$cache_name));
					return true;
				}
			}
		}
		else
		{
			// save cache
			if (strlen($this->InterpolateContent) > 0)
			{
				$saveCache($cache_name, $this->InterpolateContent);
			}
		}

		return false;
	}

	// load authentication handler for view.
	final public function authentication($data=null)
	{
		// get controller
		\Authenticate::$controller = BootLoader::$helper['get_controller'];

		// authentication handler sent
		if (!is_null($data))
		{
			// get authentication class and method
			$data = explode('@', $data);
			// class and method. unpack
			list($handler, $method) = $data;

			// get params
			$args = func_get_args();
			$params = array_splice($args, 1);

			// scan for file
			$path = deepScan(PATH_TO_AUTHENTICATION, [$handler . '.php', $handler.'.auth.php']);

			// check if external config is not empty
			if (!empty(View::$external_config))
			{
				// scan from thirdparty directory.
				$path1 =  deepScan(Model::$thirdparty_path . 'utility/Authentication/', [$handler . '.php', $handler.'.auth.php']);

				if (file_exists($path1))
				{
					$path = $path1;
				}

				$path1 = null;
			}

			$error = false;
			$message = null;

			if (file_exists($path))
			{
				include_once $path;

				$handler = basename($handler);

				if (strpos($handler, '.auth') === false)
				{
					$handler .= '.auth';
				}

				$class = ucwords(str_replace(".", ' ', $handler));
				$class = preg_replace('/[\s]{1,}/', '', $class);

				if (!is_null($method))
				{
					// get instance
					$ref = new \ReflectionClass($class);
					// check if we can call construct method
					if ($ref->hasMethod('__construct'))
					{
						// get arguments
						Bootloader::$instance->getParameters($class, '__construct', $const);
						// create instance
						$handler = $ref->newInstanceArgs($const);
					}
					else
					{
						$handler = new $class;
					}

					if (method_exists($handler, $method))
					{
						$this->system->session->set('history.url', \get_query());

						Bootloader::$instance->getParameters($handler, $method, $const, $params);

						call_user_func_array([$handler, $method], $const);
						
					}
					else
					{
						$error = true;
						$message = "$class handler method '$method' doesn't exists. Action Failed!";
					}
				}
				else
				{
					$this->system->session->set('history.url', \get_query());
					
					Bootloader::$instance->getParameters($class, '__construct', $const, $params);

					$ref = new \ReflectionClass($class);
										
					$handler = $ref->newInstanceArgs($const);
				}

			}
			else
			{
				$error = true;
				$message = "Authentication handler '$handler' doesn't exits in authentication/ dir. Action Failed!";
			}

			if ($error !== false)
			{
				Event::emit('authentication.error', $message);
				throw new \Exceptions\Authentication\AuthenticationException($message);
			}

		}

		return Bootloader::$currentClass;
	}

	// interpolate
	final private function interpolate(&$data, &$content = null)
	{
		$data = html_entity_decode($data, ENT_QUOTES, 'UTF-8');
		
		static $hyphe;

		if (is_null($hyphe))
		{
			$hyphe = new \Moorexa\CHS();
		}

		$hyphe->interpolateString = false;
		$class = isset(Bootloader::$currentClass->model) ? Bootloader::$currentClass->model : null;

		if (is_null($class))
		{
			$class = $this;
		}
		
		$data = $hyphe->interpolateExternal($data, $class, $interpolated);
		\Hyphe\Compile::ParseDoc($interpolated);

		$content = $interpolated;

		$data = preg_replace('/(<php-var>)([^<]+)(<\/php-var>)/', '', $data);

		$data = preg_replace("/(@)\s{0}(setdefault)/m", '', $data);

		// clean up
		$class = null;

		return $data;
	}
}
// END class
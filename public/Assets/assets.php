<?php

Namespace Moorexa;

// Handle Assets
class Assets
{
	// folder
	private $folder = null;
	private $build = [];
	private $resize = [];
	private $output = null;
	private $dom = null;
	private $static = "";
	private $image_path = PATH_TO_IMAGE;
	private $css_path = PATH_TO_CSS;
	private $js_path = PATH_TO_JS;
	public static $jsScripts = [];
	public static $jsLoadConfig = [];
	public static $changePosition = ['css' => [], 'js' => []];

	public function __get($name)
	{
		$this->static = Bootloader::boot('staticurl');

		$this->folder = $name;

		return $this;
	}

	// config
	public function config($data)
	{
		if (is_array($data))
		{
			foreach ($data as $key => $val)
			{
				if (property_exists($this, $key))
				{
					$this->{$key} = $val;
				}
			}
		}
	}

	public function image($file)
	{
		if ($this->getFileIfCache($file, $cache, $json))
		{
			return $cache;
		}
		else
		{
			$cache = PATH_TO_PUBLIC . 'Assets/assets.paths.json';
			$size = null;
			$filecopy = $file;
			$fileNoUpdate = $file;

			if (strpos($file,'@') !== false)
			{
				$size = substr($file, strpos($file,'@')+1);
				$file = substr($file, 0, strpos($file,'@'));
				$size = explode(':',$size);
				$filecopy = $file;
			}

			$dirs = Linker::getDirs();

			$other = isset($dirs[rtrim($this->image_path, '/')]) ? $dirs[rtrim($this->image_path, '/')] : null;

			if (strpos($file, '::') !== false)
			{
				$folder = substr($file, 0, strpos($file, '::'));
				$file = substr($file, strpos($file, '::'));
				$file = preg_replace('/[:]/','',$file);
				$folder = preg_replace('/[:]/','',$folder);
				$this->folder = $folder;
				$folder = null;
			}
			
			$filePassed = $file;
			$file = explode('/', $file);
			$filen = end($file);
			array_pop($file);
			$extra = implode('/', $file);
			$file = $filen;

			$cont = isset(BootLoader::$helper['get_controller']) ? ucfirst(BootLoader::$helper['get_controller']) : ucfirst(config('router.default.controller'));

			if ($this->static == '')
			{
				// failed! so we check
				$newpath = null;

				$parse = parse_url($filePassed);
				
				if (isset($parse['scheme']))
				{
					$newpath = $filePassed;
					$json[$fileNoUpdate] = $filePassed;
				}
				else
				{
					// get image path
					$getImage = function($file) use ($filecopy, &$json, $fileNoUpdate, $size){
						$dir = $this->image_path;
						$getPath = $dir . $file;

						$parse = parse_url($filecopy);

						if (file_exists($filecopy) || isset($parse['scheme']))
						{
							$scan = $filecopy;
						}
						elseif (file_exists($getPath))
						{
							$scan = $getPath;
						}
						else
						{
							$scan = deepScan($dir, $file);
						}

						if ($scan == '')
						{
							return url(deepScan(PATH_TO_IMAGE, 'no-image-available.png'));
						}
						else
						{
							$path = url($scan);

							if ($size !== null)
							{
								$path = url(resizeImage($scan, $size[0], $size[1]));
							}

							$json[$fileNoUpdate] = $scan;

							return $path;
						}
					};
					
					switch (is_dir($this->image_path . $cont))
					{
						case true:
							$dir = $this->image_path . $cont . '/';
							$getPath = $dir . $filePassed;

							if (file_exists($getPath))
							{
								$scan = $getPath;
							}
							else
							{
								$scan = deepScan($dir, $filePassed);
							}

							if ($scan == '')
							{
								$newpath = $getImage($filePassed);
							}
							else
							{
								$path = url($scan);

								if ($size !== null)
								{
									$path = url(resizeImage($scan, $size[0], $size[1]));
								}

								$json[$fileNoUpdate] = $scan;
								$newpath = $path;
							}
						break;

						case false:
							$newpath = $getImage($filePassed);
						break;
					}
				}

				// save json
				if (count($json) > 0)
				{
					if (is_writable($cache))
					{
						file_put_contents($cache, json_encode($json, JSON_PRETTY_PRINT));
					}
				}

				return $newpath;
			}
			else
			{
				return rtrim($this->static, '/') . '/' . $this->folder . '/' . $file;
			}
		}
	}

	public function css($file)
	{
		if ($this->getFileIfCache($file, $cache, $json))
		{
			return $cache;
		}
		else
		{
			$queryInPath = '';

			if (strpos($file, '?') !== false)
			{
				$end = strpos($file, '?');
				$fileName = substr($file, 0, $end);
				$queryInPath = substr($file, $end);
				$file = $fileName;
			}

			$cache = PATH_TO_PUBLIC . 'Assets/assets.paths.json';
			$filecopy = $file;
			$fileNoUpdate = $file . $queryInPath;
			$cont = isset(BootLoader::$helper['get_controller']) ? ucfirst(BootLoader::$helper['get_controller']) : ucfirst(config('router.default.controller'));

			// get css path
			$getCss = function($file) use ($filecopy, &$json, $fileNoUpdate, $queryInPath){
				$dir = $this->css_path;
				$getPath = $dir . $file;
				$parse = parse_url($filecopy);

				if (file_exists($filecopy) || isset($parse['scheme']))
				{
					$scan = $filecopy;
				}
				elseif (file_exists($getPath))
				{
					$scan = $getPath;
				}
				else
				{
					$scan = deepScan($dir, $file);
				}

				if ($scan == '')
				{
					return '';
				}
				else
				{
					$json[$fileNoUpdate] = $scan . $queryInPath;

					return $scan;
				}
			};

			switch (is_dir($this->css_path . $cont))
			{
				case true:
					$dir = $this->css_path . $cont . '/';
					$getPath = $dir . $file;

					if (file_exists($getPath))
					{
						$scan = $getPath;
					}
					else
					{
						$scan = deepScan($dir, $file);
					}

					if ($scan == '')
					{
						$newpath = $getCss($file);
					}
					else
					{
						$json[$fileNoUpdate] = $scan . $queryInPath;

						$newpath = $scan;
					}
				break;

				case false:
					$newpath = $getCss($file);
				break;
			}

			// save json
			if (count($json) > 0)
			{
				if (is_writable($cache))
				{
					file_put_contents($cache, json_encode($json, JSON_PRETTY_PRINT));
				}
			}

			return $newpath . $queryInPath;
		}
	}

	public function js($file)
	{
		if ($file == 'php-vars.js')
		{
			// would import all data avaliable in Controller::$dropbox
			return $file;
		}

		if ($this->getFileIfCache($file, $cache, $json))
		{
			return $cache;
		}
		else
		{
			$queryInPath = '';

			if (strpos($file, '?') !== false)
			{
				$end = strpos($file, '?');
				$fileName = substr($file, 0, $end);
				$queryInPath = substr($file, $end);
				$file = $fileName;
			}

			$cache = PATH_TO_PUBLIC . 'Assets/assets.paths.json';
			$filecopy = $file;
			$fileNoUpdate = $file . $queryInPath;
			$cont = isset(BootLoader::$helper['get_controller']) ? ucfirst(BootLoader::$helper['get_controller']) : ucfirst(config('router.default.controller'));

			// get js path
			$getJs = function($file) use ($filecopy, &$json, $fileNoUpdate, $queryInPath){
				$dir = $this->js_path;
				$getPath = $dir . $file;

				$parse = parse_url($filecopy);

				if (file_exists($filecopy) || isset($parse['scheme']))
				{
					$scan = $filecopy;
				}
				elseif (file_exists($getPath))
				{
					$scan = $getPath;
				}
				else
				{
					$scan = deepScan($dir, $file);
				}

				if ($scan == '')
				{
					return '';
				}
				else
				{
					$json[$fileNoUpdate] = $scan . $queryInPath;

					return $scan;
				}
			};

			switch (is_dir($this->js_path . $cont))
			{
				case true:
					$dir = $this->js_path . $cont . '/';
					$getPath = $dir . $file;

					if (file_exists($getPath))
					{
						$scan = $getPath;
					}
					else
					{
						$scan = deepScan($dir, $file);
					}

					if ($scan == '')
					{
						$newpath = $getJs($file);
					}
					else
					{
						$json[$fileNoUpdate] = $scan . $queryInPath;

						$newpath = $scan;
					}
				break;

				case false:
					$newpath = $getJs($file);
				break;
			}

			// save json
			if (count($json) > 0)
			{
				if (is_writable($cache))
				{
					file_put_contents($cache, json_encode($json, JSON_PRETTY_PRINT));
				}
			}

			return $newpath . $queryInPath;
		}
	}

	public function media($file)
	{
		if ($this->getFileIfCache($file, $cache, $json))
		{
			return $cache;
		}
		else
		{
			$cache = PATH_TO_PUBLIC . 'Assets/assets.paths.json';
			$filecopy = $file;
			$fileNoUpdate = $file;
			$cont = isset(BootLoader::$helper['get_controller']) ? ucfirst(BootLoader::$helper['get_controller']) : ucfirst(config('router.default.controller'));


			// get media path
			$getMedia = function($file) use ($filecopy, &$json, $fileNoUpdate){
				$dir = PATH_TO_MEDIA;
				$getPath = $dir . $file;

				$parse = parse_url($filecopy);

				if (file_exists($filecopy) || isset($parse['scheme']))
				{
					$scan = $filecopy;
				}
				elseif (file_exists($getPath))
				{
					$scan = $getPath;
				}
				else
				{
					$scan = deepScan($dir, $file);
				}

				if ($scan == '')
				{
					return '';
				}
				else
				{
					$json[$fileNoUpdate] = $scan;

					return $scan;
				}
			};

			switch (is_dir(PATH_TO_MEDIA . $cont))
			{
				case true:
					$dir = PATH_TO_MEDIA . $cont . '/';
					$getPath = $dir . $file;

					if (file_exists($getPath))
					{
						$scan = $getPath;
					}
					else
					{
						$scan = deepScan($dir, $file);
					}

					if ($scan == '')
					{
						$newpath = $getMedia($file);
					}
					else
					{
						$json[$fileNoUpdate] = $scan;

						$newpath = $scan;
					}
				break;

				case false:
					$newpath = $getMedia($file);
				break;
			}

			// save json
			if (count($json) > 0)
			{
				if (is_writable($cache))
				{
					file_put_contents($cache, json_encode($json, JSON_PRETTY_PRINT));
				}
			}

			return $newpath;
		}
	}

	public function build()
	{
		$keys = array_keys($this->build);

		$val = $this->build[$keys[0]];

		$resize = isset($this->resize[$keys[0]]) ? $this->resize[$keys[0]] : false;

		$attr = null;

		switch ($keys[0])
		{
			case 'image':

			    if (!empty($resize) && strpos($resize, 'resize') >= 0)
			    {
			    	$resizeval = substr($resize, strlen('resize')+1);

			    	if ($resize === false)
			    	{
			    		$this->output = '<img data-src="'.$this->image[$val].'">';
			    	}
			    	else
			    	{
			    		$exp = explode('x', $resizeval);

			    		if (count($exp) > 0)
			    		{
			    			$width = $exp[0];
			    			$height = $exp[1];

			    			$resized = resizeImage($val, $width, $height);

			    			$large = abspath($resized);

			    			$tablet = 0;
			    			$mobile = abspath(resizeImage($val, 320, 240));

			    			if ($width > 640)
			    			{
			    				$tablet = abspath(resizeImage($val, 640, 480));
			    			}
			    			else
			    			{
			    				$tablet = $large;
			    			}

			    			$this->output = '<img data-src="'.$large.'" srcset="'.$large.' 1024w, '.$tablet.' 640w,'.$mobile.' 320w" sizes="(min-width: 36em) 33.3vw, 100vw">';
			    		}
			    		
			    	}
			    	
			    }
			    else
			    {
			    	$this->folder = 'image';
			    	$tablet = abspath(resizeImage($val, 640, 480));
			    	$mobile = abspath(resizeImage($val, 320, 240));

			    	$this->output = '<img data-src="'.$this->image[$val].'" srcset="'.$this->image[$val].' 1024w, '.$tablet.' 640w,'.$mobile.' 320w" sizes="(min-width: 36em) 33.3vw, 100vw">';	
			    }
				
			break;

			case 'css':
				$this->output = '<link rel="stylesheet" type="text/css" href="'.$this->css[$val].'">';
			break;

			case 'js':
				$this->output = '<script type="text/deffered" data-src="'.$this->js[$val].'" aysnc></script>';
			break;

			case 'attr':
				$attr = $val;
			break;
		}

		if ($attr != "")
		{
			ob_end_clean();

			$out = rtrim($this->output, '>');

			$out .= ' '.$attr .'>';

			$this->output = $out;
			
			echo $this->dom;
			echo $this->output;
		}
		else
		{
			echo $this->output;
		}

		$this->build = [];
		return $this;
	}

	private function getFileIfCache($file, &$cachefile=null, &$json=[])
	{
		if ($file != null && is_string($file) && strlen($file) > 1)
		{
			$fileNoUpdate = $file;
			$cont = isset(BootLoader::$helper['active_c']) ? ucfirst(BootLoader::$helper['active_c']) : ucfirst(config('router.default.controller'));

			$cache = PATH_TO_PUBLIC . 'Assets/assets.paths.json';
			$json = json_decode(file_get_contents($cache));
			$json = is_null($json) ? [] : toArray($json);

			$fileNoUpdate = $cont . '.' . $fileNoUpdate;

			if (isset($json[$fileNoUpdate]))
			{
				if (file_exists(abspath($json[$fileNoUpdate])))
				{
					$cachefile = url($json[$fileNoUpdate]);
					return true;
				}
			}
			else {
				if (isset($json[$file]))
				{
					if (file_exists(abspath($json[$file])))
					{
						$cachefile = url($json[$file]);
						return true;
					}
				}
			}
		}

		return false;
	}

	public function __call($meth, $arg)
	{
		if ($meth == 'attr')
		{
			$this->build['attr'] = implode(' ', $arg);
		}
		elseif ($this->static != "" && $meth == 'from')
		{
			return rtrim($this->static, '/') . '/' . $arg[0] . '/' . $arg[1];
		}
		else
		{ 
	 		$this->build[$meth] = $arg[0];
			$this->resize[$meth] = isset($arg[1]) ? $arg[1] : "";
			$this->dom = ob_get_contents();
		}

		return $this->build();
	}
	
	public function __toString()
	{
		return "";
	}

	// load css
	public function loadCss($cssFiles)
	{
		$css = [];

		// check if a css position has been changed
		$this->changePositionIfChanged('css', $cssFiles);

		// ilterate
		array_map(function($val) use (&$css){
			// has request
			$parse = parse_url($val);
			if (!isset($parse['scheme']))
			{
				$val = url($val);
			}
			$css[] = '<link rel="stylesheet" type="text/css" href="'.$val.'"/>';
		}, $cssFiles);

		// return css
		return implode("\n\t", $css);
	}

	// change file position before rendering
	private function changePositionIfChanged(string $typeOfFile, array &$referenceArray)
	{
		$changePosition = self::$changePosition[$typeOfFile];

		if (count($changePosition) > 0)
		{
			foreach ($changePosition as $fileToChange => $config)
			{
				// get position and otherjs
				list($position, $otherFile) = $config;

				// get position of otherfile
				$otherPosition = null;

				// run through reference array
				foreach ($referenceArray as $index => $filePath)
				{
					// get base name
					$basename = basename($filePath);

					if ($basename == basename($otherFile) || $otherFile == $filePath)
					{
						$otherPosition = $index;
					}

					if ($fileToChange == $filePath || $basename == basename($fileToChange))
					{
						// get path
						$fileToChange = $filePath;
						
						// remove from position
						unset($referenceArray[$index]);

						// now move file
						switch (strtolower($position))
						{
							case 'before':
								array_splice($referenceArray, $otherPosition, 2, [$fileToChange, $referenceArray[$otherPosition]]);
							break;

							case 'after':
								array_splice($referenceArray, $otherPosition, 2, [$referenceArray[$otherPosition], $fileToChange]);
							break;
						}
					}
				}
			}
		}
	}

	// load javascripts
	public function loadJs($jsFiles)
	{
		$js = [];

		if (count(Controller::$assetPreloader) > 0)
		{
			foreach (Controller::$assetPreloader as $index => $callback)
			{
				call_user_func($callback);
			}
		}

		// check if a javascript position has been changed
		$this->changePositionIfChanged('js', $jsFiles);

		// ilterate
		array_map(function($val) use (&$js){
			// has request
			$parse = parse_url($val);
			if (!isset($parse['scheme']))
			{
				$val = url($val);
			}

			$type = 'text/deffered';
			$base = basename($val);

			if (isset(self::$jsLoadConfig[$base]))
			{
				$config = self::$jsLoadConfig[$base];
				if (isset($config['deffer']) && !$config['deffer'])
				{
					$type = 'text/javascript';
				}
				else {
					$type = 'text/deffered';
				}
			}

			if ($base != 'php-vars.js')
			{
				$js[] = '<script type="'.$type.'" src="'.$val.'"></script>';
			}
			else
			{
				$js[] = Controller::exportDropboxAsScript();
			}

		}, $jsFiles);

		// has script tags
		if (count(self::$jsScripts) > 0)
		{
			array_map(function($script) use (&$js){
				$js[] = $script;
			}, self::$jsScripts);
		}

		// deffer
		$deffer = $this->js('deffer.min.js');
		$parse = parse_url($deffer);
		if (!isset($parse['scheme']))
		{
			$deffer = url($deffer);
		}
		$js[] = '<script type="text/javascript" src="'.$deffer.'" data-moorexa-appurl="'.url().'"></script>';

		// return js
		return implode("\n\t", $js);
	}


	// change path
	public function changePath(array $config)
	{
		// run through array
		if (count ($config) > 0)
		{
			foreach ($config as $property => $path)
			{
				if (property_exists($this, $property))
				{
					// set path
					$this->{$property} = $path;
				}
			}
		}
	}
	
	// reset path
	public function resetPath()
	{
		$this->css_path = PATH_TO_CSS;
		$this->js_path 	= PATH_TO_JS;
		$this->image_path = PATH_TO_IMAGE;
	}
}
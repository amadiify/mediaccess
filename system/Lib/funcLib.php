<?php

// resize image
function resizeImage( $image, $width, $height, $quality = 65, $dir = PATH_TO_IMAGE)
{

	$resizeLib = PATH_TO_IMAGE . 'resized/';

	if (!is_dir($resizeLib))
	{
		mkdir($resizeLib);
	}

	$prefix = $width .'x'. $height;

	$fullImage = $prefix . basename($image);

	$continue = false;

	if (file_exists( $resizeLib . $fullImage ))
	{
		if (!file_exists($image))
		{
			$imagePath = deepScan(PATH_TO_IMAGE, basename($image));
		}
		else
		{
			$imagePath = $image;
		}

		$fileinfo = getimagesize($imagePath);

		$lastpre = 'original-';

		$imagename = $lastpre . basename($image);

		$resized = $resizeLib . $imagename;

		$rezm = filemtime($resized);
		$rezm1 = filemtime($imagePath);

		$date = new DateTime();
		$date->setTimestamp($rezm);

		$date2 = new DateTime();
		$date2->setTimestamp($rezm1);
		

		if ($date->format('g:i:s a') != $date2->format('g:i:s a'))
		{
			unlink($resizeLib . $fullImage);
			unlink($resized);
			$continue = false;
		}
		else
		{
			$continue = true;
		}
		
	}


	if ($continue === true)
	{
		return $resizeLib . $fullImage;
	}
	else
	{
		// create new file
		$resizePath = $resizeLib . $fullImage;

		if (file_exists($image))
		{
			$imagePath = $image;
			$image = basename($imagePath);
		}
		else
		{
			$imagePath = deepScan(PATH_TO_IMAGE, $image);
		}

		if (file_exists($imagePath))
		{	
			$fileInfo = getimagesize($imagePath);
			$i_width = $fileInfo[0];
			$i_height = $fileInfo[1];
			
			if ($i_width !== $width && $i_height !== $height || $quality > 0)
			{
				
				// perform rescale to size
				$mime = $fileInfo['mime'];

				$type = explode("/", $mime);
				$type = strtolower($type[1]);

				$truecolor = imagecreatetruecolor($width, $height);

				if ($type == "png")
				{
					$createimage = imagecreatefrompng($imagePath);
				}
				elseif ($type == "jpg" || $type == "jpeg")
				{
					$createimage = imagecreatefromjpeg($imagePath);
				}
				elseif ($type == 'gif')
				{
					$createimage = imagecreatefromgif($imagePath);
				}

				if (isset($createimage))
				{
					$color = imagecolorallocate($truecolor, 0, 0, 0);
					imagecolortransparent($truecolor, $color);
					imagecopyresampled($truecolor, $createimage, 0,0,0,0, $width, $height, $i_width, $i_height);

					
					if ($type == "png")
					{
						$invertScaleQuality = 9 - round(($quality/100) * 9);
						imagepng($truecolor, $resizeLib . $fullImage, $invertScaleQuality);
					}
					elseif ($type == "jpg" || $type == "jpeg")
					{
						imagejpeg($truecolor, $resizeLib . $fullImage, $quality);
					}
					elseif ($type == 'gif')
					{
						imagegif($truecolor, $resizeLib . $fullImage);
					}
					

					// copy original
					$original = 'original-'.$image;

					if (!file_exists($resizeLib . $original))
					{
						copy($imagePath, $resizeLib . $original);
						unlink($imagePath);
						copy($resizeLib . $original, $imagePath);
					}

					return $resizeLib . $fullImage;
				}
				else
				{

				}
			}
			else
			{
				// maybe compress image and return path
				$compressed = PATH_TO_IMAGE . 'compressed/';
				$compressedPath = deepScan($compressed, $image);

				if (file_exists($compressedPath))
				{
					return $compressedPath;
				}
				else
				{
					// not compressed! 
					// compress image
					return compressImage($image);
				}
			}

		}
		else
		{
			return absolutePath() . deepScan(PATH_TO_IMAGE, 'no-image-available.png');
		}
		
		
	}
}


// Make a deep scan for files
function deepScan($dir, $file)
{
	$getjson = file_get_contents(PATH_TO_STORAGE . 'Caches/getpaths.json');
	$json = json_decode($getjson);

	$failedPaths = file_get_contents(PATH_TO_STORAGE . 'Caches/pathsNotFound.json');
	$failed = json_decode($failedPaths);
	$failed = is_object($failed) ? (array) $failed : [];
	
	if (is_object($json))
	{
		$json = (array) $json;
	}
	else
	{
		$json = [];
	}

	$_path = "";
	$updateJson = false;
	$updateFailed = false;

	if(is_array($file))
	{
		$key = $dir.':'.implode(":",$file);

		if (isset($json[$key]))
		{
			$_path =  $json[$key];
		}
		else
		{
			if (!isset($failed[$key]))
			{
				$found = false;
				foreach($file as $inx => $ff)
				{
					if ($found == false)
					{
						$_path = __fordeepscan($dir, $ff);
						if ($_path !== "") {
							$found = true; 
							$json[$key] = $_path;
							$updateJson = true;
							break;
						}

					}
				}

				if (!$found)
				{
					$updateFailed = true;
					$failed[$key] = [$dir, $file];
				}
			}
			else
			{
				$arr = $failed[$key];
				$dir = $arr[0];
				if (is_dir($dir))
				{
					foreach ($arr[1] as $i => $file)
					{
						$build = $dir . $file;
						if (file_exists($build))
						{
							$_path = $build;
							break;
						}
					}
				}
				$arr = null;
				$dir = null;
			}

			$file = null;
		}
	}
	else
	{
		$key = $dir.':'.$file;

		if (isset($json[$key]))
		{
			$_path = $json[$key];
		}
		else
		{
			if (!isset($failed[$key]))
			{
				$_path = __fordeepscan($dir, $file);
				if ($_path !== '')
				{
					$json[$key] = $_path;
					$updateJson = true;
				}

				if ($_path == '')
				{
					$updateFailed = true;
					$failed[$key] = [$dir, $file];
				}
			}
			else
			{
				$arr = $failed[$key];
				$dir = $arr[0];
				$build = $dir . $arr[1];

				if (file_exists($build))
				{
					$_path = $build;
				}
				$arr = null;
				$dir = null;
			}
		}
	}

	if ($updateJson)
	{
		$json = json_encode($json, JSON_PRETTY_PRINT);
		file_put_contents(PATH_TO_STORAGE . 'Caches/getpaths.json', $json);
	}

	if ($updateFailed)
	{
		$update = json_encode($failed, JSON_PRETTY_PRINT);
		file_put_contents(PATH_TO_STORAGE . 'Caches/pathsNotFound.json', $update);
	}

	$dir = null;

	return $_path;
}

function __fordeepscan($dir, $file)
{
	$path = "";
	$scan = glob($dir.'/*');
	$q = preg_quote($file, '\\');

	if (is_array($scan))
	{
		foreach ($scan as $d => $f)
		{
			if ($f != '.' && $f != '..')
			{
				$f = preg_replace("/[\/]{1,}/", '/', $f);

				if (!is_dir($f))
				{
					$base = basename($f);

					if (($base == $file) && strrpos($f, $file) !== false)
					{
						$path = $f;
					}

					$base = null;
				}

				if ($path == "")
				{
					$path = __fordeepscan($f, $file);
					if ($path !== ""){
						if (strrpos($path, $file) !== false){
							break;
						}
					}
				}

				$f = null;
			}
		}

		$scan = null;
	}

	return $path;
}


function findSubDirectory($dir, $sub, $file)
{
	if (preg_match('/[\/]/', $sub))
	{
		$sub = preg_replace('/[\/]/','', $sub);
	}

	$sub = trim($sub);
	
	return __findSubDirectory($dir, $sub, $file);
}

function __findSubDirectory($dir, $sub, $file)
{
	$path = "";
	$scan = glob($dir.'/*');

	if (is_array($scan))
	{
		foreach ($scan as $d => $f)
		{
			if ($f != '.' && $f != '..')
			{
				$f = preg_replace("/[\/]{1,}/", '/', $f);

				if (is_dir($f))
				{
					$base = basename($f);
					$quote = preg_quote($sub);
					$match = preg_match("/($quote)$/", $f);
					
					if (($base == $sub || $match == true) && file_exists($f . '/' . $file))
					{
						$path = $f;
						break;
					}
					else
					{
						$path = __findSubDirectory($f, $sub, $file);
						if ($path != '')
						{
							break;
						}
						else
						{
							$path = '';
						}
					}
				}
			}
		}
	}	

	return $path;
}
	

function isVal($data, $res)
{
	$found = false;

	foreach($res as $key => $val)
	{
		if(is_array($val) || is_object($val))
		{
			foreach($val as $kval => $val1)
			{
				if(trim($val1) == trim($data))
				{
					$found = true;
				}
		
			}
		}
		else
		{
			if(trim($val) == trim($data))
			{
				$found = true;
			}
		}
	}

	return $found;
}


function isKey($data, $res)
{
	$found = false;

	foreach($res as $key => $val)
	{
		if(is_array($key) || is_object($key))
		{
			foreach($key as $kval => $val1)
			{
				if(trim($kval) == trim($data))
				{
					$found = true;
				}
		
			}
		}
		else
		{
			if(trim($key) == trim($data))
			{
				$found = true;
			}
		}
	}

	return $found;
}


function url($path = "", $encode = false) 
{
	$url = settings::get('url');

	$url = rtrim($url, '/');
	$url = rtrim($url, '/ ');

	if ($encode)
	{
		$encode = (strlen($path) > 0 ? urlencode($path) : '');

		if ($encode != '')
		{
			$encode = str_replace('%2F', '/', $encode);
		}
	}
	else
	{
		$encode = $path;
	}

	// get extension
	$extension = extension($encode);

	if ($extension !== false)
	{
		switch (strtolower($extension))
		{
			case 'css':
			case 'js':
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
				$static = env('bootstrap', 'static_url');

				if ($static != '')
				{
					$url = $static;
					$encode = ltrim($encode, HOME);
				}
			break;
		}
	}

	return rtrim($url, "/")."/".$encode;
}


function is_serialized( $value, &$result = null ) {
	// Bit of a give away this one
	if ( ! is_string( $value ) ) {
		return FALSE;
	}
	// Serialized FALSE, return TRUE. unserialize() returns FALSE on an
	// invalid string or it could return FALSE if the string is serialized
	// FALSE, eliminate that possibility.
	if ( 'b:0;' === $value ) {
		$result = FALSE;
		return TRUE;
	}
	$length	= strlen($value);
	$end	= '';
	
	if ( isset( $value[0] ) ) {
		switch ($value[0]) {
			case 's':
				if ( '"' !== $value[$length - 2] )
					return FALSE;
				
			case 'b':
			case 'i':
			case 'd':
				// This looks odd but it is quicker than isset()ing
				$end .= ';';
			case 'a':
			case 'O':
				$end .= '}';
	
				if ( ':' !== $value[1] )
					return FALSE;
	
				switch ( $value[2] ) {
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
					break;
	
					default:
						return FALSE;
				}
			case 'N':
				$end .= ';';
			
				if ( $value[$length - 1] !== $end[0] )
					return FALSE;
			break;
			
			default:
				return FALSE;
		}
	}
	
	if ( ( $result = @unserialize($value) ) === FALSE ) {
		$result = null;
		return FALSE;
	}
	
	return TRUE;
}


function session()
{
	static $session;

	if ($session == null)
	{
		$session = new \Moorexa\Session();
	}

	if (func_num_args() == 1)
	{
		$key = func_get_args()[0];
		return $session->get($key);	
	}
	elseif (func_num_args() > 1)
	{
		$args = func_get_args();
		$session->set($args[0], $args[1]);
		return true;
	}

	return $session;
}


function cookie()
{
	static $cookies;

	if ($cookies == null)
	{
		$cookies = new \Moorexa\Cookie();
	}

	if (func_num_args() == 1)
	{
		$key = func_get_args()[0];
		return $cookies->get($key);	
	}
	elseif (func_num_args() > 1)
	{
		$args = func_get_args();
		$cookies->set($args[0], $args[1]);
		return true;
	}

	return $cookies;
}


function getTitle()
{
	$__cont = Moorexa\Bootloader::$helper['get_controller'];

	if (class_exists("Moorexa\Controller") && isset(Moorexa\Controller::$dropbox['apptitle']))
	{
		$__cont = null;

		return ucfirst(Moorexa\Controller::$dropbox['apptitle']);
	}
	else
	{
		$build = "";

		if (isset($_GET['__app_request__']))
		{
			$appurl = explode("/", $_GET['__app_request__']);

			$build = end($appurl);

			$appurl = null;

			return ucfirst($build);	
		}
			
	}
}

function redirect($path, $data = null)
{
	$content = ob_get_contents();

	if ($content !== false){
		
		// clean output
		ob_clean();
		ob_start();
	}

	// create an instance of the app class
	$app = new \Moorexa\View();
	return $app->renderNew($path, $data);
}

function absolutePath()
{

	$path = "";

	$staticurl = Moorexa\Bootloader::boot('static_url');
	$isonline = isset(Moorexa\Bootloader::$helper['isonline']) ? Moorexa\Bootloader::$helper['isonline'] : null;

	if ($staticurl !== "" && $isonline === true)
	{
		$staticurl = rtrim($staticurl, "/");
		$path = $staticurl . "/";

		$staticurl = null;
	}	
	else
	{
		if (isset($_GET['__app_request__']) || isset($_SERVER['REQUEST_QUERY_STRING']))
		{
			if (isset($_GET['__app_request__']) && !isset($_SERVER['REQUEST_QUERY_STRING']))
			{
				$get = $_GET['__app_request__'];

				$app = explode("/", $get);
			}
			else
			{
				if (isset($_SERVER['REQUEST_QUERY_STRING']))
				{
					$app = explode('/', $_SERVER['REQUEST_QUERY_STRING']);
				}
			}

			$bf = $app;

			if (count($app) >= 1)
			{
				unset($app[0]);

				foreach ($app as $key => $value){
					# code...
					$path .= "../";
				}

				$app = null;
				
			}

		}

	}

	
	$isonline = false;
	
	return $path;
}


// show failed requests
function __failedRequests()
{
	$failed = Moorexa\DatabaseHandler::$failedRequests;
	?>
		<div class="mor-failed-requests">
			<div class="btn">
				<b>( <?=count($failed)?> )</b>
				<span> Failed Database <?=count($failed) == 1 ? "Query" : "Queries" ?></span>
			</div>

			<div class="info">
				<div class="info-inner">
					<ul>
						<?php

							foreach ($failed as $in => $row)
							{
								if (isset($row[2]))
								{
									?>
									<li>
										<div><span>Func: </span> <?=$row[0]?>() </div>
										<div><span>Request: </span> <?=$row[1]?> </div>
										<div><span class="mor-text-error">Reason: </span> <?=$row[2]?> </div>
									</li>
									<?php
								}
								else
								{
									?>
									<li>
										<div><span>Func: </span> <?=$row[0]?>() </div>
										<div><span>Request: </span> <?= implode(" ", $row[1])?> </div>
										<div><span class="mor-text-error">Reason: </span> No Connectivity.</div>
									</li>
									<?php
								}
							}
						
						?>
						
					</ul>
				</div>
			</div>
		</div>
	<?php

	$failed = null;
}


function get_dir_size($directory){
    $size = 0;
    $files= glob($directory.'/*');
    foreach($files as $path){
        is_file($path) && $size += filesize($path);

        if (is_dir($path))
        {
        	$size += get_dir_size($path);
        }
    }

    $files = null;

    return $size;
}


function get_files_count($directory)
{
	$files = 0;

	$scan = glob($directory."/*");

	foreach ($scan as $path)
	{
		if (is_file($path))
		{
			$files += 1;
		}
		else
		{
			if (is_dir($path))
			{
				$files += get_files_count($path);
			}
		}
	}

	$scan = null;

	return $files;

}


function parse_query(string $str)
{
	// get individual request
	$str = ltrim($str, '?');
	$ind = explode("&", $str);

	$query = [];

	if (count($ind) > 0)
	{
		foreach ($ind as $i => $d)
		{
			$dd = explode("=", $d);
			$query[$dd[0]] = isset($dd[1]) ? $dd[1] : "";
			$dd = null;
		}
	}

	$ind = null;
	$str = null;

	return $query;
}


/*
 * OpenSSL AES encryption for strings
 * @function encrypt()
 * @param $data string 
 * @return string
*/
function encrypt($data)
{
	// secret key
	$key = \Moorexa\Bootloader::boot('secret_key');

	// encryption method
	$method = "AES-256-CBC";

	// encrypt level
	$level = 2;

	// moorexa key
	$secret_iv = '8418512c4faed1fb9c464bfa0ad07f25ab402c5b';
	
	// get key
	$key = hash('sha256', $key);

	// iv
	$iv = substr(hash('sha256', $secret_iv), 0, 16);

	// encrypt data;
	$encrypt = openssl_encrypt($data, $method, $key, 0, $iv);

	$encrypt = base64_encode(__se($encrypt, $level));

	$key = null;
	$method = null;
	$level = null;
	$secret_iv = null;
	$key = null;
	$iv = null;

	return $encrypt;
}

/*
 * @function __se() to re-encrypt data
*/
function __se($e, $level)
{
	$d = serialize(strrev($e));

	if ($level != 0)
	{
		$level -= 1;
		$d = __se($d, $level);
	}

	return $d;
}

/*
 * OpenSSL AES decrytion for strings
 * @function decrypt()
 * @param $data string 
 * @return string
*/
function decrypt($data)
{
	// secret key
	$key = \Moorexa\Bootloader::boot('secret_key');
	
	// encryption method
	$method = "AES-256-CBC";

	// encrypt level
	$level = 2;

	// moorexa key
	$secret_iv = '8418512c4faed1fb9c464bfa0ad07f25ab402c5b';
	
	// get key
	$key = hash('sha256', $key);

	// iv
	$iv = substr(hash('sha256', $secret_iv), 0, 16);

	$dec = __de(base64_decode($data), $level);

	$decrypt = openssl_decrypt($dec, $method, $key, 0, $iv);

	return $decrypt;

}

/*
 * @function __de() to re-decrypt data
*/
function __de($e, $level)
{
	$d = strrev(unserialize($e));

	if ($level != 0)
	{
		$level -= 1;
		$d = __de($d, $level);
	}

	return $d;
}

// ErrorLogger
function ErrorLogger($error, $sql, $cw)
{
	date_default_timezone_set('America/london');

	$log = file_get_contents(PATH_TO_LOGS . $cw.'_error.txt');

	$write = "[".date('d/m/Y g:i a'). ']  '.$error.'' . "\n( " .$sql. ' )'."\n***************************\n";

	if (strpos($log, $write) === false)
	{
		$fo = fopen(PATH_TO_LOGS . $cw.'_error.txt', 'a+');
		fwrite($fo, $write);
		fclose($fo);
	}

	$log = null;
	$write = null;
	$fo = null;
}


function __addcsrftoken__($datar, $matchess, $tokens, $path, $contentd = null)
{
	$content =  _addtoken_($datar, $matchess, $tokens, $path, $contentd, 0);

	return $content;
}

function _addtoken_($datar, $matchess, $tokens, $path, $contentd = null, $id = 0)
{

	if ($contentd !== null)
	{
		$content = $contentd;
	}
	else
	{
		if (file_exists($path))
		{
			$content = file_get_contents($path);		
		}
		else
		{
			$content = "";
		}
		
	}
	

	$ix = 1;

	foreach ($matchess as $id => $match)
	{
		if (isset($datar[$id]))
		{

			if (strpos($content, $datar[$id]) == false)
			{
				$with = $datar[$id];

				// get first occurance
				$pos = strpos($content, $match);
				$len = strlen($match);

				$match = null;

				$before = substr($content, 0, $pos);
				$after = substr($content, $pos + $len);

				$pos = null;
				$len = null;

				if (strpos($after, 'csrf_token()') === false || strpos($before, 'csrf_token()') === false)
				{

				$with = str_replace('<form', '<form data-csrf-tokenid="'.$ix.'"', $with);

				$content = $before . $with . $after;

				}

				$before = null;
				$after = null;

				$with = null;

				$ix++;
				
			}
		}

	}

	return $content;
		
}

function _bindif_($data, $content)
{
	return ifrecursion($data, $content, 0, count($data)-1);
}

function ifrecursion($data_, $content, $index, $size)
{
	$data = $data_[$index];

	$replace = $data;

	$pos = strpos($content, $data);

	$if = substr($content, $pos);
	$pos = null;

	$mainstring = substr($if, 0, strpos($if, "</if>") + strlen("</if>"));
	$if = null;

	$arg = preg_replace('/[<]+(if)/', '', $data);

	$data = null;

	$arg = rtrim($arg,">");
	$arg = trim($arg);

	$append = "";

	$append .= '
	<?php
		if ('.$arg.')
		{
	?>';

	$ending = '
	<?php
		}
	?>';

	$newstring = str_replace($replace, $append, $mainstring);

	$replace = null;

	if(preg_match('/[<](elseif)+(.*)+[>]{1}/', $mainstring) == false || 
		preg_match('/[<](else)+\s{0,}+[>]{1}/', $mainstring) == false)
	{
		$newstring .= $ending;
	}
	elseif (preg_match('/[<](elseif)+(.*)+[>]{1}/', $mainstring) != false && 
		preg_match('/[<](else)+\s{0,}+[>]{1}/', $mainstring) == false)
	{
		$newstring .= $ending;
	}

	$newstring = str_replace("</if>", '', $newstring);

	$content = str_replace($mainstring, $newstring, $content);
	
	$mainstring = null;
	$newstring = null;
	$ending = null;

	$contents =& $content;


	if ($index != $size)
	{
		$index += 1;
		$contents = ifrecursion($data_, $content, $index, $size);
	}

	return $contents;
}

// lets try suggest an answer to help developer fix errors
function icouldsuggest($error, $line = 0)
{
	$suggest = "";
	// undefined variable?
	if(preg_match('/(undefined)\s{1,}(variable)/i', $error) == true)
	{
		$suggest = "peharps you are running a check, please use the isset() function to be sure, else declare the variable and maybe assign a value to it so to avoid this error.";
	}
	elseif(preg_match('/(undefined)\s{1,}(function)/i', $error) == true)
	{
		$suggest = 'the function you are trying to call isn\'t a php nor moorexa function. You could add this function in '. PATH_TO_LIB . 'funcLib.php and retry. To avoid errors like this, always check for functions with is_callable().';
	}
	elseif(preg_match('/(too)\s{1,}(few)\s{1,}(arguments to function)/i', $error, $match) == true)
	{
		$suggest = 'you should look up the documentation for this function or check the function definition within the system and be sure of the required arguments to pass.';
	}
	elseif (preg_match('/(SQLSTATE\[HY000\] \[2002\])/i', $error) == true)
	{
		$suggest = 'your database system isn\'t running. Check and be sure or try using 127.0.0.1 inplace of localhost.';
	}
	elseif(preg_match('/(syntax error, unexpected \'=>\')/i', $error, $match) == true)
	{
		$suggest = 'you were supposed to use a equal sign for assignment (=) inplace of a double arrow (=>), please check';
	}
	elseif(preg_match('/(syntax error, unexpected)\s{1,}[\']{0,}\w{1,}[\']{0,}\s{1,}[\(](T_STRING)/i', $error, $match) == true)
	{
		$suggest = 'you didn\'t declare your variable properly, ensure that there are no spaces after $ eg $boy, $_cat, $man21';
	}
	elseif(preg_match('/(syntax error, unexpected)\s{1,}[\']{0,}[\$|]\w{1,}[\']{0,}\s{1,}[\(](T_VARIABLE)/i', $error, $match) == true)
	{
		$suggest = 'i think you are missing a semicolon ; somewhere before line '.$line;
	}
	elseif(preg_match('/(syntax error, unexpected \'=\', expecting \']\')/i', $error, $match) == true)
	{
		$suggest = 'i think you should replace (=) with (=>) in your array. (=) cannot be used to assign key values. Do this and try running your program again.';
	}
	elseif(preg_match('/(Use of undefined constant)/i', $error, $match) == true)
	{
		$suggest = 'you should define constant in '.PATH_TO_CONFIG.'constants.php, it\'s really simple to do. Or check documentation that requires this CONSTANT.';
	}
	elseif(preg_match('/(class)\s{1,}[\']{0,}\w{1,}[\']{0,}\s{1,}(not found)/i', $error, $match) == true)
	{
		$suggest = 'if it\'s your defined class try and create a folder where moorexa can autoload all your classes and register it in the autoloader file. Here is where the autoloader can be found : '. PATH_TO_INC . 'autoloader.php';
	}

	return $suggest;
}


function __morbind__($str, $content, $strict = false)
{
	$len = 0;

	if ($strict === true)
	{
		preg_match_all('/[(]+[{]\s{0,}[^!]/m', $content, $matches);

		if (isset($matches[0]) && count($matches[0]) > 0)
		{
			$len = count($matches[0]) - 1;
		}

		$matches = null;
	}
	else
	{	
		preg_match_all('/[(]+[{]\s{0,}[!]/m', $content, $matches);

		if (isset($matches[0]) && count($matches[0]) > 0)
		{
			$len = count($matches[0]) - 1;
		}

		$matches = null;
	}

	$content = __bindrecursive($str, $content, $strict, $len);

	$strict = null;

	return $content;
}

function __bindrecursive($str, $content, $strict, $len) 
{
	if ($strict == false)
	{
		$pos = strpos($content, "({!");	
	}
	else
	{
		$pos = strpos($content, $str);
	}

	if ($pos !== false)
	{

		$thisline = substr($content, $pos);

		$ending = strpos($thisline, "})");

		$string = substr($thisline, 0, $ending) . '})';

		$string = trim($string);

		$continue = false;

		$replace = "";

		if ($strict === true)
		{
			if (preg_match('/[(]+[{]\s{0,}[!]/m', $string) == false)
			{
				$continue = true;
			}

			if ($continue == true)
			{
				$replace = str_replace("({", '<?=', $string);
				$replace = str_replace("})", '?>', $replace);
			}
		}
		else
		{
			if (preg_match('/[(]+[{]\s{0,}[!]/m', $string) == true)
			{
				$continue = true;
			}

			if ($continue == true)
			{
				$replace = preg_replace('/[(]+[{]\s{0,}+[!]/m', '<?php ', $string);
				$replace = str_replace("})", '?>', $replace);
			}
		}

		$content = str_replace($string, $replace, $content);

		if ($len != -1)
		{
			$len -= 1;
			$content = __bindrecursive($str, $content, $strict, $len);
		}

		$string = null;
		$ending = null;
		$thisline = null;
	}

	return $content;
}

function abspath($path)
{
	return absolutePath() . $path;
}


function lastIndex($needle, $haystack)
{
	$pos = -1;

	$split = str_word_count($haystack);

	if (is_array($split) && count($split) > 0)
	{
		for($i=count($split)-1; $i != 0; $i--)
		{
			if ($split[$i] == $needle)
			{
				$pos = $i;
				break;
			}
		}
	}

	$split = str_split($haystack);

	if (is_array($split) && count($split) > 0 && $pos == -1)
	{
		for($i=count($split)-1; $i != 0; $i--)
		{
			if ($split[$i] == $needle)
			{
				$pos = $i;
				break;
			}
		}
	}

	$split = null;

	return $pos;
}


function getAllFiles($dir)
{
	$files = [];

	$files = ___allfiles($dir);

	return $files;
}

function ___allfiles($dir)
{
	$file = [];

	$glob = glob(rtrim($dir, '/') .'/{,.}*', GLOB_BRACE);

	if (count($glob) > 0)
	{
		foreach ($glob as $i => $p)
		{
			if (basename($p) != '.' && basename($p) != '..')
			{
				$p = preg_replace("/[\/]{2}/", '/', $p);

				if (is_file($p))
				{
					$file[] = $p;
				}
				elseif (is_dir($p))
				{
					$file[] = ___allfiles($p);
				}
			}
		}
	}
	
	$glob = null;

	return $file;
}

function reduce_array($array)
{	
	$arr = [];
	$arra = __reduceArray($array, $arr);

	return $arra;
}

function __reduceArray($array, $arr)
{

	if (is_array($array))
	{
		foreach ($array as $a => $val)
		{
			if (!is_array($val))
			{
				$arr[] = $val;
			}
			else
			{
				foreach($val as $v => $vf)
				{
					if (!is_array($vf))
					{
						$arr[] = $vf;
					}
					else
					{
						$arr = __reduceArray($vf, $arr);
					}
				}
			}
		}
	}

	return $arr;
}


function PDOErrorDisplay($config = false, $e = false)
{

	\system\Inc\Controllers::$dropbox['noheader'] = true;

	if (is_array($config))
	{
		extract($config);

		$developer = developer('developer');

		$button = "";

		if (isset($create))
		{
			$button = '<a href="?table=create" class="btn mor-btn mor-reload">Auto Create Table</a>';
		}
		else
		{
			$button = '<a href="" class="btn mor-btn mor-reload">Retry</a>';
		}

		$has_suggestion = "";

		if (isset($suggestion))
		{
			$has_suggestion = "we suggest you try $suggestion,";
		}

		$code = $e->getCode();
		$line = $e->getLine();
		$file = $e->getFile();
		$str = $e->getMessage();

		$trace = $e->getTrace();

		$traceBack = "";

		if (isset($trace[1]))
		{
			$tf = $trace[1]['file'];

			if (strpos(basename($tf), "system") == 0)
			{
				$tf = str_replace('system/build/compiled/', PATH_TO_CONTROLLER, $tf);
				$tf = str_replace('system.', '', $tf);
				$tf = str_replace('.out', '.php', $tf);
			}
			elseif (strpos(basename($tf), "model") == 0)
			{
				$tf = str_replace('system/build/compiled/', PATH_TO_MODEL, $tf);
				$tf = str_replace('model.', '', $tf);
				$tf = str_replace('.out', '.php', $tf);
			}
			elseif (strpos(basename($tf), ".out") > 0)
			{
				$tf = str_replace(PATH_TO_OUT . 'compiled/', PATH_TO_VIEW, $tf);
				$tf = str_replace('.out', '.html', $tf);
			}

			$traceBack = '<h3> Trace </h3>
				<div>
					<code>File: '.$tf.'</code>
				</div>
				<div>
					<code>Line: '.$trace[1]['line'].'</code>
				</div>
			';
		}

		if (strpos($str, "/compiled/system.") >= 0)
		{
			$str = str_replace('system/build/compiled/', PATH_TO_CONTROLLER, $str);
			$str = str_replace('system.', '', $str);
			$str = str_replace('.out', '.php', $str);
		}
		elseif (strpos($str, "/compiled/model.") >= 0)
		{
			$str = str_replace('system/build/compiled/', PATH_TO_MODEL, $str);
			$str = str_replace('model.', '', $str);
			$str = str_replace('.out', '.php', $str);
		}
		elseif (strpos($str, ".out") > 0)
		{
			$str = str_replace(PATH_TO_OUT . 'compiled/', PATH_TO_VIEW, $str);
			$str = str_replace('.out', '.html', $str);
		}

		$body = <<< EOD
<style type="text/css">body{background:#212841;}</style>
<div class="mor-error-box">
<div class="container-fluid">
	<div class="row">
		<h1 class="error-box-title">PDO Statement Error</h1>
		<div class="col-lg-8" style="padding: 0;">
			<div class="statement-box">
				<div>
					<code style="display: block; padding: 10px; margin-bottom: 10px; ">
					$str
					</code>
				</div>

				<br>
				$traceBack
			</div>
		</div>

		<div class="col-lg-4 error-box-sidebar">
			<div class="error-box-suggestion">
				<h1>This may help</h1>
				<p>Hi $developer, $has_suggestion contact <a href="mailto:support@moorexa.com"> support@moorexa.com </a> for a quick guide. You could also try other possible means, like speak to <a href="mailto:hellojoesphi@gmail.com"> Joesphi</a>, he could be of help to you or maybe copy the error you see and ask other moorexa developers on <a href="https://www.stackoverflow.com" target="_blank">Stack Overflow</a>. Thank you for building with Moorexa. </p>
			</div>

			<div class="error-box-action">
				<h1>Avaliable Options</h1>
				$button
				<a href="javascript:void(0)" class="btn mor-btn mor-error">Dismiss</a>
			</div>
		</div>
	</div>
</div>
</div>
EOD;
		$code = null;
		Moorexa\PdoQueries::$errorBox[] = $body;

		$body = null;
	}
	elseif ($config === false)
	{
		$errorBox = Moorexa\PdoQueries::$errorBox;

		if (Moorexa\PdoQueries::$errorView == 0)
		{
			MoorexaErrorContainer::$messageOut = end($errorBox);
			Moorexa\PdoQueries::$errorView = 1;
		}

		$errorBox = null;

	}
}

function ErrorHelperLogger( $self = false )
{
	$errors = MoorexaErrorContainer::$errors;
	$suggestion = MoorexaErrorContainer::$suggestion;

	static $called = 0;

	$developer = config('public.developer');

	$button = '<a href="" class="btn mor-btn mor-reload">Check Again</a>';

	$error = '';

	if (count($errors) > 0)
	{
		foreach ($errors as $i => $err)
		{
			$error .= $err;
		}
	}

	$has_suggestion = "please contact <a href=\"mailto:support@moorexa.com\"> support@moorexa.com </a> for a quick guide";

	if (!empty($suggestion))
	{
		$has_suggestion = "<span class=\"i-could-suggest\">$suggestion</span>";
	}

	$title = 'Opps! Something went wrong.';

	if ($self != false)
	{
		$title = $self;
	}
		$body = <<< EOD
		<style type="text/css">body{background:#0a0b15;}</style><div class="mor-error-box wrapper"
		style="width: auto; max-width: 100%;"><div class=w1-end><div class=wrapper><h1
		class="error-box-title w1-end">{$title}</h1><div class=w1-13 style="padding: 0;"><div
		class=statement-box>{$error} </div></div><div class="w13-end error-box-sidebar"
		style="margin-left:2px"><div class=error-box-suggestion><h1>This may help</h1><p>Hi {$developer},
		{$has_suggestion}. You could also try other possible means, like speak to <a
		href="mailto:helloamadiify@gmail.com" style="text-decoration:none">@amadiify</a>, he could be of help
		to you or maybe copy the error you see and ask other moorexa developers on <a
		href="https://stackoverflow.com/questions/tagged/moorexa" target=_blank>Stack Overflow</a>. Thank
		you for building with Moorexa. </p></div><div class="error-box-action wrapper"><h1
		class=w1-end>Avaliable Options</h1><div class=w1-17>{$button} <a href="javascript:history.back()"
		class="btn mor-btn mor-error">Dismiss</a></div></div></div></div></div></div>
EOD;

	$has_suggestion = null;
	$button = null;
	$developer = null;
	$title = null;

	//ob_end_clean();

	if (!empty($error))
	{
		if ($called == 0)
		{
			echo $body;
			MoorexaErrorContainer::$messageOut = $body;
			$called += 1;
		}
	}
	
	$error = null;
	$body = null;
	//ob_flush();
}


// get controllers
function getControllers()
{
	$pages = glob(HOME . 'pages/*');

	$controllers = [];

	foreach ($pages as $i => $path)
	{
		if (is_dir($path))
		{
			$base = basename($path);

			$controllers[] = $base;
		}
	}

	return $controllers;
}

// load Package
function loadPackage()
{
	$file = trim(file_get_contents(PATH_TO_KERNEL . 'loadStatic.json'));

	$json = json_decode($file);

	$public = config('public');

	$public->title = config('title');

	$public = toArray($public);

	return (object) array_merge($public, (array) $json);
}

function handleCallback($func, $arg)
{
	return call_user_func_array($func, $arg);
}

function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2) | 0;
}

function convertToReadableSize($size, &$sbase=null){
	$base = log($size) / log(1024);
	$suffix = array("Byte", "KB", "MB", "GB", "TB");
	$f_base = floor($base);
	$convert = round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];

	$sbase = strtolower($suffix[$f_base]);

	if ($convert > 0)
	{
		return $convert;
	}

	return 0 . 'KB';
}

function arr2ini($a, $parent = array(), $space = true)
{
    $out = '';
    foreach ($a as $k => $v)
    {
        if (is_array($v))
        {
            //subsection case
            //merge all the sections into one array...
            $sec = array_merge((array) $parent, (array) $k);
            //add section information to the output
            if ($space === false)
            {
            	$out .= '[' . join('.', $sec) . ']' . PHP_EOL;
            }
            else
            {
            	$out .= "\n".'[' . join('.', $sec) . ']' . PHP_EOL;
            }
            
            //recursively traverse deeper
            $out .= arr2ini($v, $sec, $space);
        }
        else
        {
            //plain key->value case
            if ($space === true)
            {
            	$out .= "$k = $v" . PHP_EOL . "\n";
            }
            else
            {
            	$out .= "$k = $v" . PHP_EOL;
            }
        }
    }
    return $out;
}

function isaval($index, $arg)
{
	if (isset($arg[$index]))
	{
		return $arg[$index];
	}
	else
	{
		return null;
	}
}

function copyhead($file)
{
	$split = explode('.', $file);

	$path = "";

	if ($split[0] == 'template')
	{
		$path = HOME . 'custom/' . rtrim($file, '.php') . '.php';
	}
	else
	{
		$controller = $split[0];

		$path = HOME . 'pages/' . $controller . '/custom/' . rtrim($file, '.php') . '.php';
	}

	if (file_exists($path))
	{
		$content = file_get_contents($path);
		$head = strstr($content, "<head>");
		$end = strpos($head, "</head>");

		$head = substr($head, strlen("<head>"), $end - strlen('</head>'));

		$app = new Moorexa\View();

		//$sub = $app->read_output($path, true, $head);

		//include_once $sub;

		return $head;
	}
}


// get current url path
function geturladdress()
{
	$local = ['127.0.0.1', 'localhost'];

    if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != '127.0.0.1')
    {
    	if (isset($_SERVER['HTTP_HOST']))
    	{
    		$ref = "http://".rtrim($_SERVER['HTTP_HOST'], '/');
    	}
    	else
    	{
    		$ref = "http://localhost";	
    	}
    	
    }
    else
    {
		if (isset($_SERVER['REMOTE_ADDR']))
		{
			$ref = "http://".$_SERVER['REMOTE_ADDR'];	
		}
		else
		{
			$ref = abspath($_SERVER['PHP_SELF']);
		}	
	}
	
	if (strpos($ref, '/') > 0)
	{
		$folder = explode("/", $_SERVER['SCRIPT_NAME' ]);

		$refend = explode("/", $ref);

		$refend = $refend[ count($refend)-2 ];
		$mainfolder = isset($folder[ count($folder)-2 ]) ? $folder[ count($folder)-2 ] : '';

		if($folder != $refend)
		{
			array_pop($folder);
			array_pop($folder);

			if (isset($folder[count($folder)-1]) && $folder[count($folder)-1] != $mainfolder)
			{
				$folder = implode("/", $folder);
				$ref = $ref.$folder."/".$mainfolder;
			}
			else
			{
				$ref = $ref."/".$mainfolder;
			}
			
		}
	}


	return $ref .'/';

}

function abort( $code, $message = null)
{
	$handler = $_SERVER['SERVER_PROTOCOL']."  ". (!is_null($message) ? "  $code ". $message : "$code Page not found. ");

	echo($handler);

	// send header to the screen
	header($handler, true, $code);

	exit;
}

function matchinarray( $string, $arr, $seprator = " ", $match = 0)
{
	$found = false;
	$key = 0;
	$val = null;

	$str_arr = explode($seprator, $string);

	$match = $str_arr[$match];

	$allfirst = [];

	foreach ($arr as $a => $x)
	{
		if (is_string($x))
		{
			$x_arr = explode($seprator, $x);

			$first = $x_arr[0];

			$allfirst[] = $first;
		}
	}

	if (in_array($match, $allfirst))
	{
		$found = true;
	}	

	return $found;
}

function savearray($array, $mask)
{
	$build  = '<?php';
	$build .= "\n\n";
	$build .= $mask;
	$build .= " = [\n\n";


	foreach ($array as $key => $val)
	{
		$build .= "\t'$key' => ";

		if (is_array($val))
		{
			$build .= "[";

				foreach ($val as $i => $x)
				{
					if (is_string($i))
					{
						$build .= "'$i' => ";
						$build .= (is_int($x) || is_float($x) || is_double($x) ? $x : "'$x'");
						$build .= ", ";
					}
					else
					{
						$build .= (is_int($x) || is_float($x) || is_double($x) ? $x : "'$x'");
						$build .= ", ";
					}
				}

			$build = rtrim($build, ", ");

			$build .= "]";
		}
		else
		{
			$build .= (is_int($val) || is_float($val) || is_double($val) ? $val : "'$val'");
		}

		$build .= ",\n";
	}

	$build .= "\n];\n";

	return $build;

}

function load_css_files($file)
{
	$static = "";

	if (is_array($file))
	{
		foreach ($file as $i => $path)
		{
			$static .= '<link rel="stylesheet" type="text/css" href="'.$path.'">'."\n";
		}
	}
	else
	{
		$static .= '<link rel="stylesheet" type="text/css" href="'.$file.'">'."\n";
	}

	return $static;
}

function load_js_files($file)
{
	$static = "";

	if (is_array($file))
	{
		foreach ($file as $i => $path)
		{
			$static .= '<script type="text/deffered" data-src="'.$path.'" ></script>'."\n";
		}
	}
	else
	{
		$static .= '<script type="text/deffered" data-src="'.$file.'"></script>'."\n";
	}

	return $static;
}

function when_static($data, $callback)
{
	if (isset(Moorexa\Bootloader::$helper['isonline']))
	{
		$isonline = Moorexa\Bootloader::$helper['isonline'];

		if (isset($data['any']))
		{
			return call_user_func($callback, $data['any']);
		}
		else
		{
			if (!$isonline && isset($data['dev']))
			{
				return call_user_func($callback, $data['dev']);
			}
			else
			{
				if (isset($data['live']) && $isonline === true)
				{
					return call_user_func($callback, $data['live']);	
				}
			}
		}
		
	}
}

function get_query()
{
	$url = null;

	if (isset($_SERVER['QUERY_STRING']))
	{
		$r_query = $_SERVER['QUERY_STRING'];

		$parse = parse_query($r_query);

		$app = isset($parse['app']) ? $parse['app'] : "";

		unset($parse['app']);

		$other = "";

		if (count($parse) > 0)
		{
			$other = '?';

			foreach ($parse as $key => $value)
			{
				$other .= $key .'='.$value.'&';
			}

			$other = rtrim($other, '&');
		}

		$url = $app . $other;
	}


	return rawurlencode($url);
}

function location($index = 0)
{
	$url = isset($_GET['__app_request__']) ? explode('/', $_GET['__app_request__']) : Moorexa\Bootloader::$helper['location.url'];

	if (is_string($index))
	{
		if ($index == '--all')
		{
			return $url;
		}
	}
	elseif (is_int($index) && $index >= 0)
	{
		if (isset($url[$index]))
		{
			return $url[$index];
		}

		return false;
	}	

	$pp = null;

	if (end($url) == "")
	{
		$pp = array_shift($url);
	}
	else
	{
		$pp = end($url);
	}

	return $pp;
}

function array_size($var, $file)
{
	$cont = file_get_contents($file);
	$array = strstr($cont, $var);
	$array = substr($array, strpos($array, '=')+1);
	$array = trim($array);
	$array = rtrim($array,';');
	var_dump(exec($array));
}

function read_json($path, $toarray = false)
{
	if (file_exists($path))
	{
		$data = trim(file_get_contents($path));

		if (substr($data, 0,1) == "{" && strlen($data) > 3)
		{
			$json = json_decode($data);

			if ($toarray)
			{
				return (array) $json;
			}

			return $json;
		}
		else
		{
			if ($toarray)
			{
				return [];
			}

			return (object) [];
		}
	}
	else
	{
		\Moorexa\Event::emit('json.error', $path . 'doesn\'t exists. Please check file path.');
		
		if (env('bootstrap','debugMode') == 'on')
		{
			error($path . 'doesn\'t exists. Please check file path.');
		}
	}
}

function save_json($path, $data)
{
	if (is_array($data))
	{
		$dec = json_encode($data, JSON_PRETTY_PRINT);
	}
	else
	{
		$conv_arr = (array) $data;
		$dec = json_encode($conv_arr, JSON_PRETTY_PRINT);
	}

	if (file_exists($path) && is_writable($path))
	{
		file_put_contents($path, $dec);
	}
	else
	{
		\Moorexa\Event::emit('json.error', $path . 'isn\'t writable or doesn\'t exists.');

		if (env('bootstrap','debugMode') == 'on')
		{
			error($path . 'isn\'t writable or doesn\'t exists.');
		}
	}
}

function csrf_token()
{
	// get token
	$token = \Moorexa\Bootloader::$csrfToken;

	// get current path
	$path = $_SERVER['PHP_SELF'];

	$tokens = session()->get('path.csrf.tokens');

	if ($tokens === false)
	{
		$tokens = [];
	}

	// set token
	$tokens[$path] = [
		'id' => session_id()
	];

	// save token for this path
	session()->set('path.csrf.tokens', $tokens);

	// token is not null then render
	if ($token !== null)
	{
		$tag = '<input type="hidden" name="CSRF_TOKEN" value="'.$token.'">';

		return $tag;
	}
	else
	{
		return null;
	}
}

function is_avail($id, $array)
{
	if (isset($array[$id]))
	{
		return $array[$id];
	}
	else
	{
		return null;
	}
}


function run_middleware($bus, $callback)
{
	if (count($bus) > 1)
	{
		$response = [];

		foreach ($bus as $i => $middleware)
		{
			if (!empty($middleware))
			{
				if (strtolower($middleware) != 'auth' && strtolower($middleware) != 'authentication')
				{
					$response[$middleware] = Moorexa\Middleware::{$middleware}();
				}
				else
				{
					$response[$middleware] = new Moorexa\Middleware('auth');
				}
			}
		}

		$response = (object) $response;

		call_user_func($callback, $response);

		Moorexa\Middleware::$active = (array) $response;
	}
	else
	{
		$key = $bus[0];

		if (strtolower($key) != 'auth' && strtolower($key) != 'authentication')
		{
			$bus = Moorexa\Middleware::{$key}();	
		}
		else
		{
			$bus = new Moorexa\Middleware('auth');
		}
		
		call_user_func($callback, $bus);

		Moorexa\Middleware::$active = [$key => $bus];
	}
}


// dig function
function dig($dir, $file)
{
	$find = deepScan($dir, $file);

	if (!empty($find))
	{
		return $find;
	}
	else
	{
		return HOME;
	}
}

// finder
function finder($find)
{
	if (class_exists('\Moorexa\Bootloader'))
	{
		$finder = \Moorexa\Bootloader::$finder;

		if (!is_null($finder))
		{
			return $finder($find);
		}
	}

	return new \Objects();
}

function env($type, $name = null, $object = true)
{
	$envariables = env::$variables;
	
	if (strtolower($type) == 'db')
	{
		if ($name === true || $name == null)
		{
			// current db
			$name = \Moorexa\DatabaseHandler::$connectWith;
		}

		$config = \Moorexa\DatabaseHandler::readvar($name);

		return ($object === true) ? ($config !== null) ? (object) $config : $config : $config;
	}
	elseif($type == 'database')
	{
		return \Moorexa\DatabaseHandler::readvar();
	}
	else
	{
		$type = strtolower($type);

		$config = null;

		if (isset($envariables[$type.'.'.$name]))
		{
			return $envariables[$type.'.'.$name];
		}

		if ($type != 'bootstrap')
		{
			$bootstrap = env::$config_env['bootstrap'];

			if (isset($bootstrap[$type]))
			{
				$config = $bootstrap[$type];
			}
			else
			{
				if (!is_null($name))
				{
					env::$variables[$type] = $name;

					return $name;
				}

				$config = \Moorexa\View::$packagerJson;
			}
		}
		else
		{	
			$config = isset(env::$config_env[$type]) ? env::$config_env[$type] : null;
		}

		if (!is_null($config))
		{	
			if ($name !== null)
			{
				if (!isset($config[$name]))
				{
					\Moorexa\View::$packagerJson[$type] = $name;
				}

				$env = isset($config[$name]) ? $config[$name] : null;

				return $env;
			}
			else
			{
				return $config;
			}
		}

	}

	return null;
}

// set environment var
function envset($type, $name, $val)
{
	if (strtolower($type) == 'db' || $type == 'database')
	{
		if ($name === true || $name == null)
		{
			// current db
			$name = \Moorexa\DatabaseHandler::$connectWith;
		}

		$config = \Moorexa\DatabaseHandler::readvar($name);

		$config[$name] = $val;
	}
	else
	{
		$type = strtolower($type);
		$config = env::$config_env;

		if (isset($config[$type]))
		{	
			$config[$type][$name] = $val;
			env::$config_env = $config;

			if ($type == 'bootstrap')
			{
				Moorexa\Dependencies::$initials['config'] = $config;
			}
		}
	}
}

function server($callback)
{
	$const = [];
	$server = $_SERVER;
	$other = [];
	foreach ($server as $key => $val)
	{
		$other[strtolower($key)] = $val;
	}
	$server = null;

	\Moorexa\Route::getParameters($callback, $const, [$other]);

	return call_user_func_array($callback, $const);
}

// quick documentation
function quickdoc($data = null)
{
	$isonline = isset(Moorexa\Bootloader::$helper['isonline']) ? Moorexa\Bootloader::$helper['isonline'] : false;

	if (!($isonline))
	{
		if (is_array($data) && isset($data['path']))
		{
			$path = $data['path'];

			$file = file_get_contents($path);

			if (preg_match('/[@]+\s{0,}(quickdoc)/i', $file) == true)
			{

				$document = "";

				// get captures
				preg_match_all('/[@]+\s{0,}(quickdoc)+(.*)/im', $file, $matches);

				$sha256 = sha1(implode('::', $matches[0]));

				$continue = true;

				$base = basename($path);

				$base = substr($base, 0, strpos($base, '.'));

				$fn = substr(md5($path), 0,3).'_'.$base . '.doc.txt';

				if(file_exists(HOME .'help/quickdoc/'.$fn))
				{
					$match = preg_match("/($sha256)/", file_get_contents(HOME . 'help/quickdoc/'.$fn));

					if ($match)
					{
						$continue = false;
					}

					$match = null;

				}

				$fn = null;
				$base = null;

				if ($continue)
				{
					if (count($matches) > 0)
					{
						$lines = explode("\n", $file);

						foreach ($matches[0] as $i => $data)
						{
							if (preg_match('/(capture)\s{0,}[:]\s{0,}+[\(]/', $data) == true)
							{
								$begin = strstr($file, $data);

								if (strpos($begin, '@endcapture') > 0)
								{
									$pos = strpos($begin, '@endcapture');
									$string = substr($begin, 0, $pos);
									$string = str_replace($data, '', $string);

									$bf = $data;
									$data = str_replace('@quickdoc.', '', $data);
									$data = str_replace('@quickdoc{', '', $data);
									$data = rtrim($data, '}');

									$data = preg_replace('/(capture)+\s{0,}[:]\s{0,}[(]/', '', $data);

									$data = preg_replace('/([,]+\s{0,})$/', '', $data);

									$data = preg_replace('/([\w]+)\s{0,}[:]/', "\n".'[$1]'."\n", $data);

									$rl = __readline($lines, $bf, true, 1);

									$string = preg_replace('/\t{1,}/', " ", $string);

									$document .= $data . "\n";
									$document .= "========(line : $rl->line)=====\n";
									$document .= trim($string) ."\n";

									$document .= "\n\n";


									$pos = null;
									$string = null;
									$bf = null;
									$data = null;
									$rl = null;


								}

								$begin = null;
							}
							else
							{
								$bf = $data;
								$data = str_replace('@quickdoc.', '', $data);
								$data = str_replace('@quickdoc{', '', $data);
								$data = rtrim($data, '}');

								$data = preg_replace('/([\w]+)\s{0,}[:]/', "\n".'[$1]'."\n", $data);

								$rl = __readline($lines, $bf, true, 1);

								$document .= $data . "\n";
								$document .= "========(line : $rl->line)=====\n";
								$document .= trim($rl->next) . "\n";
								$document .= trim($rl->shift);

								$document .= "\n\n";

								$rl = null;
								$data = null;
								$bf = null;
							}
						}
					}

					if (!empty($document))
					{
						$date = date('Y-m=d'); $time = date('g:i:s a');

						$header = "
						/*
						 ****************************\n
						 * Quick Documentation \n
						 * FilePath : $path\n
						 * Basic-Salt : $sha256\n 
						 * Date : $date\n 
						 * Time : $time\n
						 */\n\n
						";

						$header = preg_replace('/\t{0,}/', '', $header);
						$header = preg_replace('/\n{0,}/', '', $header);

						$header .= "\n";

						$document = $header . $document;

						$base = basename($path);

						$base = substr($base, 0, strpos($base, '.'));

						$fn = substr(md5($path), 0,3).'_'.$base . '.doc.txt';

						@file_put_contents(HOME . 'help/quickdoc/' . $fn, $document);

						$header = null;
						$document = null;
						$base = null;
						$fn = null;
						$date = null;
						$time = null;
					}
				}
			}

			$file = null;
			$path = null;
			$data = null;
			$isonline = null;
		}
	}
}

// private function readlines of a script
function __readline($lines, $data, $next = false, $shift = 0)
{
	if (is_array($lines))
	{
		$response = [];

		foreach ($lines as $line => $code)
		{
			if (strstr($code, $data) != false)
			{
				$response['line'] = $line+1;

				$ne = 0;

				if ($next)
				{
					$ne += 1;
					$response['next'] = isset($lines[$line+1]) ? $lines[$line+1] : null;
				}

				if ($shift > 0)
				{
					$append = "";

					for ($i=($line+$ne+1); $i <= ($line + $ne + $shift); $i++)
					{
						$append .= $lines[$i] . "\n";
					}

					$append = rtrim($append, "\n");

					$response['shift'] = $append;

					$append = null;

				}
				break;
			}
		}

		$code = null;
		$line = null;
		$lines = null;

		return (object) $response;
	}
}


// read xml
function readXml($path)
{
	$xml = simplexml_load_file($path);

	$xmlArr = (array) $xml;

	$xmlw = json_encode($xml);

	$xmlw = str_replace('@attributes', 'attr', $xmlw);

	preg_match_all('/["]+(attr)+["]+[:]([^}]+)+[}]/', $xmlw, $matches);

	if (count($matches) > 0)
	{
		if (count($matches[0]) > 0)
		{
			foreach ($matches[0] as $i => $line)
			{
				$replace = $line;

				$line = str_replace('"attr":{', '', $line);
				$line = rtrim($line, "}");
				
				$xmlw = str_replace($replace, $line, $xmlw);
			}
		}
	}

	return json_decode($xmlw);

	
}

// convert array to object
function toObject($array)
{
	$res = (object) [];

	foreach ($array as $i => $x)
	{
		if (is_array($x))
		{
			$x = toObject($x);

			$res->{$i} = (object) $x;
		}
		else
		{
			$res->{$i} = $x;
		}
	}

	return $res;
}

// conver object to array
function toArray($object, $id = null)
{
	$res = [];

	$res = json_encode($object);
	$dec = json_decode($res, true);

	return $dec;
}


function config($id, $key = null)
{
	if (!is_null($key))
	{
		\Env::$config[$id] = $key;
	}

	if (isset(\Env::$config[$id]))
	{
		return \Env::$config[$id];
	}

	$find = "";

	$br = explode('.', $id);

	$xml = readXml(PATH_TO_CONFIG . 'config.xml');

	if (count($br) > 0)
	{
		$res = ___config($xml, $br);

		if ($id == 'router.default.controller' && is_object($res))
		{
			return '/';
		}

		return $res;
	}

	return $xml;
}


function ___config($xml, $br, $id = 0)
{
	if (count($br) != 0)
	{	
		$f = array_shift($br);

		if (isset($xml->{$f}))
		{
			$xml = ___config($xml->{$f}, $br);
		}
		else
		{
			$xml = null;
		}
	}
	else
	{
		return $xml;
	}

	return $xml;
}

function findFolder($from, $folder)
{
	$progress = ['found' => false, 'path' => ""];
	$dir = __findFolderDeep($from, $folder, $progress);

	if ($progress['found'])
	{
		return $progress['path'];
	}
}

function __findFolderDeep($from, $folder, &$progress)
{
	$seek = glob($from.'/*');

	foreach ($seek as $i => $next)
	{
		$next = preg_replace('/[\/]{2}/', '/', $next);

		if (is_dir($next) && basename($next) != $folder)
		{
			$progress = __findFolderDeep($next, $folder, $progress);
		}

		if (is_dir($next) && basename($next) == $folder)
		{
			$progress['found'] = true;
			$progress['path'] = $next;
			break;
		}
	}

	return $progress;
}

function is($check, $data)
{
	return $data === $check;
}

function http_response($code, $flag = "", $text = "")
{
	$app = new \Moorexa\View();
	$app->apptitle = "Main.php not found";
	$app->template = false;
	$app->render($code, $flag, $text);
	$app = null;
}


function ___listen_for_event()
{
	$debug = env('bootstrap', 'debugMode');

	if ($debug == 'on')
	{
		// ok check if event emmitted
		$events = \Moorexa\Event::$emmitted;

		if (count($events) > 0)
		{
			?>	
				<div class="event-listeners">

					<div class="event-card">
						<span class="count"><b>2</b></span> <span class="text">Events Emmitted</span>
					</div>

					<div class="event-list">
						<ul>
							<li><code>csrf.token</code> triggered <br>
								<code>Response: shss</code>
							</li>

							<li><code>csrf.token</code> triggered <br>
								<code>Response: shss</code>
							</li>
						</ul>
					</div>
					</div>

					<style>
						.event-listeners{position: fixed; bottom:0%; right: 0; 
						box-shadow: 0px 10px 10px rgba(0,0,0,0.3); transition:bottom 0.6s ease-in-out;
						background: #fff; height: auto; font-family:'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;}
						.event-card{width:200px; height: auto; background:#246ee6; color: #fff; padding: 7px;
						display: flex; align-items: baseline; padding-left: 10px;}
						.event-card:hover{cursor: pointer;}
						.event-card .count{display: inline-block; width: 30px; height: 30px; background: #0949c6;
						display: flex; margin-right: 20px; border-radius: 50%; font-size: 70%;}
						.event-card span b{display: block; margin: auto; font-weight: normal;}
						.event-list ul{margin: 0; padding: 10px; list-style: none;}
						.event-list ul li{box-shadow: 0px 0px 4px rgba(0,0,0,0.1); background: #f9f6f6; padding: 10px; margin-bottom: 15px;}
						.event-list ul li code{background:#fff; padding: 5px; line-height: 30px;}
						.event-list ul li code:nth-child(1){background: rgb(36, 110, 230); color:#fff;}
						.event-card .text{font-size: 14px;}
					</style>

					<script>
						var ls = document.querySelector('.event-listeners');
						var hg = ls.offsetHeight;

						var push = hg - 40;
						ls.style.bottom = '-' + push + 'px';

						ls.firstElementChild.addEventListener('click', (e)=>{
							if (!e.target.hasAttribute('data-clicked'))
							{
								ls.style.bottom = '0px';
								e.target.setAttribute('data-clicked', true);
							}
							else
							{
								e.target.removeAttribute('data-clicked');
								ls.style.bottom = '-' + push + 'px';
							}
						});
					</script>
			<?php
		}
	}
}

// get all controllers
function get_controllers()
{
	$cnt = glob(HOME . 'pages/*');
	$contr = [];

	foreach ($cnt as $i => $fld)
	{
		if (is_dir($fld))
		{
			$contr[] = basename($fld);
		}
	}

	$cnt = null;

	return $contr;
}

// get all views for a controller
function get_views($controller = null)
{	
	if ($controller === null)
	{
		// get current controller;
		$controller = \Moorexa\Bootloader::$helper['get_controller'];
	}
	
	$path = HOME . 'pages/'. $controller . '/main.php';

	$cont = file_exists($path) ? $path : false;

	if ($cont !== false)
	{
		include_once($cont);

		$ref = new \ReflectionClass($controller);
		$meth = $ref->getMethods();

		$views = [];

		foreach ($meth as $i => $func)
		{
			if ($func->class == ucfirst($controller))
			{
				if ($func->name != '__construct')
				{
					$views[] = $func->name;
				}
			}
		}

		$ref = null;
		$meth = null;
		$cont = null;
		$path = null;

		return $views;
	}

	return [];
}


function modal_box($type, $message, $return = false)
{
	$assets = new \Moorexa\Assets();

	$image = $assets->image['icons/'.$type.'.png'];

	$string = <<<EOD
	<section data-type="modal-wrapper">
		<section data-type="modal-inner">
			<main data-type="modal-content">
				<aside data-type="modal-icon">
					<img src="$image">
				</aside>

				<aside data-type="modal-text">
					<p>$message</p>
				</aside>
			</main>
			<footer data-type="modal-footer" data-modal-type="$type">
				<a href="" data-type="modal-cancel"> <span>Cancel</span> </a>
				<a href="" data-type="modal-continue"> <span>Continue</span> </a>
			</footer>
		</section>
	</section>	

	<script type="text/javascript">
		let cancel = document.querySelector('*[data-type="modal-cancel"]');
		let cont = document.querySelector('*[data-type="modal-continue"]');
		let wrap = document.querySelector('*[data-type="modal-wrapper"]');

		cancel.addEventListener('click', func);
		cont.addEventListener('click', func);

		function func(e){
			e.preventDefault();
			e.cancelBubble = false;

			wrap.style.opacity = 0;
			setTimeout(function(){
				wrap.style.display = 'none';
			},600);
		}

		if (!wrap.hasAttribute('data-modal-out'))
		{
			wrap.style.display = 'flex';
			setTimeout(function(){
				wrap.style.opacity = 1;
				wrap.setAttribute('data-modal-out', true);
			},100);
		}
	</script>
EOD;

	if ($return)
	{
		return $string;
	}
	else
	{
		echo $string;
	}
}

// create a new object function.
function Object()
{
	$obj = new \Objects();
	$args = func_get_args();

	if (count($args) > 0)
	{
		foreach ($args as $i => $a)
		{
			if (is_string($i))
			{
				$obj->{$i} = $a;
			}
		}
	}
	return $obj;
}

function method_exist($class, $cont, $view)
{
	$ref = new \ReflectionClass($class);
	$cont2 = ucfirst($cont);
	$methods = $ref->getMethods();

	$ret = false;

	foreach ($methods as $i => $Obj)
	{
		if (strcmp($Obj->class, $cont2) === 0 || strcmp($Obj->class, $cont) === 0)
		{
			if (strcmp($Obj->name, $view) === 0)
			{
				$ret = true;
				break;
			}
		}
	}

	return $ret;
}

function get_methods($class)
{
	if (is_string($class) && class_exists($class))
	{
		$ref = new \ReflectionClass($class);
		$methods = $ref->getMethods();
		
		$class = $ref->name;

		$classMethod = [];

		foreach ($methods as $i => $Obj)
		{
			if ($Obj->class == $class)
			{
				$classMethod[] = $Obj->name;
			}
		}

		$methods = null;
		$ref = null;

		return $classMethod;
	}

	return [];
}


function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

// remove all single line comments
function singleline($minjs, $level = 5, $current = 1, $from = null)
{
	preg_match_all('/(^((?![a-zA-Z|\\\\|\"|\'|\s])|([\s|\t|\n|\r]))|([\s|;]))+(\/\/)([\s\S]*?\n)/', $minjs, $matches);
	if (count($matches) > 0 && count($matches[0]) > 0)
	{
		$continue = true;

		foreach ($matches[0] as $i => $match)
		{
			$begin = $match[0];

			$trim = trim($match);
			$end = substr($trim, -2);

			if ($end != "';")
			{
				$new = preg_replace('/(\/\/)([\s\S]*?\n)/','',$match);
				$new = preg_replace('/(\/\/){1,}/','',$new);

				if (trim($new) == "")
				{
					$minjs = str_replace($match, $new, $minjs);
				}
				
				if (preg_match('/([^;])/', trim($new)))
				{
					if (preg_match('/(\|\|)/', $new) && substr(ltrim($new),0,2) == '||')
					{
						$new = preg_replace('/\s{1,}/'," ",$new);
						$minjs = str_replace($match, $new, $minjs);
					}
				}
				else
				{
					$minjs = str_replace($match, $new, $minjs);
				}

				$continue = true;

			}
			else
			{
				if (preg_match('/(\s{2,}|\n{1,})(\/\/)/',$match))
				{
					$new = preg_replace('/(\/\/)([\s\S]*?\n)/','',$match);
					$new = preg_replace('/(\/\/){1,}/','',$new);
					

					$minjs = str_replace($match, $new, $minjs);
				}	
			}
		}

		preg_match_all('/(^((?![a-zA-Z|\\\\|\"|\'|\s])|([\s|\t|\n|\r]))|([\s|;]))+(\/\/)([\s\S]*?\n)/', $minjs, $matches);
		
		if (count($matches) > 0 && count($matches[0]) > 0)
		{
			$bf = $minjs;

			if ($current < $level)
			{
				$minjs = singleline($minjs, $level, ($current+1), $from);
				$minjs = is_null($minjs) ? $bf : $minjs;
			}
		}
	}

	return $minjs;
}

// remove new line single comment
function removeNewlineComment($minjs)
{
	preg_match_all('/([\n|\s|}|{|;|\t|)|,])(\/\/)([^\n]*?)[\\\\][n]/', $minjs, $match);
	if (count($match) > 0 && count($match[0]) > 0)
	{
		foreach($match[0] as $i => $ma)
		{
			$with = $match[1][$i];
			$minjs = str_replace($ma, $with, $minjs);
		}

		$minjs = removeNewlineComment($minjs);
	}

	return $minjs;
}

function correctfunctions($minjs, $level = 1)
{
	$bf = $minjs;
	preg_match_all('/(\}\(\)|\(jQuery\)|\}\(\)\);)\s{0,},(function\(([\s\S]*?[\}]{1,2}\s{0,}\(\),))/', $minjs, $matches);
	
	if ($level == 2)
	{
		preg_match_all('/(\}\(\)|\(jQuery\)|\}\(\)\);)\s{0,},(function\(([\s\S]*?[\}]{1,2}\s{0,}\(\)))/', $minjs, $matches);
	}

	if ($level == 3)
	{
		preg_match_all('/(\}\(\)|\(jQuery\)|\}\(\)\);)\s{0,}(function\(([\s\S]*?[\}]{1,2}\s{0,}\(\);))/', $minjs, $matches);
	}

	if (is_array($matches) && count($matches) > 0 && count($matches[0]) > 0)
	{
		foreach ($matches[0] as $i => $ma)
		{
			$query = $matches[1][$i];
			$func = rtrim($matches[2][$i], ',');

			$build = $query . ';(' . $func;

			if($level == 1)
			{
				$build .= ');';
			}
			else
			{
				if (!preg_match('/(jQuery)/', $query) && $level == 2)
				{
					$build .= ');';
				}
				elseif ($level = 3)
				{
					$build = rtrim($build, ';') . ');';
				}
			}

			$minjs = str_replace($ma, $build, $minjs);
		}

		$minjs = correctfunctions($minjs, $level);
	}
	else
	{
		if (is_array($matches))
		{
			if ($level == 1)
			{
				$minjs = correctfunctions($minjs, 2);
			}
			elseif ($level == 2)
			{
				$minjs = correctfunctions($minjs, 3);
			}
		}
	}

	$minjs = is_null($minjs) ? $bf : $minjs;

	return $minjs;
}

function correctJquery($minjs)
{
	preg_match_all('/[)]{1}(jQuery+\()/', $minjs, $matches);

	if (is_array($matches) && count($matches) > 0 && count($matches[0]) > 0)
	{
		foreach ($matches[0] as $i => $x)
		{
			$rep = ");jQuery(";
			$minjs = str_replace($x, $rep, $minjs);
		}
		
		$minjs = correctJquery($minjs);
	}

	return $minjs;
}

function jqueryCallback($minjs)
{
	// preg_match_all('/(function\()([\s\S]*?}\)\(jQuery\))([;|\s])/', $minjs, $match);
	// if (is_array($match) && count($match) > 0 && count($match[0]) > 0)
	// {
	// 	foreach ($match[0] as $i => $x)
	// 	{
	// 		$minjs = str_replace($x, $x.';', $minjs);
	// 	}
	// 	$minjs = jqueryCallback($minjs);
	// }

	return $minjs;
}

function intermidiateFunction($minjs)
{
	preg_match_all('/(\)\s{1,}\(function\()/', $minjs, $match);
	if (count($match) > 0 && count($match[0]) > 0)
	{
		$bf = $minjs;
		foreach ($match[0] as $i => $x)
		{
			$rep = ");\n(function(";
			$quote = preg_quote($x);
			$minjs = str_replace($x, $rep, $minjs);
			$minjs = preg_replace("/($quote)/", $rep, $minjs);
		}
		$minjs = intermidiateFunction($minjs);
	}

	return $minjs;
}

// alternative to array_map
function array_each($callback, $array)
{
	if (is_array($callback))
	{
		$copy = $callback;
		$callback = $array;
		$array = $copy;
		$copy = null;
	}

	if (is_array($array) && count($array) > 0)
	{
		$ref = new \ReflectionFunction($callback);
		$params = $ref->getParameters();
		$leng = count($array);

		$new_array = []; 

		$is_numeric = false;

		foreach ($array as $key => $val)
		{
			$returned = call_user_func($callback, $val, $key, $array);

			if ($returned != null)
			{
				$new_array[$key] = $returned;
			}
		}

		return $new_array;
	}
}

// load packed data
function packed($data)
{
	$data = explode('.', $data);
	if (is_array($data))
	{
		$dropBox = \Moorexa\Controller::$dropbox;
		$main = $data[0];
		if (isset($dropBox[$main]))
		{
			unset($data[0]);
			$build = "";
			foreach ($data as $i => $key)
			{
				$build .= $key .'->';
			}
			$build = rtrim($build, '->');
			if (is_object($dropBox[$main]) && isset($dropBox[$main]->{$build}))
			{
				return $dropBox[$main]->{$build};	
			}
		}
	}
}

// export variables 
function export_variables($args=null)
{
	$caller = debug_backtrace()[0];
	$filename = $caller['file'];
	$startline = $caller['line'] - 1;
	$length = ($startline + 10) - $startline;

	$source = file($filename);
	$func = implode("", array_slice($source, $startline, $length));
	$func = substr($func, 0, strpos($func, ';'));
	$func = trim($func);
	$func = str_replace('export_variables', '', $func);
	$func = preg_replace('/(^[\(])|([\)]$)/', '', $func);

	$funcArr = explode(',', $func);

	$args = func_get_args();
	
	// store data
	$data = [];
	
	if (count($funcArr) > 0)
	{
		foreach ($funcArr as $i => $key)
		{
			if (preg_match('/[=]/', $key))
			{
				$key = trim(substr($key, 0, strpos($key, '=')));
			}

			if (preg_match('/[->]/', $key))
			{
				$key = trim(substr($key, strpos($key, '->')+2));
			}

			$key = trim($key);

			$key = preg_replace('/^[$]/', '', $key);

			if (strlen($key) > 0)
			{
				$data[$key] = $args[$i];
			}
		}
	}

	if (count($data) > 0)
	{
		// ok we good;
		// export data now.
		\Moorexa\Controller::$dropbox = array_merge(\Moorexa\Controller::$dropbox, $data);
	}

}


function notFound($value, $array)
{
	$continue = true;

	foreach ($array as $key => $val)
	{
		if (!is_object($val) && !is_array($val))
		{
			if ($val == $value)
			{
				$continue = false;
				break;
			}
		}
		elseif (is_object($val) || is_array($val))
		{
			if (is_object($val))
			{
				$val = toArray($val);
			}

			$continue2 = notFound($value, $val);
			if ($continue2 === false)
			{
				$continue = false;
				break;
			}
		}
	}
	return $continue;
}

function stringToArray($string) {
	if (!preg_match('/[\]]$/', $string))
	{
		$string .= ']';
	}

	$string = "return " . $string . ";";
	if (function_exists("token_get_all")) {//tokenizer extension may be disabled
		$php = "<?php\n" . $string . "\n?>";
		$tokens = token_get_all($php);
					foreach ($tokens as $token) {
			$type = $token[0];
			if (is_long($type)) {
				if (in_array($type, array(
						T_OPEN_TAG, 
						T_RETURN, 
						T_WHITESPACE, 
						T_ARRAY, 
						T_LNUMBER, 
						T_DNUMBER,
						T_CONSTANT_ENCAPSED_STRING, 
						T_DOUBLE_ARROW, 
						T_CLOSE_TAG,
						T_NEW,
						T_DOUBLE_COLON
						))) {
					continue;
				}
			}
		}
	}

	$string = preg_replace("/(<php-var>|<\/php-var>)/", '', $string);

	extract(\Moorexa\Controller::$dropbox);

	return eval($string);
}

// verify csrf_token
function verify_token(&$error)
{
	if (isset(System::$local_vars['csrf_verify']) && System::$local_vars['csrf_verify'] === true)
	{
		return true;
	}

	if (isset($_POST['CSRF_TOKEN']))
	{
		$ctoken = decrypt($_POST['CSRF_TOKEN']);

		if (strlen($ctoken) > 20)
		{
			// get session id
			$sessionid = session_id();

			// get current path
			$path = $_SERVER['PHP_SELF'];

			// get tokens for paths
			$tokens = session()->get('path.csrf.tokens');

			if ($tokens !== false)
			{
				if (isset($tokens[$path]))
				{
					// recover session id
					$sessionid = $tokens[$path]['id'];
				}
			}

			// get salt
			$salt = \Moorexa\View::$packagerJson['csrf_salt'];

			// explode ctoken
			$array = explode('/', $ctoken);

			// ok lets build a new token
			// build token with app url
			$token = md5(url($sessionid)) . 'salt:'.$salt.'/'.$array[1].'/sessionid:'.$sessionid;

			// now we verify 
			if ($token == $ctoken)
			{
				// regenerate id
				session_regenerate_id();

				if ($tokens !== false)
				{
					// remove token
					unset($tokens[$path]);

					// set token
					session()->set('path.csrf.tokens', $tokens);
				}

				// token matched
				return true;
			}
			else
			{
				$key = !isset($key) ? 'CSRF-TOKEN' : $key;

				$error = ['error' => $key.' sent with this form has expired. Please reload page and resubmit form.'];

				return false;
			}
			
		}
		else
		{
			$error = ['error' => 'Invalid CSRF TOKEN'];
			
			return false;
		}
		
	}

	$error = ['error' => $key . ' not sent with Form.'];
	return false;
}

function allImages($path = "")
{
	$images = [];
	
	$path = PATH_TO_IMAGE . $path . '/';

	$all = glob($path. '*');

	if (count($all) > 0)
	{
		foreach ($all as $i => $any)
		{
			if (is_file($any))
			{
				$info = mime_content_type($any);
				if (preg_match('/^(image)/i', $info))
				{
					$images[] = preg_replace('/[\/]{2}/','/', $any);
				}
			}
			elseif (is_dir($any))
			{
				$any = str_replace(PATH_TO_IMAGE, '', $any);
				$imageDir = allImages($any);
				$images = array_merge($images, $imageDir);
			}
		}
	}
	
	return $images;
}

// csrf verfied
function csrf_verified(&$post, $convert = false)
{
	if (\Moorexa\Bootloader::$csrfVerified === false && isset($_POST['CSRF_TOKEN']))
	{		
		$token = verify_token();

		if ($token === true)
		{
			unset($_POST['CSRF_TOKEN']);

			$post = $_POST;

			switch($convert)
			{
				case '-object':
					$post = toObject($post);
				break;

				case '-json':
					$post = json_encode($post);
				break;
			}

			\Moorexa\Bootloader::$csrfVerified = true;
			
			return true;
		}
		else
		{
			return false;
		}
	}
	elseif (\Moorexa\Bootloader::$csrfVerified === false && !isset($_POST['CSRF_TOKEN']))
	{
		return false;
	}
	elseif (\Moorexa\Bootloader::$csrfVerified === true)
	{
		$post = $_POST;

		switch($convert)
		{
			case '-object':
				$post = toObject($post);
			break;

			case '-json':
				$post = json_encode($post);
			break;
		}

		return true;
	}

	return false;
}

// chs function wrapper for other usage
function chs(string $template, $props = null)
{
	if (preg_match('/[<][a-zA-Z_]([\s\S]*?>)/', $template, $match))
	{
		$chs = new \Moorexa\CHS();
		return $chs->render($match[0], $template, $props);		
	}
}

// splice an object
function object_splice($object, $from = 0, $to)
{
	if (is_object($object))
	{
		// convert to an array
		$array = toArray($object);
		$splice = array_splice($array, $from, $to);

		// convert back to an object
		return toObject($splice);
	}
	
	return $object;
}

// merge an object
function object_merge()
{
	$objects = func_get_args();

	// convert to an array first
	$data = [];

	// run a loop
	array_map(function($arg) use (&$data){
		if (is_object($arg))
		{
			// convert to array
			$array = toArray($arg);
			// merge
			$data = array_merge($data, $array);
		}
	}, $objects);

	// convert data to an object
	return toObject($data);
}


// request method
function requestMethod($method)
{
	// display request method
	return '<input name="REQUEST_METHOD" type="hidden" value="'.$method.'"/>';
}

// inject helper function
function inject($class, $other = null)
{
	$inject = new \Moorexa\Injectables();
	$inject->inject($class, $other);

	return $inject;
}

// hide error for debugging
function silenterror()
{
	\MoorexaErrorContainer::$silentError = true;
}

function viewModel($model, $option = null)
{
	$data = '';
	// load view model
	$data .= '<input name="REQUEST_VIEWMODEL" type="hidden" value="'.$model.'"/>';

	if ($option == SET_DEFAULT)
	{
		$data .= "\n".'<input name="SET_VIEWMODEL_DEFAULT" type="hidden" value="'.$model.'"/>';
	}

	return $data;
}

// get uri
function uri($id=0)
{
	if (func_num_args() > 0)
	{
		$int = @intval($id);
		
		if (isset($_SERVER['REQUEST_QUERY_STRING']))
		{
			$query = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;

			if (!is_null($query))
			{
				$query = explode('/', ltrim($query, '/'));

				if (is_int($int))
				{
					$id = $int;
				}
				
				if (is_int($id))
				{
					return isset($query[$id]) ? $query[$id] : '';
				}
				else
				{
					if (isset($_GET[$id]))
					{
						return $_GET[$id];
					}
				}
			}
		}

		return null;
	}
	else
	{
		return new \Moorexa\Location;
	}

}

// import variables
function import_variables()
{
	$dropbox = \Moorexa\Controller::$dropbox;
	return toObject($dropbox);
}

// load css
function loadCss()
{
	$css = func_get_args();
	\Moorexa\View::$cssfiles = $css;
}

// load js
function loadJs()
{
	$js = func_get_args();
	\Moorexa\View::$javascripts = $js;
}

// load page
function page($path,$data=null)
{
	$cont = '';

	if (isset(Moorexa\BootLoader::$helper['active_v']))
	{
		$cont = Moorexa\Bootloader::$helper['active_c'];
	}
	elseif (isset(Moorexa\Bootloader::$helper['ROOT_GET']) && isset(Moorexa\Bootloader::$helper['ROOT_GET'][1]))
	{
		$cont = Moorexa\Bootloader::$helper['ROOT_GET'][0];
	}

	if (is_array($data))
	{
		$build = http_build_query($data);

		$pa = strtolower($cont) . '/' . $path .'?'. rawurlencode($build);

		return url($pa);
	}
	else
	{
		$pathArray = explode('/', $path);
		$useDefault = true;

		if (count($pathArray) > 1)
		{
			$getcont = strtolower($pathArray[0]);

			if ($getcont == strtolower($cont))
			{
				$useDefault = false;
				$pa = $path;
			}
		}
		
		if ($useDefault)
		{
			$pa = strtolower($cont) . '/' . $path;
		}

		return url($pa);
	}
}

// load action
function action($path, $data = null)
{
	$cont = '';

	if (isset(Moorexa\BootLoader::$helper['active_v']))
	{
		$vw = Moorexa\Bootloader::$helper['active_v'];
		$cont = Moorexa\Bootloader::$helper['active_c'];
	}
	elseif (isset(Moorexa\Bootloader::$helper['ROOT_GET']) && isset(Moorexa\Bootloader::$helper['ROOT_GET'][1]))
	{
		$vw = Moorexa\Bootloader::$helper['ROOT_GET'][1];
		$cont = Moorexa\Bootloader::$helper['ROOT_GET'][0];
	}


	if (is_array($data))
	{
		$build = http_build_query($data);

		$q = preg_quote($vw);
		$path = preg_replace("/^($q)/",'',$path);
		$path = preg_replace('/^[\/]/', '', $path);

		if (strpos($path, $vw) !== false)
		{
			$pa = $cont . '/' . $path .'?'. rawurlencode($build);
		}
		else
		{
			$pa = $cont . '/' . $vw . '/' . $path .'?'. rawurlencode($build);
		}

		return url($pa);
	}
	else
	{
		$q = preg_quote($vw);
		$path = preg_replace("/^($q)/",'',$path);
		$path = preg_replace('/^[\/]/','',$path);

		if (strpos($path, $vw) !== false)
		{
			$pa = $cont . '/' . $path;
		}
		else
		{
			$pa = $cont . '/' . $vw . '/' . $path;
		}

		return url($pa);
	}
}

// interpolate at runtime
function interpolate($content)
{
	static $chs;

	if ($chs == null)
	{
		$chs = new \Moorexa\CHS();
	}

	$chs->convertShortcuts($content);

	return $content;
}

// throw exception unless argument 1 returns true
function throw_unless($execute, $exception)
{
	if ($execute === true)
	{
		// get type
		switch (gettype($exception))
		{
			// array?
			case 'array':
				// get exception class
				$getException = $exception[0];
				// get arguments
				$argument = $exception[1] ?? null;
				// throw exception
				throw new $getException($argument);
			break;

			// string
			case 'string':
			case 'object':
				// throw exception
				throw new $exception;
			break;
		}
	}
}

// get country
function getLocationInfoByIp(){

    $client  = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : null;

    $forward = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;

    $remote  = @$_SERVER['REMOTE_ADDR'];

    $result  = array('country'=>'', 'city'=>'', 'code'=>'');

    if($client != null && filter_var($client, FILTER_VALIDATE_IP)){

        $ip = $client;

    }elseif($forward != null && filter_var($forward, FILTER_VALIDATE_IP)){

        $ip = $forward;

    }else{

        $ip = $remote;

    }

	$ch = curl_init("http://www.geoplugin.net/json.gp?ip=".$ip);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);

	if ($response != false)
	{
		$ip_data = json_decode($response); 
	

		if(is_object($ip_data) && $ip_data->geoplugin_countryName != null){

			$result['country'] = $ip_data->geoplugin_countryName;

			$result['city'] = $ip_data->geoplugin_city;

			$result['code'] = $ip_data->geoplugin_countryCode;

		}
	}

    return $result;

}

// bundler
function bundle($option=null)
{
	switch ($option)
	{
		// reset
		case 'reset':
		
			// reset css
			$css = PATH_TO_CSS;
			$all = glob($css . '*');
			array_walk($all, function($css){
				if ($css != '.' && $css != '..')
				{
					if (is_file($css))
					{
						$base = basename($css);
						if ($base != 'error.css' && $base != 'moorexa.css' && $base != 'wrapper.css')
						{
							unlink($css);
						}
					}
					elseif (is_dir($css))
					{
						// get all files
						$dr = getAllFiles($css);

						// reduce array
						$single = reduce_array($dr);
						array_walk($single, function($f){
							unlink($f);
						});

						// delete dir
						unlink($css);
					}
				}
			});

			// reset js
			$js = PATH_TO_JS;
			$all = glob($js . '*');
			array_walk($all, function($js){
				if ($js != '.' && $js != '..')
				{
					if (is_file($js))
					{
						$base = basename($js);
						if ($base != 'http.js' && $base != 'MyPHP.js')
						{
							unlink($js);
						}
					}
					elseif (is_dir($js) && basename($js) != 'Rexajs')
					{
						// get all files
						$dr = getAllFiles($js);

						// reduce array
						$single = reduce_array($dr);
						array_walk($single, function($f){
							unlink($f);
						});

						// delete dir
						unlink($js);
					}
				}
			});

			// reset bundler
			$map  = PATH_TO_LIB . 'shrinke.bundler.json';
			file_put_contents($map, '');

			echo "Bundle reset was successful.\n".PHP_EOL;

		break;
	}
}

// database table wrapper.
function db($tablename='')
{
	$db = null;

	if (!empty($tablename))
	{
		$db = \Moorexa\DB::table($tablename);
		return $db;
	}

	return null;
}


// create request body from model body
function createModelRule($tableName, $object=null)
{
	if (is_callable($tableName))
	{
		if ($object===null)
		{
			$object = $tableName;
			$tableName = 'closureFunction';
		}
	}

	if (!isset(\Moorexa\ApiModel::$useRulesCreated[$tableName]))
	{
		$body = null;

		// create a new annonymus class
		$class = new class($tableName, $object, $body) extends \Moorexa\ApiModel
		{
			// table name
			public $table;

			// callback function
			private $__callback_func;

			// createmodelrule
			public $usingRule = true;

			// constructor
			public function __construct($tableName, $object, &$body)
			{
				$this->table = $tableName;

				if (is_callable($object))
				{
					$this->__callback_func = $object;

					// call set rule
					\Moorexa\ApiModel::getSetRules($this);	

					$body = $this;
				}
			}

			// create rule
			public function setRules($body)
			{
				if (is_callable($this->__callback_func))
				{
					$argument = [];
					$argument[] = &$body;

					\Moorexa\Route::getParameters($this->__callback_func, $const, $argument);

					// call callback function 
					call_user_func_array($this->__callback_func, $const);
				}
			}
		};

		\Moorexa\ApiModel::$useRulesCreated[$tableName] = $body;
		
		// clean up
		$class = null;
	}
	else
	{
		$body = \Moorexa\ApiModel::$useRulesCreated[$tableName];
	}

	return $body;
}

// get numbers only
function number($string)
{
	$string = preg_replace('/(\D*)/','', $string);
	$number = intval($string);

	// return 
	return $number;
}

function image($img, $size=null)
{
	static $assets;

	if (strlen($img) > 2)
	{
		if (is_null($assets))
		{
			$assets = new \Moorexa\Assets();
		}

		$img = !is_null($size) ? $img.'@'.$size : $img;
		return $assets->image($img);
	}

	return null;
}

// extension
function extension($file)
{
	$extension = strrpos($file, '.');
	$extension = substr($file, $extension+1);
	return $extension;
}

// add lifecycle
function lifecycle($request)
{
	return new class ($request)
	{
		// save all attachments
		private static $attachments = [];

		// request
		public $request;

		// watchman
		public static $watchman = [];

		// constructor
		public function __construct($request)
		{
			$this->request = $request;
		}

		// attach an handle
		public function attach($handle, $data)
		{
			self::$attachments[$this->request][$handle] = $data;
		}

		// load breakpoint
		public function breakpoint($handle)
		{
			if (isset(self::$attachments[$this->request]))
			{
				$request = self::$attachments[$this->request];

				// check for handle
				if (isset($request[$handle]))
				{
					$data = $request[$handle];

					if (is_callable($data))
					{
						// get args
						\Moorexa\Route::getParameters($data, $const);

						$func = call_user_func_array($data, $const);

						if (isset(self::$watchman[$this->request]))
						{
							if (isset(self::$watchman[$this->request][$handle]))
							{
								$watchman = self::$watchman[$this->request][$handle];

								if (is_callable($watchman))
								{
									// get args
									\Moorexa\Route::getParameters($watchman, $const);
									// call
									call_user_func_array($watchman, $const);
								}
							}
						}	

						return $func;
					}

					return $data;
				}
			}
		}

		// watch breakpoint
		public function watch($handle, $data)
		{
			self::$watchman[$this->request][$handle] = $data;
		}
	};
}

// bind
function bind()
{
	\Moorexa\DB::$bindExternal['bind'] = func_get_args();
	return null;	
}

// get time ago
function get_time_ago( $time )
{
	if (is_string($time))
	{
		$time = strtotime($time);
	}

    $time_difference = time() - $time;

    if( $time_difference < 1 ) { return 'less than 1 second ago'; }
    $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
    );

    foreach( $condition as $secs => $str )
    {
        $d = $time_difference / $secs;

        if( $d >= 1 )
        {
            $t = round( $d );
            return $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
        }
    }
}

// app registry function
function app($key=null)
{
	static $app;

	$registry = [
		'app.css' => &\Moorexa\View::$bucket['css'],
		'app.js' => &\Moorexa\View::$bucket['js']
	];

	if (is_null($app))
	{
		$app = new class($registry)
		{
			// defined keys
			public $definedKey;

			// register to
			private $registerTo = [];

			// registry
			private $registry;

			// load constructor
			public function __construct(&$registry)
			{
				$this->registry = $registry;
			}

			// key method
			public function key($name)
			{
				$this->definedKey = $name;

				if (isset($this->registry[$name]))
				{
					$this->registerTo[$name] = &$this->registry[$name];	
				}

				return $this;
			}

			// registry method
			public function register($value)
			{
				$key = $this->definedKey;

				if (isset($this->registerTo[$key]))
				{
					switch (gettype($this->registerTo[$key]))
					{
						case 'array':
							$declared = &$this->registerTo[$key];
							$declared[] = $value;
						break;

						case 'object':
							$this->registerTo[$key]->{$key} = $value;
						break;

						default:
							$this->registerTo[$key] = $value;
					}
				}
				else
				{
					$this->registerTo[$key] = &$value;
				}

				return $this;
			}

			// setter method
			public function set($key, $val)
			{
				$this->registerTo[$key] = &$val;
			}

			// has method
			public function has($key, &$val)
			{
				if (isset($this->registerTo[$key]))
				{
					$val = $this->registerTo[$key];

					return true;
				}
			}

			// getter method
			public function get($key)
			{
				if ($this->has($key, $val))
				{
					return $val;
				}
				
				return null;
			}	
		};
	}

	if (!is_null($key))
	{
		$data = $app->get($key);

		return !is_null($data) ? $data : $app;
	}

	return $app;
}

// cache or load cache
function cacheOrLoadCache($path, &$newpath, $folder)
{
	static $chs;

	if (is_null($chs))
	{
		$chs = new \Moorexa\CHS();
	}

	// open loadcache.json
	$loadcache = PATH_TO_STORAGE . 'Caches/loadcache.json';
	$newpath = $path;

	if (file_exists($loadcache))
	{
		// read json
		$json = json_decode(file_get_contents($loadcache));
		$json = is_null($json) ? [] : toArray($json);
		$content = file_get_contents($path);
		$cache = false;

		if (isset($json[$path]))
		{	
			if (md5($content) == $json[$path]['hash'])
			{
				$newpath = $json[$path]['path'];
			}
			else
			{
				$cache = true;
			}
		}
		else
		{
			// cache
			$cache = true;
		}

		
		if ($cache)
		{
			$cont = \Moorexa\Bootloader::$helper['active_c'];

			$destination = PATH_TO_STORAGE .'Caches/'. $folder . '/cache.' . md5($path) . '.' .$cont.'.'. basename($path);

			// decode html
			$data = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

			// cache file
			$json[$path] = ['hash' => md5($content), 'path' => $destination];

			$chs->interpolateString = false;
			$class = \Moorexa\Bootloader::$currentClass;
			$class = isset($class->model) ? $class->model : $class;

			if ($folder == 'Footers')
			{
				$data = str_replace('</body>', '@preparejsbin;</body>', $data);
			}

			// interpolate
			$chs->interpolateExternal($data, $class, $interpolated);

			\Hyphe\Compile::ParseDoc($interpolated);

			// remove waiting
			$clearlist = \Moorexa\Rexa::$clearList;

			if (count($clearlist)>0)
			{
				foreach ($clearlist as $i => $c)
				{
					if (!preg_match('/[\@]([a-zA-Z0-9_-]+)[.]/', $c))
					{
						$interpolated = str_replace($c, '', $interpolated);
					}
				}
			}

			// save now
			file_put_contents($destination, $interpolated);
			file_put_contents($loadcache, json_encode($json, JSON_PRETTY_PRINT));

			$newpath = $destination;

			// clean up
			$cont = null;
			$destination = null;
		}
	}
}

function deldir($dir){
	$current_dir = opendir($dir);
	while($entryname = readdir($current_dir)){
	   if(is_dir("$dir/$entryname") and ($entryname != "." and $entryname!="..")){
		 deldir("${dir}/${entryname}");
	   }elseif($entryname != "." and $entryname!=".."){
		 unlink("${dir}/${entryname}");
	   }
	}
	closedir($current_dir);
	rmdir(${dir});
} 

// bind configuration
function bindConfig($bind, $data)
{
	$binds = explode('|', $bind);

	// loop through and set data
	foreach ($binds as $index => $bind)
	{
		$bind = trim($bind);
		$bind = preg_replace('/^([:])/', '', $bind);

		if ( is_array($data) && (count($data) == count($binds)) )
		{
			// set data
			\Moorexa\DB::$bindExternal[$bind] = $data[$index];
		}
		else
		{
			// set data
			\Moorexa\DB::$bindExternal[$bind] = $data;
		}
	}
}

// import from shortcuts
function import_from_shortcuts(string $name)
{
	$__path__ = getlink($name);

	if ($__path__ !== false)
	{
		if (file_exists($__path__))
		{
			unset($name);

			// include path
			$__include__ = include_once $__path__;

			// get all vars
			$vars = get_defined_vars();

			unset($vars['__include__'], $vars['__path__']);

			return (object) ['include' => $__include__, 'vars' => $vars];
		}
	}
}

// read shortcut
function getlink(string $name)
{
	$shortcut = \Moorexa\SET::$shortcuts;

	if (isset($shortcut[$name]))
	{
		return $shortcut[$name];
	}

	return false;
}

// import file
function import(string $path)
{
	$data = null;

	if (isset(\Moorexa\SET::$serviceVars[basename($path)]))
	{
		return \Moorexa\SET::$serviceVars[basename($path)];
	}

	if (file_exists($path))
	{
		$data = file_get_contents($path);
	}
	else
	{
		// get link from shortcut
		$link = getlink($path);

		if ($link !== false)
		{
			$data = file_get_contents($link);
		}
	}

	if ($data !== null)
	{
		// check for class declaration
		if (preg_match('/(class )([\S]+?)[\s]/i', $data, $class))
		{
			if (count($class) > 0)
			{
				// check for namespace
				$namespace = null;

				if (preg_match('/(namespace )([^;]+)/i', $data, $getnamesapace))
				{
					$namespace = $getnamesapace[2] . '\\'; 
				}

				$className = $namespace . $class[2];

				// get args
				$args = func_get_args();

				// remove path from args
				$args = array_splice($args, 1);

				// create instance with constructor
				$reflection = new \ReflectionClass($className);

				if ($reflection->hasMethod('__construct'))
				{
					// get params
					\Moorexa\Bootloader::getParameters($className, '__construct', $const, $args);

					// create instance
					$instance = $reflection->newInstanceArgs($const);
				}
				else
				{
					$instance = new $className;
				}

				// clean up
				$className = null;
				$args = null;
				$reflection = null;

				// save to imports
				\Moorexa\SET::$serviceVars[basename($path)] = $instance;

				return $instance;
			}
		}
		else
		{
			$__path__ = $path;

			// include path
			$___include___ = include_once $path;

			unset($data, $class, $path);

			// get all vars
			$vars = get_defined_vars();

			unset($vars['___include___'], $vars['__path__']);

			$data = (object) ['include' => $___include___, 'vars' => $vars];

			// save imports
			\Moorexa\SET::$serviceVars[basename($__path__)] = $data;

			return $data;
		}
	}

	// clean up
	$data = null;

	return null;
}

// unless fucntion
function unless($command, array $options)
{
	list($yes, $no) = $options;

	if ($command)
	{
		return $yes;
	}

	return $no;
}

// boot manager
function boot($className = null, $value = null)
{
	static $storage;
	static $bootClass;

	if (is_null($storage))
	{
		$storage = [];
	}

	if (is_null($bootClass))
	{
		$bootClass = new class()
		{
			// run singleton
			public function singleton()
			{
				return call_user_func_array('\utility\Classes\BootMgr\Manager::singleton', func_get_args());
			}

			public function method()
			{
				return call_user_func_array('\utility\Classes\BootMgr\Manager::method', func_get_args());
			}

			public function assign()
			{
				return call_user_func_array('\utility\Classes\BootMgr\Manager::assign', func_get_args());
			}

			public function get(string $className, $callback = null)
			{
				switch (is_callable($callback))
				{
					case true:
						$instance = $this->singleton($className);
						
						call_user_func($callback, $instance);

						return $instance;

					case false:

						if (strpos($className, '@') === false)
						{
							return $this->singleton($className);	
						}

						return $this->method($className);
				}
			}

			// check named
			public function hasNamed(string $className)
			{
				$named = \utility\Classes\BootMgr\Manager::$named;

				if (isset($named[$className]))
				{
					return true;
				}

				return false;
			}

			// get named
			public function getNamed(string $className)
			{
				return \utility\Classes\BootMgr\Manager::$named[$className];
			}
		};
	}

	switch (is_null($className))
	{
		case true:
			return $bootClass;

		case false:
			switch (is_null($value))
			{
				case true:
					// check named
					if ($bootClass->hasNamed($className))
					{
						return $bootClass->getNamed($className);
					}
				break;

				case false:
					$storage[$className] = &$value;
				break;
			}
		break;
	}
}

// get var from dropbox
function vars($var)
{
	$dropbox = \Moorexa\Controller::$dropbox;

	if (isset($dropbox[$var]))
	{ 
		return $dropbox[$var];
	}

	return  null;
}

// dropbox
function dropbox(string $name = '', $content = null)
{
	static $dropboxInternal;

	if ($name === '')
	{
		return $dropboxInternal;
	}

	// get dropbox
	$dropbox = &\Moorexa\Controller::$dropbox;

	if (is_null($content))
	{
		if (isset($dropbox[$name]))
		{
			return $dropbox[$name];
		}

		if (isset($dropboxInternal[$name]))
		{
			return $dropboxInternal[$name];
		}

		return false;
	}

	// set dropbox
	$dropbox[$name] = $content;

	if (is_null($dropboxInternal))
	{
		$dropboxInternal = [];
	}

	// set to internal
	$dropboxInternal[$name] = $content;
}
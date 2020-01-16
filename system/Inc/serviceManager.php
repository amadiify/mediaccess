<?php

namespace Moorexa;

/**
 * @package Moorexa Service Manager
 * @author  Amadi Ifeanyi
 * @version 0.0.1
 */

class ServiceManager 
{
    private static $args = [];
    private static $vars = [];
    private static $instance = null;
    public  static $props = [];
    
    // load vars and create instance 
    public static function loadvars()
    {
        if (self::$instance === null)
        {
            self::$instance = new self;
        }

        return self::loadServiceManager(func_get_args());
    }

    // load service managers
    private static function loadServiceManager(array $args = [])
    {
        $included = [];

        // import sm-func.php
        import_from_shortcuts('sm-func');
        
        foreach ($args as $d => $_fp)
        {
            $import = import(PATH_TO_SERVICEMANAGER. $_fp);

            if ($import !== null)
            {
                $vars = $import->vars;

                unset($vars['d'], $vars['_fp'], $vars['included']);

                $included[$_fp] = $vars;
            }
        }

        return self::getallData($included);
    }

    // get a variable
    public function __get($name)
    {
        if (count(self::$vars) > 0)
        {
            foreach (self::$vars as $key => $vars)
            {
                if (isset($vars[$name]))
                {
                    return $vars[$name];
                }
            }
        }
    }

    // call a function
    public function __call($meth, $arg)
    {
        if (count(self::$vars) > 0)
        {
            foreach (self::$vars as $key => $vars)
            {
                if (isset($vars[$meth]) && is_callable($vars[$meth]))
                {
                    return call_user_func_array($vars[$meth], $arg);
                }
            }
        }
    }

    // get all variables.
    public static function getallData(array $vars)
    {
        $data = [];

        foreach ($vars as $key => $var)
        {
            foreach ($var as $key => $val)
            {
                $data[$key] = $val;
            }
        }

        return $data;
    }

    private function callFromController($method, $args)
    {
        // call model
        return call_user_func_array([Bootloader::$currentClass, $method], $args);
    }

    // call model
    public function getModel()
    {
        return $this->callFromController('getModel', func_get_args());
    }

    // call provider
    public function getProvider()
    {
        return $this->callFromController('getProvider', func_get_args());
    }

    // call middleware
    public function getMiddleware()
    {
        return $this->callFromController('getMiddleware', func_get_args());
    }

    // call package
    public function getPackage()
    {
        return $this->callFromController('getPackage', func_get_args());
    }
}
<?php
namespace Moorexa;

/**
 * @package System Path class
 * @author Moorexa <moorexa.com>
 */

if (!class_exists('Moorexa\SysPath'))
{
    class SysPath
    {
        // custom paths
        private static $PATH = [
            'app'    => 'pages/',
            'api'    => 'api/',
            'comp'   => 'utility/Components/',
            'system' => 'system/',
            'public' => 'public/',
            'kernel' => 'kernel/',
            'root'   => './',
            'utility' => 'utility/'
        ];

        // define path
        public static function __callStatic($pathName, $args)
        {
            // get path
            $getPath = self::$PATH[$pathName];
            
            // determine data type
            // ensure array size is greater than zero
            switch (count($args) > 0)
            {
                // check data type
                case true:
                    switch (gettype($args[0]))
                    {
                        // define multiple
                        case 'array':
                            array_walk($args[0], function($path, $name) use (&$getPath) {
                                self::definePath('PATH_TO_'.strtoupper($name), $getPath . $path .'/');
                            });
                        break;

                        // define single
                        case 'string':
                            $path = $getPath . (isset($arg[1]) ? $arg[1] .'/' : '');
                            return self::definePath('PATH_TO_'.strtoupper($args[0]), $path);
                        break;
                    }
                break;

                case false:
                    // define path with path name
                    return self::definePath('PATH_TO_'.strtoupper($pathName), $getPath);
                break;
            }

            // clean path
            $getPath = null;
        }

        // init paths
        public static function init($root, $path)
        {
            // app not serving from cli
            switch ((!defined('CLI_ENV')))
            {
                case true:
                    // read config.xml
                    $config = simplexml_load_file('kernel/Config/config.xml');
                    $mode = (array) $config->versioning->attributes()->mode;
                    $mode = $mode[0];
                    
                    // determine mode
                    switch ((strtolower($mode) == 'auto') && self::appIsLive())
                    {
                        case true:
                            $mode = 'production';
                        break;

                        case false:
                            $mode = 'development';
                        break;
                    }

                    $version = (array) $config->versioning->{$mode};
                    $version = $version[0];

                    // set directory
                    $directory = './utility/Version/'.$version;

                    switch (is_dir($directory))
                    {
                        case true:
                            $versionXML = $directory . '/paths.xml';
                            // check if path exists then load path
                            if (file_exists($versionXML))
                            {
                                // load xml
                                $config = simplexml_load_file($versionXML);
                                $config = (array) $config;
                                $config['utility'] = $config['root'] . 'utility/';
                                array_walk(self::$PATH, function($val, $key) use ($config){
                                    // set if is not set
                                    $config[$key] = !isset($config[$key]) ? $val : $config[$key];
                                });
                                // set path globally
                                self::$PATH = $config;
                            }
                        break;
                    }

                    // clean up
                    $config = null;
                    $mode = null;
                    $directory = null;
                    $version = null;
            
                break;
            }

            // define path
            self::definePath(strtoupper($root), './');
        }

        // CLI surfing
        public static function cliSurfing()
        {
            $server =& $_SERVER;
        
            if (isset($server['argv']) && isset($server['argc']))
            {
                $argc = $server['argc'];
                $argv = $server['argv'];

                if (!isset($server['REQUEST_QUERY_STRING']) && count($_GET) == 0 && count($argv) > 0)
                {
                    $last = $argv[1];
                    $server['REQUEST_METHOD'] = 'GET';

                    if (strpos($last, '/') > 1)
                    {
                        $server['REQUEST_QUERY_STRING'] = $last;
                    }
                    else
                    {
                        $xml = simplexml_load_file(HOME . 'config.xml');
                        $cont = $xml->router->default->controller;
                        $server['REQUEST_QUERY_STRING'] = $cont .'/'. $last;
                    }

                    $end = end($argv);

                    if ($end == 'notemplate' || $end == '--blank' || $end == '-blank')
                    {
                        $server['RESPONSE_HIDE_TEMPLATE'] = true;
                    }
                    
                }
            }
        }

        // app is live
        public static function appIsLive()
        {
            switch (isset($_SERVER['HTTP_HOST']))
            {
                case true:

                    $HOST = $_SERVER['HTTP_HOST'];					

                    if(strpos($HOST, 'localhost') === false && strpos($HOST, '127.0.0.1') === false)
                    {
                        return true;
                    }

                break;

                case false:
                    if (isset($_SERVER['SERVER_NAME']))
                    {
                        $SNAME = $_SERVER['SERVER_NAME'];
            
                        if(strpos($SNAME, 'localhost') === false && strpos($SNAME, '127.0.0.1') === false)
                        {
                            return true;
                        }
                    }
                break;
            }

            return false;
        }

        // define path
        private static function definePath($constantTitle, $constantPath)
        {
            // ensure it hasn't been set before then create
            switch (!defined($constantTitle))
            {
                // define constant
                case true:
                    // define path here.
                    define($constantTitle, $constantPath);
                break;
            }

            // return path;
            return $constantPath;
        }
    }
}

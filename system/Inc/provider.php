<?php

namespace Moorexa;

use utility\Classes\BootMgr\Manager as BootMgr;

/**
 * @package Provider class for moorexa
 * @author Amadi ifeanyi
 */

class Provider
{
    // providers loaded
    private static $providers = [];

    // ___callStatic magic method for loading provider
    public static function __callStatic($provider, $args)
    {
        // append Provider 
        $provider = ucfirst($provider) . 'Provider';

        if (!isset(self::$providers[$provider]))
        {
            // build provider class
            $className = '\\Providers\\'.ucwords($provider);
            // check if provider exists
            $path = PATH_TO_PROVIDER . ucfirst($provider) . '.php';
            // throw exception if provider doesn't exists
            throw_unless(!file_exists($path), ['\Exceptions\Providers\ProviderException', 'Provider '.$provider.' doesn\'t exists.']);
            // load provider
            include_once $path;
            // check if provider class exists
            throw_unless(!class_exists($className), ['\Exceptions\Providers\ProviderException', 'Provider Class '.$className.' doesn\'t exists.']);
            // create provider class
            if (class_exists($className))
            {
                // get singleton
                $class = BootMgr::singleton($className, [Bootloader::$instance]);

                if (BootMgr::$BOOTMODE[$className] == CAN_CONTINUE)
                {
                    // add to loaded providers
                    self::$providers[$provider] = $class;

                    if (method_exists($class, 'boot'))
                    {
                        $request = $className . '@boot';

                        BootMgr::method($request, null);
                        
                        if (BootMgr::$BOOTMODE[$request] == CAN_CONTINUE)
                        {
                            Bootloader::$instance->getParameters($className, 'boot', $const, [Bootloader::$instance]);
                    
                            BootMgr::methodGotCalled($request, call_user_func_array([$class, 'boot'], $const));
                        }
                    }

                    // ensure it has the 
                    if (isset($args[0]) && $args[0] != 'boot')
                    {
                        $meth = $args[0];

                        // check method.
                        if (method_exists($class, $meth))
                        {
                            $request = $className . '@' . $meth;
                            $spl = array_splice($args, 1);

                            BootMgr::method($request, null);
                        
                            if (BootMgr::$BOOTMODE[$request] == CAN_CONTINUE)
                            {
                                Bootloader::$instance->getParameters($className, $meth, $const, $spl);
                                return BootMgr::methodGotCalled($request, call_user_func_array([$class, $meth], $const));
                            }
                        }
                    }

                    return $class;
                }

                return BootMgr::instance();
            }
        }
        else
        {
            $meth = isset($args[0]) ? $args[0] : null;

            $provider = self::$providers[$provider];

            if (!is_null($meth) && method_exists($provider, $meth))
            {
                $spl = array_splice($args, 1);
                Bootloader::$instance->getParameters($provider, $meth, $const, $spl);

                return call_user_func_array([$provider, $meth], $const);
            }

            return $provider;
        }
    }
}
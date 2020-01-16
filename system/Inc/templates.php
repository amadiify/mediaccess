<?php
namespace Moorexa;
use Moorexa\Bootloader as BL;

/**
 * @package Moorexa Template loader
 * @version 0.0.1
 * @author Ifeanyi Amadi
 */

class Template
{
    public static $header = "";
    public static $footer = "";

    // load template
    private static function load($dir, $name)
    {
        // check current dir
        // if '/' found then go to main
        $activeController = isset(BL::$helper['active_c']) ? BL::$helper['active_c'] : null;

        if (!is_null($activeController))
        {
            $main = $dir[0] == '/' ? HOME . 'custom' . $dir . '/' : HOME . 'pages/' . $activeController . '/Custom/' . $dir .'/';
            
            if (is_dir($main))
            {
                self::${$name} = deepScan($main, $name.'.php');
            }
        }
    }

    // change header and footer
    public static function apply($dir)
    {
        self::load($dir, 'header');
        self::load($dir, 'footer');
    }

    // change header
    public static function header($dir)
    {
        self::load($dir, 'header');
    }

    // change footer
    public static function footer($dir)
    {
        self::load($dir, 'footer');
    }

}

<?php
namespace Bootstrap;

// get rexa
use Moorexa\Rexa;

/**
 * @package Bootstrap Alerts
 * @author Amadi ifeanyi
 */
class Alert
{
    // message pack
    private static $messagePack = [];

    //  static magic method
    public static function __callStatic($name, $args)
    {
        // swap error to danger
        $name = ($name == 'error') ? 'danger' : strtolower($name);

        // set message
        self::$messagePack[][$name] = $args[0];

        // get alert
        self::getAlert();
    }

    // read alert
    private static function readAlert(string $type, string $message)
    {
        return '<aside class="alert alert-'.$type.'">'.$message.'</aside>';
    }

    // define directive
    public static function getAlert()
    {
        // get only one alert
        Rexa::directive('alert', function()
        {
            if (count(self::$messagePack) > 0)
            {
                // get first entry
                $firstEntry = self::$messagePack[0];

                // get key
                $keys = array_keys($firstEntry);

                // get the type. only for the first
                $type = $keys[0];

                // get message
                $message = $firstEntry[$type];

                // read alert
                return self::readAlert($type, $message);
            }

            return null;
        });

        // get by type
        if (count(self::$messagePack) > 0)
        {
            // pack errors
            $errors = [];

            // push errors
            array_map(function($arr) use (&$errors){
                foreach ($arr as $key => $val)
                {
                    $errors[$key] = $val;
                }
            }, self::$messagePack);

            array_each(function($message, $type)
            {
                // Run directive
                Rexa::directive('alert-'.$type, function() use (&$type, &$message){
                    // read alert
                    return self::readAlert($type, $message);
                });

            }, $errors);

        }
    }

}


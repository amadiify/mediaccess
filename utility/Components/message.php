<?php

namespace Component;

class Message
{
    public static $messages = [];

    // get message
    public static function show()
    {
        if (count(self::$messages) > 0)
        {
            // get first
            $message = array_values(self::$messages)[0];

            // remove
            $message = array_shift(self::$messages);

            // return message
            return $message;
        }
    }

    // set message
    public static function __callStatic($meth, $args)
    {
        // push to the begining of array
        array_unshift($args, $meth);

        // save messages
        self::$messages[$meth] = call_user_func_array('\Component\Message::wrapper', $args);
    }

    // clear
    public static function clear()
    {
        self::$messages = [];
    }

    // wrapper
    private static function wrapper($name, $text)
    {
        $wrapper = function($type, $text){
            $types = [
                'success' => 'bg-success',
                'pending' => 'bg-primary',
                'warning' => 'bg-warning',
                'error' => 'bg-danger'
            ];

            $type = isset($types[$type]) ? $types[$type] : $types['pending'];

            return '<section class="px-3 py-2 '.($type).' text-white" style="border-radius:10px; margin-top:10px;">'.$text.'</section><br>';
        };

        // get wrapper.
        return $wrapper($name, $text);
    }
}
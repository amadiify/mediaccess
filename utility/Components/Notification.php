<?php 

namespace Component;

use Moorexa\View;

class Notification
{
    public $response = [];

    // modal notification
    public static function modal()
    {
        $self = new self;

        return $self;
    }

    // modal box notification
    public function __call($meth, $arg)
    {
        $dec = json_decode($arg[0]);

        if ($dec !== false && $dec !== null)
        {
            View::$modalbox[$meth] = $dec->text;

            return $dec->text;
        }
        else
        {
            View::$modalbox[$meth] = $arg[0];

            return $arg[0];
        }
    }
}
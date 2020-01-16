<?php
namespace Bootstrap;
use Moorexa\Bootloader;

class Notification extends AlertController
{
    // alert types
    public static $instance;
    public static $called = false;

    //  static magic method
    public static function __callStatic($name, $args)
    {
        if (is_null(self::$instance))
        {
            self::$instance = new self;
        }

        self::$instance->prepareAlert('notification', $name, $args, self::$instance);
    }

    // get alert
    public function getAlert()
    {
        if (count(self::$messagePack) > 0)
        {
            // get first entry
            $firstEntry = self::$messagePack[$this->prefix][0];

            // get key
            $keys = array_keys($firstEntry);

            // get the type. only for the first
            $type = $keys[0];

            // get message
            $message = $firstEntry[$type];

            // read alert
            return $this->readAlert($type, $message);
        }
    }

    // read alert
    public function readAlert(string $type, $message)
    {
        $gtype = $type == 'error' ? 'danger' : $type;

        $title = $gtype.'!';

        if (is_array($message))
        {
            $title = $message[0];
            $message = $message[1];
        }

        $__config__ = [
        'type'=>$gtype,
        'title'=>ucfirst($title),
        'message'=>$message,
        'from'=>'top',
        'align'=>'right',
        'icon'=>($gtype == 'success' ? 'fa fa-check' : 'fa fa-bell'),
        'ani_in'=>'animated fadeIn',
        'ani_out'=>'animated fadeOut'
        ];

        export_variables($__config__);

        $notify = function()
        {
            Bootloader::$currentClass->jsbin(function()
            {
                $v = import_variables();
                $l = $v.__config__;
                notify($l.title,$l.message,$l.from,$l.align,$l.icon,$l.type,$l.ani_in,$l.ani_out);
            });
        };

        if (!self::$called)
        {
            if (!is_null(Bootloader::$currentClass))
            {
                $notify();
                self::$called = true;
            }
            else
            {
                lifecycle('controllers.serve')->watch('view', function() use ($notify)
                {
                    $notify();
                    self::$called = true;
                });
            }
        }
        
    }
}
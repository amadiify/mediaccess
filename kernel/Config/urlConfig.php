<?php

namespace Moorexa;

// Application configuration for URL. 

class UrlConfig extends Bootloader
{
    // application environment (development|live)
    private $mode;

    // App url; has a default value.
    public static $appurl;

    // development url address (Optional)
    private $url_devlopment = '';

    // live url address (Optional)
    public $url_live = '';


    // immidiate activity
    public function __construct()
    {
       UrlConfig::$appurl = rtrim(geturladdress(), '/') . '/';
       $this->determineMode();
    }

    /** 
     *
     * This function would check if app is online or offline, then switch mode
     *
     * @return void
     **/
    private function determineMode()
    {
        // set mode
        $this->mode = "development";

        switch ($this->__isonline())
        {
            // app is live
            case true:
                $this->mode = "live";
                // set url for production.
                UrlConfig::$appurl = $this->url_live !== '' ? rtrim($this->url_live, "/") . '/' : UrlConfig::$appurl;
            break;

            // development
            case false:
                // set url for development.
                UrlConfig::$appurl = $this->url_devlopment !== '' ? rtrim($this->url_development, "/") . '/' : UrlConfig::$appurl;
            break;
        }
    }

}
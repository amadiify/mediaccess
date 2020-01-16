<?php

namespace Moorexa;

use utility\Classes\BootMgr\Manager as Boot;

/**
 * @package Boot Manager
 */

Boot::onLoad(function($className){
    // var_dump($className);
});

Boot::called('Moorexa\UrlConfig', function()
{
    Boot::singleton_as('Query','Request\Query');

    Boot::called('Moorexa\View', function(){
        Boot::singleton_as('Wrapper','View\Wrapper');
    });
});

Boot::called('partial@notification', function()
{
    // hide alert
    dropbox('showAlert', false);

    // get paths
    $path = uri()->paths();

    // get full path
    $path = implode('/', $path);

    // check for trigger
    if (strpos($path, 'trigger-confirm') !== false)
    {
        // show alert
        dropbox('showAlert', true);

        // get cancel link
        $cancelLink = substr($path, 0, strpos($path, '/trigger-confirm'));

        // get alert link
        $path = str_replace('/trigger-confirm', '', $path);

        // Collect vars 
        $get = $_GET;
        // remove __app_request__
        unset($get['__app_request__']);

        if (count($get) > 0)
        {
            $query = http_build_query($get);
            $path .= '?' . $query;
        }

        // set alert link
        dropbox('alertLink', url($path));

        // set current link
        dropbox('currentUrl', url($cancelLink));

    }
});

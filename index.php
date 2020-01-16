<?php

namespace Moorexa;

use Exception;

/**
 * @package      Moorexa PHP Framework
 * @author       Fregatelab inc <Ifeanyi Amadi>. https://www.moorexa.com <amadiify.com>
 * @description  A Rapid PHP Technology for a better and faster web development.
 * @company      https://www.fregatelab.com
 * @version      0.0.1
 */

// enable gzip
ob_start('ob_gzhandler');


// include application paths
include_once 'system/Inc/paths.php';


// Check if CORE module really exists in the module folder
switch (file_exists(MODULE_PATH))
{
    case true:
        // require core module
        require_once MODULE_PATH;

        // check if engine class exists
        switch (class_exists('Moorexa\Engine'))
        {
            case true:
                // require composer autoloader, then create new engine class instance
                require_once COMPOSER;

                // return instance
                return new Engine(); // app entry =>

            case false:
                // throw exception
                throw new Exception('Moorexa Engine class not found in ' . PATH_TO_CORE);
        }
    break;

    // throw exception
    case false:
        throw new Exception('Error Loading Core Module. Application failed to start! You can contact support@moorexa.com for support or raise a ticket @ https://www.moorexa.com/ticket/new');
}
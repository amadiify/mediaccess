<?php

namespace Providers;

use WekiWork\Http;

/**
 * @package Connectify Provider
 * This provider should be registered in kernel/registry.php
 * In the boot array, we can access this provider via Providers
 */

class ConnectifyProvider
{
    /**
     * @method Boot startup 
     * This method would be called upon startup
     */
    public function boot()
    {
        Http::$endpoint = 'http://localhost:8888/mediportal-api/';

        // set authorize token
        Http::setHeader('x-authorize-token:fadca654b5afbfbe4e262a36eb17c8af');

    }
}
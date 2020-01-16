<?php

use Moorexa\Event;
use Moorexa\Middleware;
use utility\Classes\BootMgr\Manager as BootMgr;

// This file can be used to listen to events when triggered

// listen for when system is ready.
Event::on('system.ready', function($sys)
{
    // manage csrf token for api requests.
    $sys->push('csrf_verify', Middleware::csrfVerify()
    ->channel([ API => NO_VERIFY_HEADER ])
    ->headers('X-Csrf-Pubkey: '.env('bootstrap', 'csrf_public_key'))
    )->push('packager',  Moorexa\View::$packagerJson)
    ->push('loadAssets', BootMgr::singleton(Moorexa\Assets::class))
    ->push('session',    BootMgr::singleton(Moorexa\Session::class))
    ->push('cookie',     BootMgr::singleton(Moorexa\Cookie::class))
    ->push('model',      BootMgr::singleton(Moorexa\Model::class))
    ->push('post',       BootMgr::singleton(Moorexa\HttpPost::class))
    ->push('view',       BootMgr::singleton(Moorexa\View::class, $sys));

});

// #listen for more events here.
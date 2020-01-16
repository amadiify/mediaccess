<?php

use Moorexa\Route;
use Moorexa\Event;
use Moorexa\Registry;
use Moorexa\Bootloader;
use Moorexa\DB;
use Moorexa\Middleware;

/*
 ***************************
 * 
 * @ Route
 * info: Add your GET, POST, DELETE, PUT request handlers here. 
*/

Route::domain('console.fregatelab.com', function()
{
    if (!isset($_SESSION['lab.access.guranted']))
    {
        //redirect('http://console.fregatelab.com?msg=Access denied');
    }
});
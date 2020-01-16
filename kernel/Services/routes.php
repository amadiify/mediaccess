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
        redirect('http://console.fregatelab.com?msg=Access denied');
    }
});

Route::any('register-as-{name}', ['name' => '([a-zA-Z]+)'], function($name){
    return 'App/register/patient/'.$name;
});

Route::any('order-{id}/{action}?', ['id' => '(\d+)'], function($id, $action){
    return 'My/orders/'.$id.'/'.$action;
});

Route::any('open-chat-for-{id}', ['id' => '(\d+)'], function($id){
    return 'My/conversation/'.$id;
});
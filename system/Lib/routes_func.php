<?php

use Moorexa\View;
use Moorexa\Route;

/*
 ***************************
 * 
 * @route functions
 * info: routes request functions. 
*/

// Redirect - function
function routeTo($page)
{
	$app = new View();

	// make a redirection
	$app->renderNew($page);
}

// controller
function controller($name)
{
	// will not create an instance;
	// just prepare for encapsulation
	Route::$controllerVars[$name] = !isset(Route::$controllerVars[$name]) ? (object) [] : Route::$controllerVars[$name];

	return Route::$controllerVars[$name];
}
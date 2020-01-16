<?php

namespace Moorexa;

/**
 *@package Service manager helper functions
 *@author Moorexa 
 */

// define page_props function
function page_props(string $page, $callback)
{
	Controller::$pageProps[strtoupper($page)] = $callback;
}

// define model_props function
function model_props(string $model, $callback)
{
	$xpl = explode('@', $model);

	if (count($xpl) == 2)
	{
		list($controller, $other) = $xpl;
		// get active controller
		$cc = Bootloader::$helper['active_c'];

		if (strtolower($cc) == strtolower($controller))
		{
			$otherc = explode('/', $other);

			// check if model exists
			$path = HOME . 'pages/' . ucfirst($cc) . '/Models/' . ucfirst($otherc[0]) . '.php';

			if (file_exists($path))
			{
				if (count($otherc) == 2)
				{
					Model::$modelProps[strtoupper($other)] = $callback;
				}
			}
		}
	}
}

// define view_props function
function view_props(string $view, $callback)
{
	View::$viewProps[strtoupper($view)] = $callback;
}

// define import_sm function
function import_sm(string $serviceManager)
{
    $path = PATH_TO_SERVICEMANAGER . $serviceManager . '.php';

    if (file_exists($path))
    {
    	return import($path);
    }
}
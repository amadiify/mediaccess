<?php
use Moorexa\Model;
use Moorexa\Packages as Package;
use Moorexa\Controller;
/**
 * Documentation for AppError Page can be found in AppError/readme.txt
 *
 *@package	AppError Page
 *@author  	Moorexa <www.moorexa.com>
 **/

class AppError extends Controller
{
	/**
    * AppError/error404 wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function error404()
	{
		$this->render('error404');
	}
	/**
    * AppError/error204 wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function error204()
	{
		$this->render('error204');
	}
}
// END class
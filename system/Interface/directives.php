<?php

namespace Moorexa\Interfaces;

use Moorexa\Directive as DirectiveClass;

/**
 *@package Moorexa Directive Interface
 *@author Amadi ifeanyi <amadiify.com>
 */

Interface Directive
{
	// get all directives
	/**
	 *@method Directives
	 *@return Void
	 *@param Object
	 */
	public static function directives(DirectiveClass $injector);
}
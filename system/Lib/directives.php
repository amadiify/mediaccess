<?php

namespace Moorexa;

class Directive
{
	// instance
	public $instance;

	/**
	 *@method Inject
	 *@return void
	 */
	public function inject()
	{
		$class = func_get_args();

		// load and inject classes
		foreach ($class as $i => $className)
		{
			$className = '\\'.$className;

			if (class_exists($className))
			{
				$directive = new $className;
				$this->instance = $directive;

				// call directives
				call_user_func($className.'::directives', $this);
			}
		}
	}

	// set directive
	public function set($key, $method)
	{
		if (method_exists($this->instance, $method))
		{ 
			$build = get_class($this->instance) . '::' . $method;

			// create callable function
			$callable = function($arguments, $attrline) use ($method)
			{
				return call_user_func_array([$this->instance, $method], func_get_args());
			};

			// push to rexa 
			Rexa::$directives[$key] = $build;
		}

		return $this;
	}
}
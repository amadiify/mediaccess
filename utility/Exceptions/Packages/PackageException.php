<?php

Namespace Exceptions\Packages;

class PackageException extends \Exception
{
	public function __construct()
	{
		$args = func_get_args();

		// load args as array
		$LoadAsArray = true;

		if (count($args) == 1)
		{
			// get type
			switch (gettype($args[0]))
			{
				// string
				case 'string':
					$error = $args[0];
					$LoadAsArray = false;
				break;

				// array
				case 'array':
					$args = explode('@', $args[0][0]);
				break;
			}
		}
		
		if ($LoadAsArray)
		{
			list($controller, $packageName) = $args;
			// build error
			$error = 'Invalid Package ('.$packageName.'.php) requested for in '. 'pages/' . $controller . '/packages/';
		}

		$trace = $this->getTrace()[1];

		if (isset($trace['class']))
		{
			if (isset($trace['args'][0]))
			{
				$this->title = $trace['class'].$trace['type'].($trace['args'][0]);
			}
			else
			{
				$this->title = $trace['class'].$trace['type'].'getPackage()';
			}
		}

		$this->message = $error;

		$this->file = $trace['file'];
		$this->line = $trace['line'];
	}
}
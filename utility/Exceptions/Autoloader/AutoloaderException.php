<?php

Namespace Exceptions\Autoloader;

class AutoloaderException extends \Exception
{
	public function __construct($error)
	{
		$trace = $this->getTrace()[0];
		
		if (!isset($trace['file']))
		{
			$trace = $this->getTrace()[1];
		}

        $this->title = 'Autoloader Exception';
        
		$this->message = $error;

		if (isset($trace['file']))
		{
        	$this->file = $trace['file'];        
			$this->line = $trace['line'];
		}

	}
}
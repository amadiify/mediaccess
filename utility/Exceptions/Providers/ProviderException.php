<?php

Namespace Exceptions\Providers;

class ProviderException extends \Exception
{
	public function __construct($error)
	{
        $trace = $this->getTrace()[0];

		if (isset($trace['class']))
		{
			$this->title = $trace['class'].$trace['type'].'getProvider('.$trace['args'][0].')';
		}
		else
		{
			$this->title = 'Provider Exception';
		}
		$this->message = $error;

        $this->file = $trace['file'];        
		$this->line = $trace['line'];

	}
}
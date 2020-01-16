<?php

Namespace Exceptions\Middleware;

class MiddlewareException extends \Exception
{
	public function __construct($error)
	{
        $trace = $this->getTrace()[0];

        $this->title = 'Middleware Exception';
        
		$this->message = $error;

        $this->file = $trace['file'];        
		$this->line = $trace['line'];

	}
}
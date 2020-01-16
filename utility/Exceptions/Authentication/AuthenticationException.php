<?php

Namespace Exceptions\Authentication;

class AuthenticationException extends \Exception
{
	public function __construct($error)
	{
        $trace = $this->getTrace()[0];

        $this->title = 'Authentication Exception';
        
		$this->message = $error;

        $this->file = $trace['file'];        
		$this->line = $trace['line'];

	}
}
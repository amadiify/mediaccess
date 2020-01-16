<?php

Namespace Exceptions\Model;

class ModelException extends \Exception
{
	public function __construct($error)
	{
        $trace = $this->getTrace()[0];

        $this->title = 'Model Exception';
        
		$this->message = $error;

        $this->file = $trace['file'];        
		$this->line = $trace['line'];

	}
}
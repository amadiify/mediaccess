<?php

Namespace Classerror;

class ClassException extends \Exception
{
	public function __construct($error)
	{
		$trace = $this->getTrace()[1];

		$this->title = $trace['class'].$trace['type'].$trace['args'][0];

		$this->message = $error;

		$file = $this->file;

		$this->file = $trace['file'];

		$this->getTrace()[1]['file'] = $file;
	}
}
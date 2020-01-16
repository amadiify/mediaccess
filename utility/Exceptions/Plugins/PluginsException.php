<?php

Namespace Exceptions\Plugins;

class PluginsException extends \Exception
{
	public function __construct($error)
	{
		$trace = $this->getTrace()[0];

		$this->title = $trace['class'].$trace['type'].$trace['args'][0];

		$this->message = $error;

		$file = $this->file;

		$this->file = $trace['file'];

	}
}
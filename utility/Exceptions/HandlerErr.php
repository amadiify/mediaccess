<?php 

namespace Exception;

class HandlerErr extends \Exception
{
	public function __construct($error)
	{
		$trace = $this->getTrace()[0];

		$this->title = 'Database Error';

		$this->message = $error;

		$file = $this->file;

		$this->file = $trace['file'];

	}
}
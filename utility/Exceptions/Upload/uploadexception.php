<?php

class UploadException extends Exception
{
	// hide where exception was triggered. only show where file was called
	public $hidefile = true;
	// title to appear
	public $title = "";

	public function __construct($tmp_name, $filename, $msg = false)
	{
		$trace = $this->getTrace()[1];
		
		$func = $trace['function'];
		$args = implode(',', $trace['args']);

		$this->title = $func .'('.$args.')';

		$type = substr($func, 0, strpos($func, '_'));

		$givepath = ['PATH_TO_IMAGE' => PATH_TO_IMAGE, 'PATH_TO_CSS' => PATH_TO_CSS, 'PATH_TO_JS' => PATH_TO_JS, 'PATH_TO_MEDIA' => PATH_TO_MEDIA];

		$path = "";

		if ($type == 'local')
		{
			$path = HOME;
		}

		if ($type != 'remote')
		{
			$path = $givepath['PATH_TO_'.strtoupper($type)];
		}

		if ($msg === false)
		{
			$message = 'We tried to move '.$type.' file from the assumed tmp location to this destination '.$path.$filename.' and we couldn\'t. Please make sure of the tmp/file location and try again.';
		}
		else
		{
			$message = $msg;
		}

		$this->message = $message;

	}
}
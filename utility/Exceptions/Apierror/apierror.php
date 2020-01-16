<?php

class ApiError extends Exception
{
	public $hidefile = "";
	public $title = "";

	public function __construct($message, $title = "")
	{
		$this->title = $title;
		$this->message = $message;
	}
}
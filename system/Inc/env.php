<?php

class Env
{
	private static $env_tables = [];
	private static $active;
	private static $dumped = [];
	public static  $config_env = [];
	public static  $config = [];
	public static  $variables = [];


	public static function get($name)
	{
		if ($name == 'env_tables')
		{
			return self::$env_tables;
		}
		elseif ($name == 'env_activated')
		{
			return self::$env_activated;
		}
		elseif ($name == 'active')
		{
			return self::$active;
		}
		elseif ($name == 'dumped')
		{
			return self::$dumped;
		}
	}

	public static function call($meth, $arg)
	{
		if ($meth == 'dump')
		{
			return self::dump($arg);
		}
	}
}
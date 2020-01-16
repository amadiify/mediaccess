<?php

use Moorexa\UrlConfig;
use Moorexa\Bootloader;

class Settings extends UrlConfig
{	
	public static $data = null;
	public static function get($key)
	{
		if ($key == 'url')
		{
			return UrlConfig::$appurl;
		}
		else
		{
			$settings = Bootloader::$helper['settings'];

			if (isset($settings->{$key}))
			{
				return $settings->{$key};
			}	
		}
	}

	public static function set($key, $value)
	{
		$settings = Bootloader::$helper['settings'];

		$settings->{$key} = $value;
	}

	public static function exists($key)
	{
		$settings = Bootloader::$helper['settings'];

		if (isset($settings->{$key}))
		{
			return true;
		}

		return false;
	}

	public static function remove($key)
	{
		if (settings::exists($key))
		{
			$settings = Bootloader::$helper['settings'];
			$settings->{$key} = null;
		}
	}
}

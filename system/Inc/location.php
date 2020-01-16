<?php

namespace Moorexa;

class Location
{
	public function get($id=null)
	{
		return self::paths($id);
	}

	public static function paths($id = null)
	{
		$loc = Bootloader::$helper['location.url'];

		if (!is_null($id))
		{
			$loc = isset($loc[$id]) ? $loc[$id] : false;
		}

		return $loc;
	}

	// covert paths to string
	public static function pathAsString() :string
	{
		$paths = self::paths();
		$paths[0] = strtolower($paths[0]); // make controller lowercase

		return implode("/", $paths);
	}

	// get previous page
	public static function previous()
	{
		return Route::previous();
	}

	// check if request is sent.
	public function is($name)
	{
		$location = Bootloader::$helper['location.url'];
		$current = Bootloader::$helper['c_controller'];

		$flip = array_flip($location);
		$explode = array_flip(explode('/', $name));

		if (isset($flip[$current]))
		{
			unset($flip[$current]);
			if (isset($explode[$current]))
			{
				unset($explode[$current]);
			}
		}

		$flip = array_flip($flip);
		$explode = array_flip($explode);
		sort($flip);
		sort($explode);

		$url = array_splice($flip, 0, count($explode));
		$url = implode('/', $url);
		$explode = implode('/', $explode);

		if ($explode == $url)
		{
			return true;
		}

		return false;
	}

	// get argument
	public function arg($id=0)
	{
		$array = $this->arg;
		if (isset($array[$id]))
		{
			return $array[$id];
		}

		return null;
	}

	// get
	public function __get($name)
	{
		if ($name == 'view')
		{
			return $this->get(1);
		}
		elseif ($name == 'arg')
		{
			$array = [];
			$uri = self::paths();
			$array = array_splice($uri, 2);

			return $array;
		}
	}
}
<?php

class Objects implements ArrayAccess
{
	private $object = [];
	private $attach = 0;
	private $__before = 0;
	public  $where_clause = null;

	public function __get($name)
	{
		if (is_array($this->object))
		{
			return isset($this->object[$name]) ? $this->object[$name] : null;	
		}
		elseif (is_object($this->object))
		{
			return @$this->object->{$name} | false;
		}
		
	}

	public function __set($key, $value)
	{
		if (is_string($value))
		{
			$value .= ($this->attach > 0) ? $this->attach : null;
		}

		$this->object[$key] = $value;

		if (is_null($this->attach))
		{
			$this->attach = $this->__before;
		}
	}

	public function readObject()
	{
		return $this->object;
	}

	public function append($value)
	{
		$this->object[] = $value;
	}

	public function pop()
	{
		array_pop($this->object);
	}

	public function push($value)
	{
		if (!is_object($value) && !is_array($value))
		{
			array_unshift($this->object, $value);
		}
		else
		{
			if (count($this->object) > 0)
			{
				$this->object = array_merge($this->object, $value);
			}
			else
			{
				$this->object = (array) $value;	
			}
			
		}
	}

	public function make($data)
	{
		$this->object = $data;
	}

	public function offsetGet($key)
	{
		return $this->{$key};
	}

	public function offsetExists($key)
	{
		if (isset($this->object[$key]))
		{
			return true;
		}

		return false;
	}

	public function offsetSet($key, $value)
	{
		$this->{$key} = $value;
	}

	public function offsetUnset($key)
	{
		unset($this->object[$key]);
	}

	public function length()
	{
		return count($this->object);
	}

	public function json()
	{
		if ($this->length() > 0)
		{
			return json_encode($this->object);
		}

		return json_encode(['status' => 'empty']);
	}

	public function _array()
	{
		if ($this->length() > 0)
		{
			return $this->object;
		}

		return false;
	}

	public function post()
	{
		if ($this->length() > 0)
		{
			$_POST = $this->object;
			return true;
		}

		return false;
	}

	public function get()
	{
		if ($this->length() > 0)
		{
			return http_build_query($this->object);
		}

		return false;
	}

	public function string($string)
	{
		$before = $this->attach;

		$this->attach = null;

		return ($string) . ($before > 0 ? $before : null);
	}

	public function fixed($string)
	{
		$before = $this->attach;
		$this->attach = null;
		$this->__before = $before;
		return $string;
	}

	public function shuffle($string)
	{
		$shr = str_shuffle($string);

		$before = $this->attach;
		$this->attach = null;
		$this->__before = $before;
		
		return $shr;
	}

	public function random($string)
	{
		$string = $this->string($string);

		return str_shuffle($string);
	}

	public function add_to_string($data)
	{
		$this->attach = $data;
	}

	public function __call($meth, $param)
	{
		if ($meth == 'where')
		{
			$this->where_clause = $param[0];
		}
		elseif ($meth == 'array')
		{
			return $this->_array();
		}
		elseif ($meth == 'isset')
		{
			return $this->_isset($param[0]);
		}
	}

	public function _isset($key)
	{
		return isset($this->object[$key]) ? true : false;
	}
}
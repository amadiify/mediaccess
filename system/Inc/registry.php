<?php
// registry class
namespace Moorexa;

/**
 * @package Moorexa Data Registry
 * @version 0.0.1
 * @author Ifeanyi Amadi
 */

class Registry
{
    // packed array
    public $packed = [];
    private static $packedStatic = [];
    private $__packing = false;
    private $__packingName = null;
    private $breadcum = [];
    private static $bindData = [];

    // pack method
    final function pack($name, $option)
    {
        $ps = toArray(Registry::$packedStatic);

        // create a new object
        $object = new \Objects(); 
        $pack = false;

        // check if option is callable.
        if (is_callable($option))
        {
            $option($object);

            if ($object->length() > 0)
            {
                array_each($object->array(), function($val, $key) use (&$ps, $name){
                    $ps[$name][$key] = $val;
                });

                $pack = true;
            }
        }
        elseif (is_array($option) || is_object($option))
        {
            if (count($option) > 0)
            {
                array_each($option, function($val, $key) use (&$ps, $name){
                    $ps[$name][$key] = $val;
                });
                $pack = true;
            }
        }

        if ($pack)
        {
            $ps = toObject($ps);
            $this->packed = $ps;
            Registry::$packedStatic = $ps;

            return $ps;
        }

        return $this;
    }

    // unpack method
    final function unpack()
    {
        $args = func_get_args();

        $ps = Registry::$packedStatic;

        foreach ($args as $i => $name)
        {
            if (isset($ps->{$name}))
            {
                unset(Registry::$packedStatic->{$name});
            }
        }
        return $this;
    }

    // get magic method.
    final function __get($name)
    {
        if ($name == 'size')
        {
            return count(toArray(Registry::$packedStatic));
        }
        else
        {
            if ($this->size > 0)
            {
                $ps = toArray($this->packed);

                if (isset($ps[$name]) && $this->__packing === false)
                {
                    $this->__packing = true;
                    $this->__packingName = $name;
                }
                elseif ($this->__packing === true)
                {
                    $this->breadcum[] = $name;
                }
            }
        }


        return $this;
    }

    public function __call($name, $arg)
    {
        if ($this->__packing)
        {
            $parent = $this->__packingName;

            $ps = toArray($this->packed);

            if (isset($ps[$parent]))
            {
                $ps = Registry::$packedStatic;

                if (isset($ps->{$parent}->{$name}))
                {
                    return call_user_func_array($ps->{$parent}->{$name}, $arg);
                }
                else
                {
                    if (count($this->breadcum) > 0)
                    {
                        $build = implode('->', $this->breadcum);

					    Bootloader::$instance->getParameters($ps->{$parent}->{$build}, $name, $const, $arg);

                        return call_user_func_array([$ps->{$parent}->{$build}, $name], $const);
                    
                        
                    }
                }
            }
        }
    }

    // set magic method.
    final function __set($name, $val)
    {
        if ($this->size > 0)
        {
            if ($this->__packing === true)
            {
                $psName = $this->__packingName;
                $ps = Registry::$packedStatic;

                if (count($this->breadcum) > 0)
                {
                    $build = "";

                    foreach ($this->breadcum as $i => $key)
                    {
                        $build .= $key.'->';
                    }

                    $build = rtrim($build, '->');
                    $ps->{$psName}->{$build}->{$name} = $val;
                }
                else
                {
                    $ps->{$psName}->{$name} = $val;
                }

                $this->packed = $ps;
                Registry::$packedStatic = $ps;
            }
        }

        return $this;
    }

    // bind method
	public static function create($name = null, $callback = null)
	{
		if (is_callable($callback) && $name !== null)
		{
			self::$bindData[$name] = $callback;
		}
	}

	// manage request collection
	public static function __callStatic($name, $data)
	{
        $bindData = self::$bindData;
        
		if (count($bindData) > 0)
		{
			if (isset($bindData[$name]))
			{
                if (count($data) == 0 || isset($data[0]) && $data[0] == null)
                {
                    $data[] = new Registry;
                }

				return call_user_func_array($bindData[$name], $data);
			}
		}
	}
}
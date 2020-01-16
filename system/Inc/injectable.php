<?php

namespace Moorexa;

/**
 * @package Moorexa Injectables
 * @author  Amadi ifeanyi
 * @version 0.0.1
 */

class Injectables
{
    // private @var
    private $className = null;

    // private @var object
    private $classObject = null;


    // inject method
    public function inject($class, &$other = null)
    {
        if (is_string($class) && class_exists($class))
        {
            $base = basename(preg_replace('/[\\\\]/', '/', $class));
            $this->className = $base;

            $classObject = new $class;
            $this->classObject = $classObject;

            if (is_string($other))
            {
                $this->className = $other;
            }
        }
        elseif (is_object($class))
        {
            $base = basename(preg_replace('/[\\\\]/', '/', get_class($class)));
            $this->className = $base;

            $this->classObject = $class;

            if (is_string($other))
            {
                $this->className = $other;
            }
        }
        else
        {
            throw new \Exception("Class $class doen't exist. Couldn't make injection.");
        }

        return $this;
    }

    // bind method
    public function bind(&$object, $code = 1)
    {
        if ($this->className !== null)
        {
            $cn = $this->className;

            $object->{$cn} = $this->classObject;

            if ($code === 1)
            {
                // add properties
                $props = get_class_vars(get_class($this->classObject));
                if (count($props) > 0)
                {
                    foreach ($props as $var => $val)
                    {
                        $object->{$var} = $val;
                    }
                } 
                
                // add methods
                $methods = get_class_methods(get_class($this->classObject));
                if (count($methods) > 0)
                {
                    foreach ($methods as $id => $meth)
                    {
                        $object->{$meth} = function() use ($meth)
                        {
                            $args = func_get_args();

                            $const = [];
							Bootloader::$instance->getParameters($this->classObject, $meth, $const, $args);

                            return call_user_func_array([$this->classObject, $meth], $const);
                            
                        };
                    }
                }
            }
        }
    }

    // invoked magic method
    public function __invoke()
    {
        return $this->classObject;
    }
}
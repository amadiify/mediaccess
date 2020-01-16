<?php

namespace Controller;

use Moorexa\Controller;

class ControllerService extends Controller
{
    private $__controller;
    private $stillAlive = false;
    private $viewModel = null;

    public function __construct($class)
    {
        $this->__controller = $class;
    }

    public function __call($meth, $args)
    {
        $class = get_class($this->__controller);

        if ($this->stillAlive !== false)
        {
            if (method_exists($this->viewModel, $meth))
            {
                $const = [];
				Bootloader::$instance->getParameters($this->viewModel, $meth, $const, $args);

				return call_user_func_array([$this->viewModel, $meth], $const);
                
            }
        }

        if (method_exists($this->__controller, $meth))
        {
            // call it
            $const = [];
			Bootloader::$instance->getParameters($this->__controller, $meth, $const, $args);

			$func = call_user_func_array([$this->__controller, $meth], $const);
                
            $modelpath = HOME .'pages/'. $class . '/Models/' . $meth . '.php';

            $model = null;
						
            if (file_exists($modelpath))
            {
                $model	= \Moorexa\Model::{$meth}();
                $this->stillAlive = true;
            }

            $this->viewModel = $model;

            if ($func != null)
            {
                return $func;
            }

            return $this;
        }
        else
        {
            throw new \Exception("Method '$meth' not found in $class Class");
            return $this;
        }
    }

    public function __get($name)
    {
        if ($this->stillAlive === true)
        {
            if (property_exists($this->viewModel, $name))
            {
                return $this->viewModel->{$name};
            }
        }

        if (property_exists($this->__controller, $name))
        {
            return $this->__controller->{$name};
        }
        else
        {
            if (isset(parent::$dropbox[$name]))
            {
                return parent::$dropbox[$name];
            }

            return null;
        }
    }
}

?>
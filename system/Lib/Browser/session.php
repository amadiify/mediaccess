<?php

namespace Moorexa;

/**
 * @package Moorexa Session Manager
 * @version 0.0.1
 * @author  Amadi Ifeanyi <amadiify.com>
 */

class Session extends Hash
{
    private $session_vars = [];
    private $session_key = false;

    public function __construct()
    {
        $this->session_vars = $_SESSION;
    }

    // get session
    final public function get($key)
    {   
        if (is_null($this->domain))
        {
            $this->domain = url();
        }

        if ($this->has($key))
        {
            $key = $this->getKey($key);

            $val = $_SESSION[$key];
            $val = $this->_hashVal($val);

            $val = unserialize($val);

            if (is_array($val) && isset($val['___session__data']))
            {
                $val = $val['___session__data'];
            }
            else
            {
                if (is_array($val) && isset($val['__session__object']))
                {
                    $val = $val['__session__object'];
                }
            }

            return $val;
        }

        return false;
    }

    // set session
    final public function set($key, $val)
    {
        if (is_null($this->domain))
        {
            $this->domain = url();
        }

        if (is_array($val))
        {
            $val = serialize($val);
        }
        elseif (is_callable($val))
        {
            $ref = new \ReflectionFunction($val);
            if ($ref->isClosure())
            {
                $wrapper = new \Opis\Closure\SerializableClosure($val);
                $val = serialize($wrapper);
            }
            else
            {
                $val = serialize(['___session__data' => $val]);
            }
            $ref = null;
        }
        elseif (is_object($val) && !is_callable($val))
        {
            $val = ['__session__object' => $val];
            $val = serialize($val);
        }
        else
        {
            $val = serialize(['___session__data' => $val]);
        }
            
        $hashed = $this->_hash($val);

        $_SESSION[$this->getKey($key)] = $hashed;
        
        return true;
    }

    // get all session
    final public function all()
    {
        if (is_null($this->domain))
        {
            $this->domain = url();
        }

        $sess = $_SESSION;
        $all = [];

        foreach ($sess as $key => $val)
        {
            $keyv = substr($key, strpos($key, '_')+1);

            $hash = $this->getKey($keyv);

            if (isset($_SESSION[$hash]))
            {
                $val = $this->_hashVal($val);

                if (is_serialized($val))
                {
                    $val = unserialize($val);	
                }

                $all[$keyv] = $val;
            }
        }

        return $all;
    }

    // session keys
    final public function keys()
    {
        return array_keys($this->all());
    }

    public function getKey($name)
    {
        if (is_null($this->domain))
        {
            $this->domain = url();
        }

        // secret key
        $key = Bootloader::boot('secret_key');

        if (is_string($name))
        {
            return substr(hash('sha256', $this->domain . '/session/' . $name . '/key/' . $key), 0, 10) . '_' . $name;
        }

        return false;
    }

    // session has key?
    final public function has($key, &$val=null)
    {
        if (isset($_SESSION[$this->getKey($key)]))
        {
            $key = $this->getKey($key);

            $val = $_SESSION[$key];
            $val = $this->_hashVal($val);

            $val = unserialize($val);

            if (is_array($val) && isset($val['___session__data']))
            {
                $val = $val['___session__data'];
            }
            else
            {
                if (is_array($val) && isset($val['__session__object']))
                {
                    $val = $val['__session__object'];
                }
            }
            
            return true;
        }

        return false;
    }

    // return val then delete
    final public function pop($key)
    {
        if (is_null($this->domain))
        {
            $this->domain = url();
        }

        $val = $this->get($key);
        $this->drop($key);
        return $val;
    }

    // drop a session
    final public function drop()
    {
        if (is_null($this->domain))
        {
            $this->domain = url();
        }

        $keys = func_get_args();
        foreach ($keys as $key => $v)
        {
            $key = $this->getKey($v);

            if (isset($_SESSION[$key]))
            {
                unset($_SESSION[$key]);
            }
        }

        return true;
    }

    // empty session
    final public function _empty()
    {
        $all = $this->all();

        if (count($all) > 0)
        {   
            foreach ($all as $key => $val)
            {
                $key = $this->getKey($key);

                if (isset($_SESSION[$key]))
                {
                    unset($_SESSION[$key]);
                }
            }

            return true;
        }

        return false;
    }

    // destroy session
    final public function destroy()
    {
        $this->_empty();
    }

    // check if val matches 
    final public function is($val)
    {
        if (is_null($this->domain))
        {
            $this->domain = url();
        }

        if ($this->session_key !== false)
        {
            $v = $this->get($this->session_key);
            if ($val == $v)
            {
                return true;
            }

            return false;
        }
    }

    // MAGIC METHODS
    public function __call($key, $args)
    {
        if ($key == 'empty')
        {
            return $this->_empty();
        }
        else
        {
            if (!isset($args[0]))
            {
                return $this->get($key);
            }
            else
            {
                if (isset($args[0]))
                {
                    $this->set($key, $args[0]);

                    return true;
                }
            }
        }

        return false;
    }

    public function __get($key)
    {
        return $this->get($key);
    }
}
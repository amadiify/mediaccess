<?php

namespace Moorexa;

/**
 * @package Moorexa Cookie Manager 
 * @version 0.0.1
 * @author  Amadi Ifeanyi <amadiify.com>
 */
 
class Cookie extends Hash
{
    private $cookie_name = null;

    // set cookie
    final public function set()
    {
        $args = func_get_args();
        $name = $args[0];
        $expire = new \DateTime((isset($args[2]) ? $args[2] : "+1 year"));
        $expire = $expire->getTimestamp();
        $value = $args[1];
        $path = isset($args[3]) ? $args[3] : '/';
        $domain = isset($args[4]) ? $args[4] : '';
        $secure = isset($args[5]) ? $args[5] : false;
        $httponly = isset($args[6]) ? $httponly[6] : true;
        $options = isset($args[7]) ? $httponly[7] : [];

        if (isset($_SERVER['HTTPS']))
        {
            $secure = true;
        }

        // hash here
        $name = $this->getKey($name);
        $val = $value;

        // ok manage value
        if (is_array($val))
        {
            $val = serialize($val);
        }
        elseif (is_callable($val))
        {
            $wrapper = new \Opis\Closure\SerializableClosure($val);
            $val = serialize($wrapper);
        }
        elseif (is_object($val) && !is_callable($val))
        {
            $val = ['__cookie__object' => $val];
            $val = serialize($val);
        }
        else
        {
            $val = serialize(['___cookie__data' => $val]);
        }

        $val = $this->_hash($val);

        $size = convertToReadableSize(strlen($val));

        if ($size != '4KB')
        {
            setcookie($name, $val, $expire, $path, $domain, $secure, $httponly);

            if (!$this->has('__moorexa-cookies'))
            {
                $key = $this->getKey('__moorexa-cookies');
                
                if ($name != $key)
                {
                    $this->set('__moorexa-cookies', [$name => $val]);
                }
            }
            else
            {
                $key = $this->getKey('__moorexa-cookies');

                if ($name != $key)
                {
                    $cookies = $this->get('__moorexa-cookies');
                    if (!isset($cookies[$name]))
                    {
                        $cookies[$name] = $val;
                        $this->set('__moorexa-cookies', $cookies);
                    }
                }
            }
              
            return true;
        }
        else
        {
            Event::emit('cookie.error', 'Cookie data too large.');
        }
        
        return false;
        
    }

    public function getKey($name)
    {
        // secret key
        $key = Bootloader::boot('secret_key');

        if (is_string($name))
        {
            return substr(hash('sha256', $this->domain . '/cookie/' . $name . '/key/' . $key), 0, 10) . '_' . $name;
        }

        return false;
    }

    // get cookie
    final public function get($key)
    {
        if ($this->has($key))
        {
            $key = $this->getKey($key);

            $val = $_COOKIE[$key];
            $val = $this->_hashVal($val);

            $val = unserialize($val);

            if (is_array($val) && isset($val['___cookie__data']))
            {
                $val = $val['___cookie__data'];
            }
            else
            {
                if (is_array($val) && isset($val['__cookie__object']))
                {
                    $val = $val['__cookie__object'];
                }
            }

            return $val;
        }

        return false;
    }

    // has cookie
    final public function has($key)
    {
        if (isset($_COOKIE[$this->getKey($key)]))
        {
            return true;
        }

        return false;
    }

    // all cookie
    final public function all()
    {
        $cookie = $this->get('__moorexa-cookies');
        $all = [];

        if ($cookie !== false)
        {
            foreach ($cookie as $key => $val)
            {
                $keyv = substr($key, strpos($key, '_')+1);

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

    // cookie keys
    final public function keys()
    {
        return array_keys($this->all());
    }

    // get cookie then delete
    final public function pop($key)
    {
        $val = $this->get($key);
        $this->drop($key);
        return $val;
    }

    // drop cookie
    final public function drop()
    {
        $cookie = $this->get('__moorexa-cookies');

        $keys = func_get_args();
        foreach ($keys as $key => $v)
        {
            $key = $this->getKey($v);

            setcookie($key, '', time() - 3600);
            if ($cookie !== false)
            {
                unset($cookie[$key]);
                $this->set('__moorexa-cookies', $cookie);
            }
        }

        return true;
    }

    // empty cookie
    final public function _empty()
    {
        $all = $this->all();
        $cookie = $this->get('__moorexa-cookies');

        if (count($all) > 0)
        {   
            foreach ($all as $key => $val)
            {
                $key = $this->getKey($key);
                setcookie($key, '', time() - 3600);

                if ($cookie !== false)
                {
                    unset($cookie[$key]);
                    $this->set('__moorexa-cookies', $cookie);
                }
            }

            return true;
        }

        return false;
    }

    // destroy cookie
    final public function destroy()
    {
        $this->_empty();
    }

    // check if cookie val matches
    final public function is($val)
    {
        if ($this->cookie_key !== false)
        {
            $v = $this->get($this->cookie_key);

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
            if ($this->has($key) && !isset($args[0]))
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
        if ($this->has($key))
        {
            $this->cookie_key = $key;
        }

        return $this;
    }
}
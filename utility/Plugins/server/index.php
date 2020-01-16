<?php

// server class.
// this class is for read only.
class Server
{
    // get all server vars
    public function getData()
    {
        $server = $_SERVER;

        foreach ($server as $key => $val)
        {
            unset($server[$key]);

            // create again
            $key = strtolower($key);
            
            // set
            $server[$key] = $val;
        }
    }

    // check if server has data
    public function has($key, &$val=null)
    {
        // convert to lowercase
        $key = strtolower($key);

        // get all data
        $data = $this->getData();

        // check
        if (isset($data[$key]))
        {
            $val = $data[$key];

            return true;
        }

        // return false
        return false;
    }

    // get data
    public function get($key)
    {
        if ($this->has($key, $val))
        {
            return $val;
        }

        return null;
    }
}
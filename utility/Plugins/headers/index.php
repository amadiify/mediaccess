<?php

/**
 * @package HTTP Headers
 * @author Amadi Ifeanyi <amadiify.com>
 */

 class Headers
 {
    // packed headers
    public static $headers = [];

    // constructor
    public function __construct()
    {
        // get all headers
        $this->getHeaders();
    }

    //  check if header exists.
    public function has($header, &$val=null)
    {
        // convert to lowercase
        $header = strtolower($header);

        // all headers
        $headers = self::$headers;

        if (isset($headers[$header]))
        {
            $val = $headers[$header];

            // return true
            return true;
        }

        return false;
    }

    // get option
    public function get($header)
    {
        if ($this->has($header, $val))
        {
            return $val;
        }

        return null;
    }

    // get header value
    public function val($header)
    {
        if ($this->has($header, $val))
        {
            return $val;
        }

        return null;
    }

    // compare header value
    public function compare($header, $value)
    {
        $val = $this->get($header);

        if ($value == $val)
        {
            return true;
        }

        return false;
    }

    // send json output
    public function json($data)
    {
        switch (gettype($data))
        {
            case 'array':
                echo json_encode($data, JSON_PRETTY_PRINT);
            break;

            case 'object':
                echo json_encode(toArray($data), JSON_PRETTY_PRINT);
            break;

            case 'string':
                $args = func_get_args();
                $combine = array_combine([$args[0]], [$args[1]]);
                echo json_encode($combine, JSON_PRETTY_PRINT);
            break;
        }
    }

    // get browser
    public function browser()
    {
        $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;

        if (!is_null($useragent))
        {
            return 'Web';
        }
        else
        {
            return 'Unknown';
        }
    }

    //  get all headers
    public function getHeaders()
    {
        if (function_exists('getallheaders'))
        {
            // all headers
            $headers = getallheaders();

            // return all headers
            array_each(function($val, $key){
                $key = strtolower($key);
                self::$headers[$key] = $val;
            }, $headers);
        }
    }
 }
<?php 

class Http
{
    private $register = false;
    private static $requests = [];
    private $failed = 0;
    private $http_redirect = false;
    private static $redirect_args = [];
    private $showerror = true;

    public function _request($name)
    {
        if (is_string($name))
        {
            $this->register = strtolower($name);
        }
        
        return $this;
    }

    public function redirect()
    {
        $args = func_get_args();

        if (count(Http::$redirect_args) == 0)
        {
            Http::$redirect_args = $args;
        }

        $args = Http::$redirect_args;

        if ($this->http_redirect === true)
        {
            if (isset($args[$this->failed]))
            {
                $this->showerror = false;
                ob_start();
                $url = url($args[$this->failed]);
                header('location: '.$url);
            }
            else
            {
                $this->showerror = true;
            }
        }

        return new Http;
    }

    public function register()
    {
        $req = func_get_args();
        Http::$requests[$this->register] = $req;
        return $this;
    }
    
    public function getRegister($url)
    {
        $requests = Http::$requests;
        $method = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'get';

        if (!isset($requests[$method]) && count($requests) > 0)
        {
            $i = 0;
            foreach ($requests as $meth => $uri)
            {
                if (in_array($url, $uri))
                {
                    $this->http_redirect = true;
                    $this->failed = $i;

                    $this->redirect();

                    if ($this->showerror === true)
                    {
                        http_response_code(405);
                        http_response(405, strtoupper($method).' Method not allowed for this URL.');
                    }
                    return false;
                    break;
                }

                $i++;
            }   
        }

        $requests = null;
        $method = null;

        return true;
    }
}
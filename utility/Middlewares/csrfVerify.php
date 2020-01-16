<?php 

use Moorexa\Middleware;

class CsrfVerify
{
    private $channels = [];
    public static $error = null;
    public static $headers = [];
    private static $allHeaders = [];

    public function channel(array $channel)
    {
        $this->channels = $channel;
        return $this;
        
    }

    // get headers
    public function getHeaders()
    {
        return self::$allHeaders;
    }

    public function headers()
    {
        $header = func_get_args();
        
        $verify = false;

        if (count($header) > 0)
        {
              foreach ($this->channels as $channel => $type)
              {
                  if ($type === CSRF_VERIFY_HEADER)
                  {
                      if (is_callable('getallheaders'))
                      {
                        $headers = getallheaders();
                        
                        foreach ($header as $i => $h)
                        {
                            $key = trim(substr($h, 0, strpos($h,':')));
                            $v = trim(substr($h, strpos($h,':')+1));

                            if (isset($headers[$key]))
                            {
                                $val = $headers[$key];

                                if ($val == $v)
                                {
                                    $verify = true;
                                    self::$headers[$key] = $val;
                                }
                            }
                        }
                        break;
                      }
                  }
              }

            // get all headers
            foreach ($header as $i => $h)
            {
                $key = trim(substr($h, 0, strpos($h,':')));
                $v = trim(substr($h, strpos($h,':')+1));

                self::$allHeaders[$key] = $v;
            }
                
        }

        if ($verify === false && $type === CSRF_VERIFY_HEADER)
        {
            self::$error = "CSRF Request Token missing or not valid.";
        }
        else
        {
            $verify = true;
        }

        return $verify;
    }
}
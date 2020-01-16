<?php

namespace Moorexa;

class HttpGet extends HttpPost
{   
    public function __construct()
    {
        $get = $_GET;
        if (count($get) > 0)
        {
            $this->isEmpty = false;
        }
        $this->data = $get;
    }
}
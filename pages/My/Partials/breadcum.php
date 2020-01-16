<?php

class Breadcum
{
    public $list = [];

    public function __construct()
    {
        $uri = uri()->paths();
        $breadcum = [];

        if (count($uri) > 1)
        {
            list($controller, $view) = $uri;
            $breadcum[] = '<li class="breadcrumb-item"><a href="'.url('my').'">Home</a></li>';

            if ($view != 'home')
            {
                $breadcum[] = '<li aria-current="page" class="breadcrumb-item active">'.ucwords($view).'</li>';
            }
            else
            {
                $breadcum[] = '<li aria-current="page" class="breadcrumb-item active">Dashboard</li>';
            }
        }

        $this->list = $breadcum;
    }
}
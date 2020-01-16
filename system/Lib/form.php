<?php

namespace Moorexa;

class Form 
{
    private static $instance;
    private $form_request = ['method' => '', 'on' => '', 'data' => [], 'query' => ''];

    private static function createInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new Form;
        }

        self::$instance->form_request = ['method' => '', 'on' => '', 'data' => [], 'query' => ''];

        return self::$instance;
    }

    // 
    public static function __callStatic($method, $data)
    {
        $ins = self::createInstance();
        if (method_exists($ins, '_'.$method))
        {
            return call_user_func_array([$ins, '_'.$method], $data);
        }
    }

    public function __call($method, $data)
    {
        if (method_exists($this, '_'.$method))
        {
            return call_user_func_array([$this, '_'.$method], $data);
        }

        return false;
    }

    // set request method
    public function _method($name, $callback=null)
    {
        // set request method
        $_SERVER['REQUEST_METHOD'] = $name;
        $this->form_request['method'] = $name;

        if (!is_null($callback) && is_callable($callback))
        {
            Route::getParameters($callback, $const, [$this]);
            call_user_func_array($callback, $const);
            // call build method
            return $this->build();
        }

        return $this;
    }

    // list for request
    public function _on($page)
    {
        $this->form_request['on'] = $page;
        return $this;
    }

    // push post data
    public function _push($data)
    {
        if (!is_array($data) && !is_object($data))
        {
            $data = ['post' => $data];
        }

        $_POST = $data;
        $this->form_request['data'] = $data;
        return $this;
    }

    // push query data
    public function _query($data)
    {
        if (!is_array($data) && !is_object($data))
        {
            $data = ['query' => $data];
        }

        $_GET = $data;
        $this->form_request['query'] = $data;

        return $this;
    }

    // build form request.
    public function _build()
    {
        // for now we save to session, later we can save to the database.
        session()->set('form_request', $this->form_request);
    }
}
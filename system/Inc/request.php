<?php

namespace Moorexa;

/**
 * @package Moorexa Request Handler
 * @author  Amadi Ifeanyi
 * @version 0.0.1
 */

trait Request
{

    function request()
    {
        $data = $this->_getData();

        $args = func_get_args();

        $validator = Plugins::validator($data);
        $errors = [];
        $dataSent = [];
        
        foreach ($args as $i => $key)
        {
            if ($key[0] == '^')
            {
                // unset from data
                $key = substr($key, 1);
                if (isset($data[$key]))
                {
                    unset($data[$key]);
                }
            }
            else
            {
                if (strpos($key, ':') !== false)
                {
                    $col = strpos($key, ':');
                    $_key = substr($key, 0, $col);
                    $pattern = substr($key, $col+1);
                    $validator->validate([$_key => $pattern], $errors, $dataSent);
                }
            }
        }

        $inp = file_get_contents('php://input');

        $plain = $inp;
        $addplain = false;

        if (strlen($inp) > 1)
        {
            if (preg_match("/[<][\?](xml)/", $inp))
            {
                $xml = @simplexml_load_string($inp);
                if (is_object($xml))
                {
                    $data = $xml;
                    $addplain = true;
                }    
                else
                {
                    $data = null;
                }
            }
            else
            {
                if (isset($data[$inp]))
                {
                    $dec = json_decode($inp);
                    
                    if ($dec !== null)
                    {
                        $data = $dec;
                        $addplain = true;
                    }
                    else
                    {
                        $data = $inp;
                    }
                }
            }
        }

        $headers = [];
        if (function_exists('getallheaders'))
        {
            $headers = \WekiWork\Http:getHeaders();
        }

        $class = [
        'data' => $data,
        'errors' => $errors,
        'method' => $this->_getMethod(),
        'headers' => $headers,
        'allerrors' => call_user_func(function() use ($errors){
            $tag = new Tag;
            $list = "";
            foreach ($errors as $key => $arr)
            {
                $li = '';
                foreach ($arr as $i => $x)
                {
                    $li .= $tag->li($x)->class('has-error');
                }
                $list .= $tag->h3($key)->close()->ul($li);
            }
            return $tag->section($list)->class('moorexa-request-errors');
        }),
        'list' => call_user_func(function() use ($errors){
            $list = [];
            $index = 1;
            foreach ($errors as $i => $obj)
            {
                foreach ($obj as $key => $val)
                {
                    $list[] = "{$index} - ". $val;
                    $index++;
                }
            }
            return implode("\n", $list);
        })];

        if ($addplain)
        {
            $class['plain'] = $plain;
        }

        if (is_object($data))
        {
            $data = toArray($data);
        }
        
        if (is_array($data))
        {
            $class = array_merge($class, $data);
        }

        if (count($errors) == 0)
        {
            $class['ok'] = true;
        }
        else
        {
            $class['ok'] = false;
        }

        $class['file'] = $_FILES;

        return toObject($class);
    }

    function _getData($key = null, $method = 'post')
    {
        $meth = $_SERVER['REQUEST_METHOD'];

        $data = [];

        switch ($meth)
        {
            case 'POST':
            case 'post':
                $data = $_POST;
            break;

            case 'GET':
            case 'get':
                $data = $_GET;
            break;

            default:
              if (count($_POST) > 0)
              {
                  $data = $_POST;
              }
              elseif (count($_GET) > 0)
              {
                  $data = $_GET;
              }
        }

        if ($key !== null)
        {
            return isset($data[$key]) ? $data[$key] : null;
        }

        return $data;
    }

    function clearData()
    {
        $meth = $_SERVER['REQUEST_METHOD'];

        switch ($meth)
        {
            case 'POST':
            case 'post':
                $_POST = [];
            break;

            case 'GET':
            case 'get':
                $_GET = [];
            break;

            default:
              if (count($_POST) > 0)
              {
                 $_POST = [];
              }
              elseif (count($_GET) > 0)
              {
                 $_GET = [];
              }
        }

        return true;
    }

    function _getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}
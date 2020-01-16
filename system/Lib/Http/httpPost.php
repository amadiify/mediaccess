<?php

namespace Moorexa;

/**
 * @package HTTP Post 
 */

class HttpPost
{
    public $isEmpty = true;
    public $files = [];
    protected $data = [];
    private $whitelist = ['CSRF_TOKEN','REQUEST_VIEWMODEL','SET_VIEWMODEL_DEFAULT','REQUEST_METHOD'];

    public function __construct()
    {
        // fetch data
        $this->fetchData();
    }

    // fetch post data
    private function fetchData()
    {
        $post = $_POST;
        if (count($post) > 0)
        {
            $this->isEmpty = false;
        }
        $this->data = $post;
        
        if (count($_FILES) > 0)
        {
            $this->files = $_FILES;
        }
    }
    
    public function __get($name)
    {
        if ($name == 'getData')
        {
            return $this->data;
        }

        if (isset($this->data[$name]))
        {
            return $this->data[$name];
        }

        return null;
    }

    public function set($key, $val)
    {
        $this->data[$key] = $val;

        return true;
    }

    // get $_FILES
    public function file($name=null, &$data=[])
    {
        $files = $_FILES;

        if (!is_null($name))
        {
            if (isset($files[$name]))
            {
                $size = $files[$name]['size'];

                if ($size > 0)
                {
                    $data = (object) $files[$name];
                    return $data;
                }

                return false;
            }
        }
        else
        {
            if (count($files) > 0)
            {
                $data = $files;
                return $data;
            }
        }

        return false;
    }

    // check if file is an image
    public function isImage($file)
    {
        // convert to array
        $file = is_object($file) ? toArray($file) : $file;

        $mime = isset($file['type']) ? $file['type'] : false;

        if (!is_bool($mime))
        {
            $allowed = array_flip([
                'image/jpg',
                'image/jpeg',
                'image/png',
                'image/gif'
            ]);

            if (isset($allowed[$mime]))
            {
                return true;
            }

            return false;
        }

        return $mime;
    }

    // check if file is video
    public function isVideo($file)
    {
        // convert to array
        $file = is_object($file) ? toArray($file) : $file;

        $mime = isset($file['type']) ? $file['type'] : false;

        if (!is_bool($mime))
        {
            $allowed = array_flip([
                'video/mp4',
                'video/3gpp',
                'video/x-flv',
                'application/x-mpegURL'
            ]);

            if (isset($allowed[$mime]))
            {
                return true;
            }

            return false;
        }

        return $mime;
    }

    // get all post data or get a key value.
    public function get($name = null)
    {
        if ($name === null)
        {
            $data = [];
            foreach ($this->data as $key => $val)
            {
                $data[$key] = is_string($val) ? filter_var($val, FILTER_SANITIZE_STRING) : $val;
                
            }
            return $data;
        }
        else
        {
            if (isset($this->data[$name]))
            {
                return is_string($this->data[$name]) ? filter_var($this->data[$name], FILTER_SANITIZE_STRING) : $this->data[$name];
            }
        }

        return null;
    }

    // invoke class
    public function __invoke()
    {
        return $this->data;
    }

    // return data
    public function data()
    {
        $data = $this->data;

        // filter off whitelist
        $whitelist = array_flip($this->whitelist);

        // run a loop
        foreach ($data as $key => $val)
        {
            if (isset($whitelist[$key]))
            {
                unset($data[$key]);
            }
        }

        $key = null;
        $val = null;

        return $data;
    }

    // unset data
    public function remove()
    {
        $args = func_get_args();

        foreach ($args as $index => $key)
        {
            if (isset($this->data[$key]))
            {
                unset($this->data[$key]);
            }
        }
    }

    // check if key exists
    public function has($name, &$val=null)
    {
        if (isset($this->data[$name]))
        {
            $val = $this->get($name);

            return true;
        }
        elseif (isset($_FILES[$name]))
        {
            $val = $this->file($name);

            return true;
        }

        return false;
    }

    // check if post is empty
    public function isEmpty()
    {
        
        if (count($this->data) == 0 && count($_FILES) == 0)
        {
            return true;
        }

        return false;
    }
}
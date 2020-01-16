<?php
namespace WekiWork;

class Fileio
{
    // manage file upload locally
    private $files = [];
    private $asRefrence = false;
    private $waiting = [];

    // check from
    public function from($data)
    {
        if (is_object($data))
        {
            $this->files = &$data;
            $this->asRefrence = true;
        }
        else
        {
            $this->files = $data;
        }

        return $this;
    }

    // listen for upload
    public function upload()
    {
        $names = func_get_args();
        
        if (count($names) > 0)
        {
            $data = $this->files;

            array_walk($names, function($key) use ($data)
            {
                if (is_object($data))
                {
                    $this->waiting[$key] = $data->{$key};
                }
                else
                {
                    if (is_array($data) && isset($data[$key]))
                    {
                        $this->waiting[$key] = $data[$key];
                    }
                }
            }); 
        }

        return $this;
    }

    // move to destination folder
    public function to($destination, $callback=null)
    {
        $uploads = [];

        if (count($this->waiting) > 0)
        {
            foreach ($this->waiting as $key => $data)
            {
                if (is_array($data))
                {
                    if (isset($data['name']) && !is_array($data['name']))
                    {
                        $name = $data['name'];

                        // check if directory exists
                        if (is_dir($destination))
                        {
                            // get extension
                            $extension = extension($name);
                            // hash filename
                            $filename = sha1(time().$name.$destination.uniqid($extension)).'.'.$extension;

                            if (move_uploaded_file($data['tmp_name'], $destination . '/'. $filename))
                            {
                                $uploads[$key]['code'] = 200;
                                $uploads[$key]['status'] = 'Success';
                                $uploads[$key]['size'] = $data['size'];

                                if (is_callable($callback) && $callback !== null)
                                {
                                    call_user_func_array($callback, [$key, $destination . '/'. $filename, $filename]);
                                }
                            }
                        }
                        else
                        {
                            $uploads[$key]['status'] = 'Destination folder doesn\'t exists.';
                            $uploads[$key]['code'] = 0;
                        }
                    }
                    elseif (isset($data['name']) && is_array($data['name']))
                    {
                        // check if directory exists
                        if (is_dir($destination))
                        {
                            foreach ($data['name'] as $index => $name)
                            {
                                // get extension
                                $extension = extension($name);

                                // hash filename
                                $filename = sha1(time().$name.$destination.uniqid($extension)).'.'.$extension;
                                if (move_uploaded_file($data[$index]['tmp_name'], $destination . '/'. $filename))
                                {
                                    $uploads[$key]['code'] = 200;
                                    $uploads[$key]['status'] = 'Success';
                                    $uploads[$key]['size'] = $data[$index]['size'];

                                    if (is_callable($callback) && $callback !== null)
                                    {
                                        call_user_func_array($callback, [$key, $destination . '/'. $filename, $filename]);
                                    }
                                }
                            }
                        }
                        else
                        {
                            $uploads[$key]['status'] = 'Destination folder doesn\'t exists.';
                            $uploads[$key]['code'] = 0;
                        }
                    }
                }
            }
        }

        return $uploads;
    }
    // -end file upload locally
}
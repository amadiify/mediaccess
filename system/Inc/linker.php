<?php

namespace Moorexa;

/**
 * @package Moorexa Linker
 * @author  Ifeanyi Amadi <helloamadiify@gmail.com>
 * @version 0.0.1
 */

class Linker
{
    private static $instance = null;
    private static $linkerFiles = [];
    private static $linkerDirs  = [];
    private $directory = null;
    private $file = null;
    private static $copyList = [];

    // link method
    public static function link(string $path)
    {
        if (is_null(self::$instance))
        {
            self::$instance = new Linker;
        }

        if (is_dir($path))
        {
            self::$instance->directory = $path;
        }
        elseif (is_file($path))
        {
            self::$instance->file = $path;
        }
        else
        {
            // get caller
            $debug = debug_backtrace()[0];
            $file = $debug['file'];
            $base = basename($file);
            $dir = rtrim($file, $base);

            $path = ltrim($path, '/');

            $path = $dir . $path;

            if (is_dir($path))
            {
                self::$instance->directory = $path;
            }
            elseif (is_file($path))
            {
                self::$instance->file = $path;
            }
        }

        return self::$instance;
    }

    // with method
    public function with(string $path)
    {
        $path = rtrim($path,'/');
        
        if ($this->directory !== null)
        {
            self::$linkerDirs[$path][] = $this->directory;
        }

        if ($this->file != null)
        {
            self::$linkerFiles[$path][] = $this->file;
        }

        $this->directory = null;
        $this->file = null;
    }

    // get directories
    public static function getDirs()
    {
        return self::$linkerDirs;
    }

    // get files
    public static function getFiles()
    {
        return self::$linkerFiles;
    }

    // copy
    public static function copy()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new Linker;
        }

        self::$copyList[] = func_get_args();

        return self::$instance;
    }

    // from directory
    public function from(string $path)
    {
        $last = end(self::$copyList);

        array_pop(self::$copyList);

        if (is_dir($path))
        {
            self::$copyList[$path] = $last;
        }
        else
        {
            // get caller
            $debug = debug_backtrace()[0];
            $file = $debug['file'];
            $base = basename($file);
            $dir = rtrim($file, $base);

            $path = ltrim($path, '/');

            $path = $dir . $path;
            $root = rtrim($_SERVER['SCRIPT_FILENAME'], basename($_SERVER['SCRIPT_FILENAME']));

            $path = str_replace($root, '', $path);

            if (is_dir($path))
            {
                self::$copyList[$path] = $last;
            }
        }

        return $this;
    }

    // destination directory
    public function to()
    {
        $destination = func_get_args();

        if (count($destination) > 0)
        {
            if (count(self::$copyList) > 0)
            {
                $failed = [];

                array_map(function($dest) use (&$failed)
                {
                    if (is_dir($dest))
                    {
                        array_each(function($val, $key) use ($dest, &$failed)
                        {
                            if (is_array($val))
                            {
                                array_map(function($file) use ($key, $dest, &$failed)
                                {
                                    $path = deepScan($key, $file);

                                    if (is_file($path))
                                    {
                                        // check if file doesn't exists in destination
                                        $destCheck = deepScan($dest, $file);

                                        if ($destCheck == '')
                                        {
                                            // copy file
                                            @copy($path, $dest . '/' . $file);
                                        }
                                        else
                                        {
                                            $failed[] = "'$file' exists in $destCheck. Couldn't copy again.";
                                        }
                                    }
                                    else
                                    {
                                        $failed[] = "'$file' doesn't exits in $key";
                                    }
                                }, $val);
                            }

                        }, self::$copyList);
                    }
                }, $destination);

                if (count($failed) > 0)
                {
                    return $failed;
                }
            }
        }

        self::$copyList = [];

        return $this;
    }
}
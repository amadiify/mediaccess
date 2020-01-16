<?php 

namespace Moorexa;

// working with directories
class Dir
{
    // save params 
    public static $params = [];

    // seek for directories
    public static function __callStatic($name, $args)
    {
        $dir = new Dir;
        
        if (!empty(Model::$thirdparty_path))
        {
            $dir->path[] = Model::$thirdparty_path;
        }

        if (isset(Bootloader::$helper['active_c']))
        {
            $dir->path[] = HOME . 'pages/'.Bootloader::$helper['active_c'].'/';
        }
        
        $dir->path[] = HOME . 'pages/';
        $dir->path[] = HOME;
        $dir->findin = $name;

        if (isset($args[0]))
        {
            $dir->sub = $args[0];
        }
        else
        {
            $dir->sub = "";
        }

        return $dir;
    }  
    
    // import command
    public function import()
    {
        // arguments
        $args = func_get_args(); 
        
        // get file requested
        $file = $args[0];

        // remove the file
        unset($args[0]);

        // sort
        sort($args);

        // found path
        $path = "";

        // make a search
        foreach ($this->path as $i => $dir)
        {
            $mdir = findFolder($dir, $this->findin);

            if (!is_null($mdir))
            {
                if (strpos($file, '.php') === false)
                {
                    $scan = deepScan($mdir, [$file . '.php', $file . '.html']);
                }
                else
                {
                    $scan = deepScan($mdir, $file);
                }

                if (!empty($scan) && !empty($this->sub))
                {
                    $sub = str_replace('/', '\/', $this->sub);

                    if (preg_match("/($sub)/", $scan))
                    {
                        $path = $scan;
                        break;
                    }

                    $sub = null;
                }
                elseif (!empty($scan))
                {
                    $path = $scan;
                    break;
                }
            }
        }

        $dir = null;

        // directory file found!
        if (!empty($path))
        {
            // save parameters
            DIR::$params = $args;

            // extract vars from dropbox..
            extract(\Moorexa\Controller::$dropbox);

            $uri = Bootloader::$getUrl;

            $args = ['Cont', 'Meth', 'Arg', 'Arg1', 'Arg2', 'Arg3', 'Arg4'];

            foreach ($args as $i => $def)
            {
                if (!defined($def))
                {
                    $res = array_slice($uri,0,$i);
                    define($def, url(implode('/', $res)) . '/'); 
                }
            }

            if (!defined('URI'))
            {
                define('URI', $uri);
            }
            
            // include path 
            return (include_once $path);
        }
        else
        {
            // Emit directory error.
            Event::emit('dir.error', 'Requested file ['.$file .'] doesn\'t exists in the system');
        }
        
        return "";
    }
}
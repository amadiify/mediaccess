<?php

// autoloader for API
spl_autoload_register(function($class){
    
    // get handler
    $handler = ApiManager::$handler;

    // build path
    $path = HOME . 'api/' . $handler . '/';

    // figure request option
    $class = str_replace('\\', '/', $class);

    // convert to an array
    $classArray = explode('/', $class);
    
    // get folder
    $folder = $classArray[0];
    if (strtolower($folder) == 'model')
    {
        $folder = 'Models';
    }
    
    // remove class from string
    $getclass = array_pop($classArray);

    // replace it
    $classArray[0] = $folder;
    
    // get folder
    $folder = implode('/', $classArray);
    $path .= $folder;

    // config
    $config = [$getclass . '.php', lcfirst($getclass) . '.php', strtolower($getclass) . '.php'];

    // get file
    $path = deepScan($path, $config);

    if (strlen($path) > 1 && file_exists($path))
    {
        // include
        include_once $path;
    }
    else
    {
        // check top level directories
        $topDirs = glob(HOME . 'api/*');

        foreach ($topDirs as $i => $fl)
        {
            if ($fl != '.' && $fl != '..')
            {
                // append folder 
                if (is_dir($fl))
                {
                    $fl .= '/'.$folder.'/';

                    if (is_dir($fl))
                    {
                        // deep scan 
                        $path = deepScan($fl, $config);

                        if (strlen($path) > 1 && file_exists($path))
                        {
                            // include path
                            include_once $path;

                            break;
                        }
                    }
                }
            }
        }
    }
});
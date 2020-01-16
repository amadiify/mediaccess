<?php

namespace Hyphe;

use Masterminds\HTML5;

class Compile
{
    // root directory
    public static $rootDir = 'directives/';

    // cache directory
    private static $cacheDir = __DIR__ .'/Caches/';

    // compile file.
    public static function CompileFile(string $filename, $namespace = '', $directive = null)
    {
        // check directive
        $directive = is_null($directive) ? self::$rootDir : $directive;


        if (file_exists($filename))
        {
            // read file content
            return self::runCompile($filename, $namespace);
        }
        else {
            // check 
            if (is_dir(self::$rootDir))
            {
                // scan for file
                $path = self::deepScan($directive, $filename);

                return self::runCompile($path, $namespace);
            }
        }
    }

    // run compile and return path
    private static function runCompile(string $path, $namespace)
    {
        // path was returned ?
        if (!is_null($path))
        {
            $filename = basename($path);

            $cachename = md5($path) . '.php';

            // yes we read its content.
            $content = file_get_contents($path);

            $default = $content;

            $continue = true;
              
            if (file_exists(self::$cacheDir . $cachename))
            {
                $_content = file_get_contents(self::$cacheDir . $cachename);

                $start = strstr($_content, 'public static function ___cacheData()');

                preg_match('/(return)\s{1,}["](.*?)["]/', $start, $return);

                if (count($return) > 0)
                {
                    $cached = $return[2];

                    if ($cached == md5($default))
                    {
                        $continue = false;
                    }
                }

                // clean up
                $_content = null;
                $return = null;
            }
            
            if ($continue)
            {
                $content = '<!doctype html><html><body>'.$content.'</body></html>';
    
                // read dom
                $html = new HTML5();
                $dom = $html->loadHTML($content);

                $replaces = [];
                $engine = new Engine();

                $cachesize = md5($default);

                foreach ($dom->getElementsByTagName('hy') as $hy)
                {
                    if ($hy->hasAttribute('directive'))
                    {
                        $class = $hy->getAttribute('directive');
                        // inner content
                        $body = self::innerHTML($hy);

                        $classMap = [];
                        $classMap[] = '<?php';
                        if ($namespace != '')
                        {
                            $classMap[] = 'namespace '.$namespace.';';
                        }
                        $classMap[] = 'class '.ucfirst($class). ' extends \Hyphe\Engine {';
                        $classMap[] = html_entity_decode($body);
                        $classMap[] = 'public static function ___cacheData()';
                        $classMap[] = '{';
                        $classMap[] = '  return "'.$cachesize.'";';
                        $classMap[] = '}';
                        $classMap[] = '}';

                        $replaces[] = [
                            'replace' => $dom->saveHTML($hy),
                            'with' => implode("\n\t", $classMap)
                        ];
                    }
                    else
                    {
                        $out = $dom->saveHTML($hy);
                        $inner = self::innerHTML($hy);

                        if ($hy->hasAttribute('func'))
                        {
                            $funcName = $hy->getAttribute('func');
                            $access = $hy->hasAttribute('access') ? $hy->getAttribute('access') : 'public';
                            $args = $hy->hasAttribute('args') ? $hy->getAttribute('args') : '';

                            $func = [];
                            $func[] = $access .' function '. $funcName . '('.$args.')';
                            $func[] = '{';
                            $func[] = html_entity_decode($inner);
                            $func[] = '}'; 

                            $replaces[] = [
                                'replace' => html_entity_decode($out),
                                'with' => implode("\n\t", $func)
                            ];
                        }
                        else
                        {
                            if ($hy->hasAttribute('lang'))
                            {
                                $lang = $hy->getAttribute('lang');

                                switch (strtolower($lang))
                                {
                                    case 'html':

                                        // interpolate props and this
                                        $inner = preg_replace('/(props)[.]([a-zA-Z_]+)/', '$this->props->$2', $inner);
                                        $inner = preg_replace('/(this)[.]([a-zA-Z_]+)/', '$this->$2', $inner);

                                        $inner = html_entity_decode($inner);

                                        $engine->interpolateExternal($inner, $data);
                                        $return = [];
                                        $return[] = '?>';
                                        $return[] = $data;
                                        $return[] = '<?php';

                                        // add replace
                                        $replaces[] = [
                                            'replace' => html_entity_decode($out),
                                            'with' => implode("\n\t", $return)
                                        ];
                                    break;
                                }
                            }    
                        }
                    }
                }

                if (isset($replaces[0]))
                {
                    $default = $replaces[0]['replace'];

                    $count = count($replaces);

                    $default = preg_replace('/(\S+)(=\s*)[\'](.*?)[\']/', '$1$2"$3"', $default);

                    for ($x = 0; $x != $count; $x++)
                    {
                        $default = str_replace($replaces[$x]['replace'], $replaces[$x]['with'], $default);
                    }

                    // interpolate props and this
                    $default = preg_replace('/(props)[.]([a-zA-Z_]+)/', '$this->props->$2', $default);
                    $default = preg_replace('/(this)[.]([a-zA-Z_]+)/', '$this->$2', $default);

                    if (!is_dir(self::$cacheDir))
                    {
                        mkdir(self::$cacheDir);
                    }

                    // save cache file and return path
                    file_put_contents(self::$cacheDir . $cachename, $default);
                }
            }
            
            
            return self::$cacheDir . $cachename;
        }

        return null;
    }

    // parse doc 
    public static function ParseDoc(&$doc)
    {
        // read dom
        $html = new HTML5();
        $dom = $html->loadHTML($doc);

        foreach ($dom->getElementsByTagName('hy') as $hy)
        {
            $directory = self::$rootDir;

            $out = $dom->saveHTML($hy);
            $inner = self::innerHTML($hy);

            $hash = md5($inner);
            $hash = preg_replace('/[0-9]/','',$hash);
            $var = '$'.$hash;

            $inner = html_entity_decode($inner);

            $inner = str_replace('<?=', '{', $inner);
            $inner = str_replace('?>', '}', $inner);

            $content = $var . '= <<<EOT'. "\n";
            $content .= $inner . "\n";
            $content .= 'EOT;';

            if ($hy->hasAttribute('directory'))
            {
                $directory = $hy->getAttribute('directory');
            }

            $build = [];
            $build[] = '<?php';
            $build[] = $content;
            $build[] = 'echo \Hyphe\Engine::ParseTags('.$var.', \''.$directory.'\');';
            $build[] = '?>';

            $build = implode("\n\t", $build);

            $out = html_entity_decode($out);

            $doc = str_ireplace($out, $build, $doc);
        }
    }

    // Make a deep scan for files
    public static function deepScan($dir, $file)
    {
        $getjson = file_get_contents(__DIR__ . '/hyphe.paths.json');
        $json = json_decode($getjson);

        $failed = [];
        
        if (is_object($json))
        {
            $json = (array) $json;
        }
        else
        {
            $json = [];
        }

        $_path = "";
        $updateJson = false;
        $updateFailed = false;

        if(is_array($file))
        {
            $key = $dir.':'.implode(":",$file);

            if (isset($json[$key]))
            {
                $_path =  $json[$key];
            }
            else
            {
                if (!isset($failed[$key]))
                {
                    $found = false;
                    foreach($file as $inx => $ff)
                    {
                        if ($found == false)
                        {
                            $_path = self::__fordeepscan($dir, $ff);
                            if ($_path !== "") {
                                $found = true; 
                                $json[$key] = $_path;
                                $updateJson = true;
                                break;
                            }

                        }
                    }

                    if (!$found)
                    {
                        $updateFailed = true;
                        $failed[$key] = [$dir, $file];
                    }
                }
                else
                {
                    $arr = $failed[$key];
                    $dir = $arr[0];
                    if (is_dir($dir))
                    {
                        foreach ($arr[1] as $i => $file)
                        {
                            $build = $dir . $file;
                            if (file_exists($build))
                            {
                                $_path = $build;
                                break;
                            }
                        }
                    }
                    $arr = null;
                    $dir = null;
                }

                $file = null;
            }
        }
        else
        {
            $key = $dir.':'.$file;

            if (isset($json[$key]))
            {
                $_path = $json[$key];
            }
            else
            {
                if (!isset($failed[$key]))
                {
                    $_path = self::__fordeepscan($dir, $file);
                    if ($_path !== '')
                    {
                        $json[$key] = $_path;
                        $updateJson = true;
                    }

                    if ($_path == '')
                    {
                        $updateFailed = true;
                        $failed[$key] = [$dir, $file];
                    }
                }
                else
                {
                    $arr = $failed[$key];
                    $dir = $arr[0];
                    $build = $dir . $arr[1];

                    if (file_exists($build))
                    {
                        $_path = $build;
                    }
                    $arr = null;
                    $dir = null;
                }
            }
        }

        if ($updateJson)
        {
            $json = json_encode($json, JSON_PRETTY_PRINT);
            file_put_contents(__DIR__ . '/hyphe.paths.json', $json);
        }

        $dir = null;

        return $_path;
    }

    // helper function for deepScan
    private static function __fordeepscan($dir, $file)
    {
        $path = "";
        $scan = glob($dir.'/*');
        $q = preg_quote($file, '\\');

        if (is_array($scan))
        {
            foreach ($scan as $d => $f)
            {
                if ($f != '.' && $f != '..')
                {
                    $f = preg_replace("/[\/]{1,}/", '/', $f);

                    if (!is_dir($f))
                    {
                        $base = basename($f);

                        if (($base == $file) && strrpos($f, $file) !== false)
                        {
                            $path = $f;
                        }

                        $base = null;
                    }

                    if ($path == "")
                    {
                        $path = self::__fordeepscan($f, $file);
                        if ($path !== ""){
                            if (strrpos($path, $file) !== false){
                                break;
                            }
                        }
                    }

                    $f = null;
                }
            }

            $scan = null;
        }

        return $path;
    }

    // read element inner html
    public static function innerHTML(\DOMElement $element)
    {
        $doc = $element->ownerDocument;

        $html = '';

        foreach ($element->childNodes as $node) {
            $html .= $doc->saveHTML($node);
        }

        return $html;
    }
}
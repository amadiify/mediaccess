<?php

namespace Moorexa;

class Rexa
{
    public static $directives = [];
    private static $Mooxes = null;
    public static $clearList = [];

    // if binding
    public static function _if($arguments, $attrLine) : string
    {
        return "<?php if($attrLine) { ?>";
    }

    // else binding
    public static function _else() : string
    {
        return "<?php } else { ?>";
    }

    // end if binding
    public static function _endif() : string
    {
        return "<?php } ?>";
    }

    // end for binding
    public static function _endfor() : string
    {
        return "<?php } ?>";
    }

    // end foreach binding
    public static function _endforeach() : string
    {
        return "<?php } ?>";
    }

    // end while binding
    public static function _endwhile() : string
    {
        return "<?php } ?>";
    }

    // elseif binding
    public static function _elseif($arguments, $attrLine) : string
    {
        return "<?php } elseif ($attrLine) { ?>";
    }

    // foreach binding
    public static function _foreach($arguments, $attrLine) : string
    {
        return "<?php foreach ($attrLine) { ?>";
    }

    // for binding
    public static function _for($arguments, $attrLine) : string
    {
        return "<?php for ($attrLine) { ?>";
    }

    // while binding
    public static function _while($arguments, $attrLine) : string
    {
        return "<?php while ($attrLine) { ?>";
    }

    // load custom directive
    public static function loadDirective(&$content, &$instance, &$chs = null)
    {
        if (is_null($chs))
        {
            $chs = isset(Bootloader::$currentClass->model) ? Bootloader::$currentClass->model : null;
        }

        $pendingElse = [];

        if (self::hasDirectives($content, $matches, $instance))
        {   
            if (is_array($matches) && count($matches[0]) > 0)
            {
                $shouldFail = [];

                foreach ($matches[0] as $index => $attr)
                {
                    $cleanAttr = rtrim($attr, '}');
                    $attrName = $matches[1][$index];

                    // get attribute line
                    $removed = false;
                    $attrLine = trim(ltrim($cleanAttr, '@'.$attrName));
                    if (preg_match("/^[(]/", $attrLine))
                    {
                        $attrLine = preg_replace("/^[(]{1}/", '', $attrLine);
                        if (preg_match("/[)]{1}$/", $attrLine))
                        {
                            $attrLine = preg_replace("/[)]{1}$/", '', $attrLine);
                            $removed = true;
                        }
                    }

                    if (preg_match('/[;]$/', $attrLine) !== false)
                    {
                        $attrLine = preg_replace('/[;]$/', '', $attrLine);
                        if (!$removed)
                        {
                            $attrLine = preg_replace("/[)]{1}$/", '', $attrLine);
                        }
                    }

                    $build = '';

                    // get declearation
                    $output = Rexa::getResponse($attrName, $attrLine, $response);

                    $keywords = ['if', 'else', 'elseif'];
                    
                    if (!preg_match("/\s*[\{]([\s\S]*?)[\?][\>]/", $response) && !preg_match('/([<]\?php)/', $response) && !preg_match('/([?]>)/', $response))
                    {
                        $attrLine = preg_replace("/^['|\"]$/",'',$attrLine);

                        if (preg_match('/[\}]/', $response))
                        {
                            $build = trim($response);
                            $build = preg_replace("/^['|\"]$/",'',$build);
                        }
                        else
                        {
                            $comma = '';

                            if (is_string($attrLine) && strlen($attrLine) > 1)
                            {
                                $comma = ',';
                            }
                            
                            $build = '<?=\Moorexa\Rexa::runDirective(true,\''.$attrName.'\''.$comma.$attrLine.')?>';
                        }

                        $cleanAttr = trim($cleanAttr);

                        if (!is_null($instance->interpolateContent))
                        {
                            $instance->interpolateContent = str_replace($cleanAttr, $build, $instance->interpolateContent);
                        }
                    }
                    else
                    {
                        $output = self::runDirective(false, $attrName, $attrLine, $chs, $response);

                        if (!is_null($instance->interpolateContent))
                        {
                            if ($attrName != 'else')
                            {
                                $instance->interpolateContent = str_replace(trim($cleanAttr), $response, $instance->interpolateContent);
                            }
                            else
                            {
                                $pendingElse[] = [$cleanAttr, $response];
                            }
                        }
                    }
                }
            }
        }

        if (count($pendingElse) > 0)
        {
            foreach ($pendingElse as $i => $arr)
            {
                $instance->interpolateContent = str_replace(trim($arr[0]), $arr[1], $instance->interpolateContent);
            }
        }
    }

    // get directive response
    public static function getResponse($attrName, $attrLine, &$response)
    {
        if (isset(self::$directives[$attrName]))
        {
            $response = self::$directives[$attrName];

            $func = false;

            if (is_string($response))
            {
                if (strpos($response, '::') === false)
                {
                    if (is_callable($response) || function_exists($response))
                    {
                        $func = true;
                    }
                }
            }
            elseif (is_callable($response) && !is_array($response))
            {
                $func = true;
            }
            

            $ref = null;
            $callMethodFromClass = false;

            if ($func)
            {  
                $ref = new \ReflectionFunction($response);
            }
            else
            {
                $responseString = $response;

                if (is_array($response))
                {
                    list($className, $classMethod) = $response;

                    if (is_string($className))
                    {
                        $responseString = '\\' . $className . '::' . $classMethod;
                    }
                }

                if (is_string($responseString) and strpos($responseString, '::') !== false)
                {
                    $start = substr($responseString, 0, strpos($responseString, "::"));
                    $method = substr($responseString, strpos($responseString, '::')+2);

                    if ($start[0] != '\\')
                    {
                        $start = '\\' . $start;
                    }

                    if (class_exists($start))
                    {
                        $callMethodFromClass = true;
                    }
                    else
                    {
                        $response = '';
                    }
                }
            }

            if (!$callMethodFromClass and isset($className))
            {
                if (is_object($className))
                {
                    $start = $className;
                    $method = $classMethod;
                    $callMethodFromClass = true;
                }
            }

            if ($callMethodFromClass)
            {
                // create reflection class
                $ref = new \ReflectionClass($start);
                $ref = $ref->getMethod($method);
            }

            if (is_object($ref))
            {
                $start = $ref->getStartLine()-1;
                $end = $ref->getEndLine();
                $length = $end - $start;
                $filename = $ref->getFileName();
                $source = file($filename);
                $func = implode("", array_slice($source, $start, $length));

                if (stripos($func, 'return') !== false)
                {
                    // check for php
                    if (stripos($func, '<?php') === false)
                    {
                        // get last return
                        $lastReturn = strrpos($func, 'return');
                        $start = substr($func, $lastReturn + strlen('return'));
                        $start = preg_replace('/(\s*)/', '', $start);

                        // get ending curlybrace 
                        $start = substr($start, 0, strrpos($start, '}'));

                        // look for the last statement terminator
                    
                        $end = strrpos($start, ';');
                        $start = substr($start, 0, $end);

                        $response = trim($start);
                    }
                    else
                    {
                        $response = '<?php hasphp(--masking--){ ?>';
                    }
                }
                else
                {
                    $response = '';
                }

                $source = null;
            }
        }
    }

    // add directive
    public static function directive($directiveName, $callbackOrClass)
    {
        self::$directives[$directiveName] = $callbackOrClass;
    }

    // run directives
    public static function runDirective($called = false, $attrName, $attrLine = '', $class = null, &$output = '')
    {
        if (isset(self::$directives[$attrName]))
        {
            $response = self::$directives[$attrName];

            if (is_null($class))
            {
                $class = isset(Bootloader::$currentClass->model) ? Bootloader::$currentClass->model : null;
            }

            if (self::$Mooxes === null)
            {
                self::$Mooxes = new CHS(false);
            }

            $args = func_get_args();

            $arguments = array_splice($args, 2);

            $func = false;

            if (is_string($response))
            {
                if (strpos($response, '::') === false)
                {
                    if (is_callable($response) || function_exists($response))
                    {
                        $func = true;
                    }
                }
            }
            elseif (is_callable($response) && !is_array($response))
            {
                $func = true;
            }

            if (count($arguments) > 0)
            {
                foreach ($arguments as $index => $arg)
                {
                    if (is_string($arg))
                    {
                        $arg = preg_replace("/^(['|\"])|(['|\"]$)/", '', trim($arg));
                        $arguments[$index] = $arg;
                    }
                }
            }

            if ($func)
            {   
                $const = [];
                Route::getParameters($response, $const, $arguments);

                $returned = call_user_func_array($response, $const);

                $output = $returned;

                if (strpos($returned, '<?') !== false)
                {
                    if ($called)
                    {
                        return self::evaluateReturn($returned);
                    }
                }
                else
                {
                    return $returned;
                }
            }
            elseif (is_string($response) || is_array($response))
            {
                $responseString = $response;

                if (is_array($response))
                {
                    // get classname and class response
                    list($className, $classMethod) = $response;

                    if (is_string($className))
                    {
                        $responseString = '\\' . $className . '::' . $classMethod;
                    }
                }

                // execute calling method
                $callMethodFromClass = false;

                if (is_string($responseString) and strpos($responseString, '::') !== false)
                {
                    $class = substr($responseString, 0, strpos($responseString, '::'));
                    $method = substr($responseString, strpos($responseString, "::")+2);

                    if (class_exists($class))
                    {
                        $callMethodFromClass = true;
                    }
                }

                if (!$callMethodFromClass and isset($className))
                {
                    if (is_object($className))
                    {
                        $class = $className;
                        $method = $classMethod;
                        $callMethodFromClass = true;
                    }
                }

                if ($callMethodFromClass)
                {
                    $ref = new \ReflectionClass($class);
                    if ($ref->hasMethod($method))
                    {
                        $arguments[1] = !isset($arguments[1]) ? null : $arguments[1];

                        if (is_null($arguments[1]) and $attrLine != '')
                        {
                            //$arguments[1] = $attrLine;
                        }

                        if ($called)
                        {
                            $returned = call_user_func_array([$class, $method], $arguments);
                        }
                        else
                        {
                            $start = $arguments[0];

                            $start = explode(',', $start);

                            foreach ($start as $i => $s)
                            {
                                $s = preg_replace('/[\'|"]/', '', $s);
                                $arguments[$i] = trim($s);
                            }

                            array_pop($arguments);

                            $returned = call_user_func_array([$class, $method], [$arguments, $attrLine]);
                        }
                        
                        $output = $returned;

                        if (is_string($returned) and strpos($returned, '<?') !== false)
                        {
                            if ($called)
                            {
                                return self::evaluateReturn($returned);
                            }
                        }
                        else
                        {
                            return $returned;
                        }
                    }
                }
            }
        }
    }

    // evaluate return
    private static function evaluateReturn($returned)
    {
        $keywords = ['if', 'elseif'];

        $res = trim(preg_replace('/(<\?php|<\?=)|(\?>)/', '', $returned));
        $res = preg_replace('/^[\}]|[{]$/','',$res);
        $res = preg_replace("/\s*[\(]/", '(', $res);
        
        $res = trim($res);
        
        preg_match("/([\S\s]*?)[\{|\(]/", $res, $command);

        if (count($command) > 0)
        {
            $command = $command[1];
            $keywords = array_flip($keywords);

            if (isset($keywords[$command]))
            {
                $keyword = $command;
                $line = ltrim($res, $keyword);

                $line = preg_replace("/^[(]|[)]$/",'',$line);

                if ($line == '')
                {
                    $line = '0';
                }

                $line = preg_replace("/['|\"]/", '', $line);

                $chs = isset(Bootloader::$currentClass->model) ? Bootloader::$currentClass->model : self::$Mooxes;

                $success = false;

                if ($keyword == 'if')
                {    
                    $success = self::$Mooxes->bindIfStatement($line, $chs);
                }
                elseif ($keyword == 'elseif')
                {
                    $success = self::$Mooxes->bindIfStatement($line, $chs);
                }

                return $success;
            }
        }

        return false;
    }

    // get block
    private static function getBlock($content, $cleanAttr, &$start=null, &$end=null, &$tab=null)
    {
        $contentCopy = $content;
        $startContent = strstr($contentCopy, $cleanAttr);
        $cleanAttr = trim($cleanAttr);
        $quote = preg_quote($cleanAttr);
        preg_match("/(\s{0}(<%:)(.*?))($quote)/", $contentCopy, $m);
        if (isset($m[1]))
        {
            $tabs = $m[1];
            $start = $m[0];

            $block = strstr($content, $start);
            // get end
            $quote = preg_quote($tabs);
            $block = ltrim($block, $start);
            \preg_match("/($quote)[@]([\S]*)/", $block, $m);
            $end = isset($m[0]) ? $m[0] : null;
            
            preg_match("/([\s\S]*?)($end)/", $startContent, $block);
            $getblock = isset($block[0]) ? $block[0] : null;

            $tab = $tabs;

            return $tab . $getblock;
        }
        else
        {
            preg_match("/(\h{0})($quote)/", $contentCopy, $m);
            if (isset($m[0]))
            {
                $start = $m[0];
                $block = strstr($content, $start);
                $block = ltrim($block, $start);

                \preg_match("/(\h{0})[@]([\S]*)/", $block, $m);
                $end = isset($m[0]) ? $m[0] : null;
                
                preg_match("/([\s\S]*?)($end)/", $startContent, $block);
                $getblock = isset($block[0]) ? $block[0] : null;

                $tab = '';

                return $getblock;
            }
        }

        return null;
    }

    // check if document has directive
    private static function hasDirectives(&$content, &$matches = [], &$instance)
    {
        $customDirectives = array_keys(Rexa::$directives);

        if (count($customDirectives) > 0)
        {
            $dir = [[],[]];

            // find directives
            $expression = '/([@|\\\]([a-zA-Z0-9_-])([^\n|;]*)+([;|\n]|))/';
            preg_match_all($expression, $content, $directives);

            if (count($directives[0]) > 0)
            {
                // flip directive
                $flipDirective = array_flip($customDirectives); 

                $cleanDirective = [];

                // loop
                foreach ($directives[0] as $index => $directive)
                {
                    if (!preg_match('/[\(]/', $directive))
                    {
                        if (strpos($directive, ';') !== false)
                        {
                            $exp = explode(';', $directive);

                            foreach ($exp as $i => $d)
                            {
                                $addterm = $d.';';
                                
                                if (strpos($directive, $addterm) !== false)
                                {
                                    $cleanDirective[] = $addterm;
                                }
                                else
                                {
                                    $cleanDirective[] = $d;
                                }
                            }
                        }
                        else
                        {
                            $cleanDirective[] = $directive;
                        }
                    }
                    else
                    {
                        // extract everything inside the bracket
                        preg_match_all('/([\(].*?[\)]?.*)(?<=[\)])/', $directive, $all);
                        foreach ($all[0] as $x => $bracket)
                        {
                            // hide terminator
                            $replace = $bracket;
                            if (strpos($bracket, ';') !== false)
                            {
                                $bracket = str_replace(';', '^&endTerminator', $bracket);
                            }
                            $directive = str_replace($replace, $bracket, $directive);
                        }

                        // Now get directive
                        preg_match_all('/(([@|\\\](.*?)([;|\n]))|([@|\\\](.*)))/', $directive, $gdirective);
                        foreach ($gdirective[0] as $o => $gd)
                        {
                            if (strpos($gd, '^&endTerminator') !== false)
                            {
                                $gd = str_replace('^&endTerminator', ';', $gd);
                            }

                            $cleanDirective[] = $gd;
                        }
                    }
                }

                $directive = null;

                foreach ($cleanDirective as $i => $directive)
                {
                    if (is_string($directive))
                    {
                        if ($directive[0] == '@')
                        {
                            // get name
                            preg_match("/[@]([a-zA-Z0-9\-\_]+)/", $directive, $a);
                            if (isset($a[1]))
                            {
                                $getName = $a[1];

                                if (isset($flipDirective[$getName]))
                                {
                                    // set
                                    $dir[0][] = $directive;
                                    // set name
                                    $dir[1][] = $getName;
                                }
                                else
                                {
                                    self::$clearList[] = $directive;
                                }
                            }
                        }
                        else
                        {
                            if (substr($directive, 0,2) == '\@')
                            {
                                $before = $directive;
                                $directive = substr($directive, 1);

                                $instance->interpolateContent = str_replace($before, $directive, $instance->interpolateContent);
                            }
                        }
                    }
                    
                }   
            }

            $matches = $dir;

            return true;
        }

        return false;
    }

    // add token to document
    private static function addToken(&$content, $attr)
    {
        $attr = trim($attr);
        $quote = preg_quote($attr);
        preg_match("/([\h]*)($quote)/", $content, $ma);

        if (isset($ma[0]))
        {
            $spaces = $ma[0];
            $quote = preg_quote($attr);
            $spaces = preg_replace("/($quote)$/",'',$spaces);
            $replace = '<'.str_repeat('%:', strlen($spaces));
            $before = strstr($content, $attr);
            $newstring = $before;
            $newstring = \preg_replace("/([\n]{1})($spaces)[@]([\S]*)/", "\n".$replace.'@$3', $newstring);
            $newstring = $replace . $newstring;
            $content = str_replace($before, $newstring, $content);
            $content = \preg_replace("/([<]{1,})[@]/",'@', $content);
        }
        
        //var_dump($content);
    }

    // preload directive
    public static function preload()
    {
        static $preloadFunc;

        if (is_null($preloadFunc))
        {
            $preloadFunc = function(){
                return ' ';
            };
        }

        // get arguments
        $args = func_get_args();

        array_walk($args, function($directive) use (&$preloadFunc){
            $directive = preg_replace('/[@|(|)]/', '', $directive);
            if (!isset(self::$directives[$directive]))
            {
                self::$directives[$directive] = $preloadFunc;
            }
        });
    }
}
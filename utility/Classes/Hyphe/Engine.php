<?php

namespace Hyphe;

use Masterminds\HTML5;

/**
 *@author Ifeanyi Amadi https://amadiify.com/
 *@version 1.0
 *@package Hyphe engine class
*/

class Engine
{
    public  static $propsInit;
    private $caller = [];
    public $dir = null;
    private $mask = [];
    private static $instances = null;
    private static $dom = null;
    public  static $variables = [];
    private static $chsInstances = [];
    private $block = "";
    public  $interpolateContent = null;
    private $cachedOutput = null;
    private $cachedFiles = [];
    private static $cachedArray = [];
    private $styles = [];
    public $interpolateString = true;
    public $interpolateExternal = null;
    private static $hypheList = [];

    // self closing tags
    public $selfClosing = [
        'img',
        'meta',
        'link',
        'hr',
        'source',
        'area',
        'base',
        'br',
        'col',
        'embed',
        'input',
        'param',
        'track',
        'wbr'
    ];

    public static function ParseTags(string $doc, string $directive = '')
    {
        $directive = strlen($directive) == 0 ? Compile::$rootDir : $directive;

        if (!is_dir($directive))
        {
            // create directory
            mkdir($directive);
        }

        if (count(self::$hypheList) == 0)
        {
            // load all
            $files = self::getAllFiles($directive);
            $files = self::reduce_array($files);

            foreach($files as $index => $file)
            {
                $base = basename($file);
                $base = substr($base, 0, strpos($base, '.'));

                if (!isset(self::$hypheList[$base]))
                {
                    self::$hypheList[$base] = $file;
                }
            }
        }

        $obj = new Engine();
        $obj->dir = $directive;

        $document = $obj->loadComponent(trim($doc));

        $document = str_replace('<?=', '', $document);
        $document = str_replace('?>', '', $document);

        return $document;
        
    }

    private function hasRequired($name)
    {
        switch ($name)
        {
            case 'assets':
                if (class_exists('\Moorexa\Assets'))
                {
                    return true;
                }
            break;

            case 'rexa':
                if (class_exists('\Moorexa\Rexa'))
                {
                    return true;
                }
            break;

            case 'bootloader':
                if (class_exists('\Moorexa\Bootloader'))
                {
                    return true;
                }
            break;
        }

        return false;
    }

    private function loadComponent($doc, $inner = null)
    {
        $doc = "  $doc";
        static $hasScript;
        static $html;

        if ($hasScript == null)
        {
            $hasScript = [];
        }

        $this->removeStyle($doc);

        $script = strstr($doc, "<script");

        if ($script !== false)
        {
            preg_match_all('/(<script)\s*(.*?)>/', $script, $s);

            if (count($s[0]) > 0)
            {
                $_script = $s[0];
                array_walk($_script, function($x) use (&$doc, &$script, &$hasScript){
                    $tag = $x;
                    $block = $this->getblock($script, $tag, 'script');
                    $strip = trim(strip_tags($block));
                    if (strlen($strip) > 3)
                    {
                        $hash = md5($block);
                        $hasScript[$hash] = $block;
                        $doc = str_replace($block, $hash, $doc);
                    }
                });
                // clean up
                $_script = null;
            }
        }

        //  check if we have tags
        $tags = [];
        $tree = [];

        $copy = $doc;

        if (is_null($html))
        {
            $html = new HTML5();
        }
        
        // load components 
        foreach(self::$hypheList as $tag => $file)
        {
            $hasTag = stristr($doc, "<{$tag}");
            
            if ($hasTag !== false)
            {
                $dom = $html->loadHTML($doc);

                foreach ($dom->getElementsByTagName(lcfirst($tag)) as $element)
                {
                    $block = Compile::innerHTML($element);
                    $inner = $block;
                    // get attributes
                    $props = [];

                    if ($element->hasAttributes())
                    {
                        foreach ($element->attributes as $attribute)
                        {
                            $props[$attribute->name] = $attribute->value;
                        }
                    }

                    if (isset($props['namespace']) || isset($props['directive']))
                    {
                        $dir = isset($props['directive']) ? $props['directive'] : $this->dir;
                        $namespace = isset($props['namespace']) ? $props['namespace'] : null;
                        
                        $scan = Compile::deepScan($dir. '/' . $namespace, $tag . '.html');
                        
                        if ($scan != null)
                        {
                            // compile file
                            $file = Compile::compileFile($scan, $namespace);
                        }
                    }
                    else {
                        // compile file
                        $file = Compile::compileFile($file, null, $this->dir);
                    }

                    $out = $dom->saveHTML($element);
                    $block = $out;

                    // get position
                    $position = stripos($copy, $hasTag);

                    $data = $this->getComponentData($file, $tag, $block, $props);

                    $tree[$position] = [
                        'inner' => $inner,
                        'block' => $block,
                        'tag' => lcfirst($tag),
                        'data' => $data
                    ];
                }
            }
        }

        if (count($tree) > 0)
        {
            ksort($tree);

            $len = count($tree) - 1;
            $keys = array_keys($tree);

            for($x=$len; $x != -1; $x--)
            {
                $data = $tree[$keys[$x]]['data'];
                $block = $tree[$keys[$x]]['block'];
                $tag = $tree[$keys[$x]]['tag'];

                $data = preg_replace("/[<]($tag)\s*(.*?)>/m", '', $data);

                $prev = $x-1;
                if ($prev != -1)
                {
                    for($y=$prev; $y != -1; $y--)
                    {
                        $inner = $tree[$keys[$y]]['inner'];

                        $pos = strpos($inner, $block);
                        $len = strlen($block);

                        if ($pos !== false)
                        {
                            if ($pos === 0)
                            {
                                $after = substr($inner, ($pos + $len));
                                $newinner = $data . $after;
                            }
                            else
                            {
                                $before = substr($inner, 0, $pos);
                                $after = substr($inner, ($pos + $len));
                                $newinner = $before . $data . $after;
                            }

                            $tree[$keys[$y]]['inner'] = $newinner;
                            break;
                        }
                    }
                }
                
            }

            foreach ($tree as $i => $comp)
            {
                $data = $comp['data'];
                $inner = $comp['inner'];
                $block = $comp['block'];
                $before = $data;

                $tag = $comp['tag'];

                $data = preg_replace("/[<]($tag)\s*(.*?)>/m", '', $data);

                if (strpos($data, '(--inner-child-dom--)') !== false)
                {
                    // next inner here
                    $data = str_replace("(--inner-child-dom--)", $inner, $data);
                }

                                
                if ($i > 0)
                {
                    $len = strlen($block);
                    $tillend = substr($doc, 0, ($len + $i));
                    $afterend = substr($doc, ($len + $i));

                    if (stripos($doc, $block) !== false)
                    {
                        $tillend = str_ireplace($block, $data, $tillend);
                        $newblock = $tillend . $afterend;
                        $doc = $newblock;
                    }
                    else
                    {
                        $doc = str_ireplace($before, $data, $doc);
                    }
                }
            }
        }
        
        $doc = trim($doc);
        
        $wrapper = $doc;
        $wrapper = preg_replace('/(<php-var>)([^<]+)(<\/php-var>)/', '', $wrapper);
        
        foreach (self::$hypheList as $tag => $file)
        {
            $hasTag = stripos($wrapper, "<{$tag}");
            
            if ($hasTag !== false)
            {
                $wrapper = $this->loadComponent($wrapper);
                break;
            }
        }

        if ($hasScript !== null)
        {
            if (is_array($hasScript) && count($hasScript) > 0)
            {
                foreach($hasScript as $hash => $block)
                {
                    $wrapper = str_replace($hash, $block, $wrapper);
                }
            }
        }

        return $wrapper;
        
    }

    private function getComponentData($file, $tag, $block, $props)
    {
        $continue = false;
        $render = null;
        $lower = strtolower($tag);
        $props = (object) $props;
        $data = null;

        if (count($this->mask) > 0)
        {
            foreach($props as $key => $val)
            {
                $_key = $tag . '/' . $key;
                if (isset($this->mask[$_key]))
                {
                    $props->{$key} = $this->mask[$_key];
                }
            }
        }

        $obj = (object)[];
        $obj->props = $props;

        $chs = null;
        $all_vars = [];

        if (file_exists($file))
        {
            include_once $file;

            $className = $tag;

            if (isset($props->namespace))
            {
                $className = $props->namespace .'\\' . $tag;
            }

            if ( class_exists ($className))
            {
                $props = $obj->props;
                $continue = true;

                $var = (object)[];

                if ($this->hasRequired('bootloader'))
                {
                    $vars = \Moorexa\Controller::$dropbox;

                    foreach ($vars as $key => $v)
                    {
                        $var->{$key} = $v;
                    }
                }

                $chs = new $className($props, $var);
                $ref = null;
                                        
                $chs->props = $props;
                $chs->props->children = '(--inner-child-dom--)';
                $chs->var = $var;
                $this->caller[] = $chs;

                $data = null;

                

                if (method_exists($chs, 'render'))
                {
                    ob_start();
                    $const = [];

                    if ($this->hasRequired('boot'))
                    {
                        \Moorexa\Bootloader::$instance->getParameters($chs, 'render', $const, [$props]);
                    }

                    $render = call_user_func_array([$chs, 'render'], $const);
                    $data = ob_get_contents();
                    ob_clean();
                }

                self::$chsInstances[$file] = ['render' => $render, 'class' => $chs];
                self::$chsInstances['chs'][strtolower($tag)] = $chs;

                if ($data != null)
                {
                    // $this->removeStyle($data);
                }
            }
        }
        

        return $data;
    }

    // convert shortcuts
    public function convertShortcuts(&$content, $chs = null)
    {

        if ($this->hasRequired('assets'))
        {
            $assets = new \Moorexa\Assets();
        }

        if ($this->hasRequired('bootloader'))
        {
            if (isset(\Moorexa\BootLoader::$helper['a_view']))
            {
                $vw = \Moorexa\Bootloader::$helper['a_view'];
            }
            elseif (isset(\Moorexa\Bootloader::$helper['ROOT_GET']) && isset(\Moorexa\Bootloader::$helper['ROOT_GET'][1]))
            {
                $vw = \Moorexa\Bootloader::$helper['ROOT_GET'][1];
            }
        }

        if (isset($vw))
        {

            $action = function(string $path, $data = null) use ($vw)
            {
                if (is_array($data))
                {
                    $build = http_build_query($data);

                    $pa = $vw .'/'. $path .'?'. rawurlencode($build);

                    return abspath($pa);
                }
                else
                {
                    $pa = $vw .'/'. $path;
                    return abspath($pa);
                }
            };


            $this->action = $action;


            unset($vw);
        }

        if ($this->hasRequired('rexa'))
        {
            \Moorexa\Controller::$dropbox['assets'] = $assets;

            $content = &$this->interpolateContent;
            
            // load directive
            \Moorexa\Rexa::loadDirective($content, $this);
        }

        
        // php-if attribute
        preg_match_all("/<\s*\w.*(php-if=)\s*\"?\s*([\w\s%#\/\.;:_-]?.*)\s*\"(\s*>|(\s*\S*?>))/", $content, $matches);
        if (count($matches) > 0 && count($matches[0]) > 0)
        {
            foreach($matches[0] as $i => $tag)
            {
                // get tag name
                preg_match('/[<]([\S]+)/', $tag, $tagName);
                $tagName = $tagName[1];
                $attribute = 'php-if';
                $attr = preg_quote($attribute, '/');

                // get quote
                preg_match("/($attr)\s*=\s*(['|\"])/",$tag, $getQuote);
                $getQuote = $getQuote[2];

                // get argument for attribute
                preg_match("/($attr)\s*=\s*([$getQuote])([\s\S]*?[$getQuote])/", $tag, $getAttr);
                $getQuote = null;
                
                $attributeDecleration = $getAttr[0];
                
                $getAttr = isset($getAttr[3]) ? $getAttr[3] : null;
                $getAttr = preg_replace('/[\'|"]$/','',$getAttr);

                $ifs = '<?php'."\n";
                $ifs .= 'if('.$getAttr.'){?>'."\n";

                // get before
                $begin = strstr($content, $tag);
                $before = $this->getblock($begin, $tag, $tagName);

                $start = strpos($before, $attributeDecleration);
                $block = substr_replace($before, '', $start, strlen($attributeDecleration));
                $block = preg_replace('/([<])([\S]+)\s{1,}[>]/', '<$2>', $block);

                $ifs .= $block;
                $ifs .= "\n<?php }\n";
                $ifs .= '?>';
                
                if (!is_null($this->interpolateContent))
                {
                    $this->interpolateContent = str_replace($before, $ifs, $this->interpolateContent);
                }
            }
        }

        $matches = null;

        // php-for attribute
        preg_match_all("/<\s*\w.*(php-for=)\s*\"?\s*([\w\s%#\/\.;:_-]?.*)\s*\"(\s*>|(\s*\S*?>))/", $content, $matches);
        if (count($matches) > 0 && count($matches[0]) > 0)
        {
            foreach($matches[0] as $i => $tag)
            {
                // get tag name
                preg_match('/[<]([\S]+)/', $tag, $tagName);
                $tagName = $tagName[1];
                $attribute = 'php-for';
                $attr = preg_quote($attribute, '/');

                // get quote
                preg_match("/($attr)\s*=\s*(['|\"])/",$tag, $getQuote);
                $getQuote = $getQuote[2];

                // get argument for attribute
                preg_match("/($attr)\s*=\s*([$getQuote])([\s\S]*?[$getQuote])/", $tag, $getAttr);
                $getQuote = null;
                
                $attributeDecleration = $getAttr[0];
                
                $getAttr = isset($getAttr[3]) ? $getAttr[3] : null;
                $getAttr = preg_replace('/[\'|"]$/','',$getAttr);

                // get before
                $begin = strstr($content, $tag);
                $before = $this->getblock($begin, $tag, $tagName);
                
                $start = strpos($before, $attributeDecleration);
                $block = substr_replace($before, '', $start, strlen($attributeDecleration));
                $block = preg_replace('/([<])([\S]+)\s{1,}[>]/', '<$2>', $block);
                
                $bind = $attribute;
                $attribute = $getAttr;
                $clear = false;

                if (strpos($attribute, ' in ') > 2)
                {
                    $statement = explode(' in ', $attribute);

                    if (count($statement) == 2)
                    {
                        $left = $statement[0];
                        $right = $statement[1];

                        $vars = '{'.$right.'}';
                        $this->stringHasVars($vars, $chs, true);

                        $val = null;
                        $key = null;

                        $exp = explode(',', $left);
                        foreach($exp as $i => $k)
                        {
                            $exp[$i] = trim($k);
                        }

                        if (count($exp) == 2)
                        {
                            $key = $exp[0];
                            $val = $exp[1];
                        }
                        else
                        {
                            $val = $exp[0];
                        }

                        if (is_numeric($vars))
                        {
                            $right = '$_'.time();
                            $int = intval($vars);
                            $range = range(0, $int);
                            $vars = $range;
                        }

                        $forl = '<?php'."\n";
                        $forl .= 'if (is_array('.$right.') || is_object('.$right.')){'."\n";
                        $forl .= "foreach ($right ";
                        if ($key !== null)
                        {
                            $forl .= "as $key => $val){\n";
                        }
                        else
                        {
                            $forl .= "as $val){\n";
                        }

                        $forl .= "?>\n";
                        $forl .= $block;
                        $forl .= "<?php }\n}?>";

                        if (!is_null($this->interpolateContent))
                        {
                            $this->interpolateContent = str_replace($before, $forl, $this->interpolateContent);
                        }
                    }
                }
                

            }
        }

        $matches = null;

        if ($this->hasRequired('bootloader'))
        {
            // php-while attribute
            preg_match_all("/<\s*\w.*(php-while=)\s*\"?\s*([\w\s%#\/\.;:_-]?.*)\s*\"(\s*>|(\s*\S*?>))/", $content, $matches);
            if (count($matches) > 0 && count($matches[0]) > 0)
            {
                foreach($matches[0] as $i => $tag)
                {
                    // get tag name
                    preg_match('/[<]([\S]+)/', $tag, $tagName);
                    $tagName = $tagName[1];
                    $attribute = 'php-while';
                    $attr = preg_quote($attribute, '/');

                    // get quote
                    preg_match("/($attr)\s*=\s*(['|\"])/",$tag, $getQuote);
                    $getQuote = $getQuote[2];

                    // get argument for attribute
                    preg_match("/($attr)\s*=\s*([$getQuote])([\s\S]*?[$getQuote])/", $tag, $getAttr);

                    $getQuote = null;
                    
                    $attributeDecleration = $getAttr[0];
                    
                    $getAttr = isset($getAttr[3]) ? $getAttr[3] : null;
                    $getAttr = preg_replace('/[\'|"]$/','',$getAttr);

                    // get before
                    $begin = strstr($content, $tag);
                    $before = $this->getblock($begin, $tag, $tagName);

                    $start = strpos($before, $attributeDecleration);
                    $block = substr_replace($before, '', $start, strlen($attributeDecleration));
                    $block = preg_replace('/([<])([\S]+)\s{1,}[>]/', '<$2>', $block);
                    
                    $bind = $attribute;
                    $attribute = $getAttr;
                    $clear = false;


                    if (strpos($attribute, ' is ') > 2)
                    {
                        $statement = explode(' is ', $attribute);

                        if (count($statement) == 2)
                        {
                            $left = trim($statement[0]);
                            $right = $statement[1];

                            $vars = '{'.$right.'}';
                            $this->stringHasVars($vars, $chs, true, $dump);

                            $whilel = '<?php'."\n";
                            $whilel .= 'if (is_array('.$right.') || is_object('.$right.')){'."\n";
                            $whilel .= '\Moorexa\DBPromise::$loopid = 0;'."\n";
                            $whilel .= 'while ('.$left.' = '.$right.'){?>'."\n";
                            $whilel .= $block;
                            $whilel .= "\n<?php }\n}?>";

                            if (!is_null($this->interpolateContent))
                            {
                                $this->interpolateContent = str_replace($before, $whilel, $this->interpolateContent);
                            }
                        }
                    }
                }
            }
        }

        $matches = null;

        $binds = ['php-if::id' => 'id',
                  'php-if::class' => 'class',
                  'php-if::for' => 'for',
                  'php-if::name' => 'name',
                  'php-if::type' => 'type',
                  'php-if::placeholder' => 'placeholder',
                  'php-if::src' => 'src',
                  'php-if::href' => 'href',
                  'php-if::value' => 'value',
                  'php-if::action' => 'action',
                  'php-if::method' => 'method',
                  'php-if::style' => 'style'
                 ];
        foreach($binds as $bind => $attrib)
        {
            $qotr = preg_quote($bind);
            preg_match_all("/<\s*\w.*($qotr=)\s*\"?\s*([\w\s%#\/\.;:_-]?.*)\s*\"(\s*>|(\s*\S*?>))/", $content, $matches);

            $alltags = [];

            if (count($matches[0]) > 0)
            {
                foreach ($matches[0] as $i => $l)
                {
                    $l = trim($l);
                    if (preg_match("/[>]$/", $l))
                    {
                        $alltags[] = $l;
                    }
                    else
                    {
                        $qu = preg_quote($l, '/');
                        preg_match("/($qu)\s*\"?\s*([\w\s%#\/\.;:_-]*)\s*\"?.*>/", $content, $s);
                        if (isset($s[0]))
                        {
                            $alltags[] = $s[0];
                        }
                    }
                }
            }

            if (count($alltags) > 0)
            {
                foreach($alltags as $i => $tag)
                {
                    // get tag name
                    preg_match('/[<]([\S]+)/', $tag, $tagName);
                    $tagName = $tagName[1];
                    $attribute = $bind;
                    $attr = preg_quote($attribute, '/');

                    // get quote
                    preg_match("/($attr)\s*=\s*(['|\"])/",$tag, $getQuote);
                    $getQuote = $getQuote[2];

                    // get argument for attribute
                    preg_match("/($attr)\s*=\s*([$getQuote])([\s\S]*?[$getQuote])/", $tag, $getAttr);
                    
                    $attributeDecleration = $getAttr[0];
                    
                    $getAttr = isset($getAttr[3]) ? $getAttr[3] : null;
                    $getAttr = preg_replace('/[\'|"]$/','',$getAttr);

                    // get before
                    $quote = preg_quote($getAttr, '/');
                    $tagattr = strpos($tag, $bind.'=');
                    $beforeattr = preg_quote(substr($tag, 0, $tagattr));
                    $begin = strstr($content, $tag);
                    $before = $this->getblock($begin, $tag, $tagName);

                    $start = strpos($before, $attributeDecleration);
                    
                    $other = ' '.$attrib.'="<?=('.$getAttr.')?>"';

                    if (!is_null($this->interpolateContent))
                    {
                        $this->interpolateContent = str_replace($attributeDecleration, $other, $this->interpolateContent);
                    }
                }
            }
            
            $matches = null;
            $qotr = null;
        }

        $binds = null;

        if ($this->hasRequired('bootloader'))
        {
            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](a)(.*?)(hy-href|hy-action|hy-shref)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);
                foreach ($matches[0] as $i => $ac)
                {
                    $href = false;
                    $href2 = false;

                    if (strstr($ac, 'hy-href') == true)
                    {
                        $ma = substr($ac, strpos($ac, 'hy-href'));
                        $href = true;
                    }
                    elseif (strstr($ac, 'hy-shref') == true)
                    {
                        $ma = substr($ac, strpos($ac, 'hy-shref'));
                        $href2 = true;
                    }
                    elseif (strstr($ac, 'hy-action') == true)
                    {
                        $ma = substr($ac, strpos($ac, 'hy-action'));
                    }


                    $replace = $ac;

                    $eq = substr($ma, strpos($ma, '=')+1);
                    $eq = preg_replace('/[\'|"]/', '', $eq);

                    $eq = rtrim($eq, '/');
                    $eq = ltrim($eq, '/');
                    $other = null;

                    $eq = preg_replace("/[\{]|[\}]/", '', $eq);

                    $eq = '"'.$eq.'"';

                    if ($href)
                    {
                        $other = 'href="<?=url('.$eq.')?>"';
                    }
                    elseif ($href2)
                    {
                        $other = 'href="<?=url('.$eq.',true)?>"';
                    }
                    else
                    {
                        $other = 'href="<?=action('.$eq.')?>"';
                    }
                    
                    $_ac = str_replace($ma, $other, $ac);
                    $this->interpolateContent = str_replace($replace, $_ac, $this->interpolateContent);
                }

            }
            
            $matches = null;

            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](img)(.*?)(hy-src)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);

                foreach ($matches[0] as $i => $ac)
                {
                    
                    $ma = substr($ac, strpos($ac, 'hy-src'));

                    $replace = $ac;


                    $eq = substr($ma, strpos($ma, '=')+1);
                    $eq = preg_replace('/[\'|"]/', '', $eq);

                    if (!preg_match("/[{]/", $eq))
                    {
                        $eq = ltrim($eq, '<?=');
                        $eq = rtrim($eq, '?>');

                        $eq = '"'.$eq.'"';

                        $other = 'src="<?=$assets->image('.$eq.')?>"';

                        $_ac = str_replace($ma, $other, $ac);
                        $this->interpolateContent = str_replace($replace, $_ac, $this->interpolateContent);

                    }
                }
            }

            $matches = null;

            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](form)(.*?)(hy-action)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);
                
                foreach ($matches[0] as $i => $ac)
                {
                    $ma = substr($ac, strpos($ac, 'hy-action'));

                    $replace = $ac;

                    $eq = substr($ma, strpos($ma, '=')+1);
                    $eq = preg_replace('/[\'|"]/', '', $eq);
                    $other = null;

                    $eq = preg_replace("/[\{]|[\}]/", '', $eq);

                    $eq = '"'.$eq.'"';

                    $other = 'action="<?=action('.$eq.')?>"';

                    
                    $_ac = str_replace($ma, $other, $ac);
                    $this->interpolateContent = str_replace($replace, $_ac, $this->interpolateContent);
                }

            }

            $matches = null;

            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](link)([^\$\n]+)(\$href)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);
                
                foreach ($matches[0] as $i => $ac)
                {
                    $ma = substr($ac, strpos($ac, '$href'));

                    $replace = $ac;


                    $eq = substr($ma, strpos($ma, '=')+1);
                    $eq = preg_replace('/[\'|"]/', '', $eq);
                    $other = null;

                    $eq = preg_replace("/[\{]|[\}]/", '', $eq);

                    $eq = '"'.$eq.'"';

                    $other = 'href="<?=$assets->css('.$eq.')?>"';

                    
                    $_ac = str_replace($ma, $other, $ac);
                    $this->interpolateContent = str_replace($replace, $_ac, $this->interpolateContent);
                }

            }

            $matches = null;

            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](video|audio|source)(.*?)(hy-media)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);
                
                foreach ($matches[0] as $i => $ac)
                {
                    $ma = substr($ac, strpos($ac, 'hy-media'));

                    $replace = $ac;

                    $eq = substr($ma, strpos($ma, '=')+1);
                    $eq = preg_replace('/[\'|"]/', '', $eq);
                    $other = null;

                    $eq = preg_replace("/[\{]|[\}]/", '', $eq);

                    $eq = '"'.$eq.'"';

                    $other = 'src="<?=$assets->media('.$eq.')?>"';

                    $_ac = str_replace($ma, $other, $ac);
                    $this->interpolateContent = str_replace($replace, $_ac, $this->interpolateContent);
                }

            }

            $matches = null;

            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](video|audio|source)(.*?)(hy-poster)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);
                
                foreach ($matches[0] as $i => $ac)
                {
                    $ma = substr($ac, strpos($ac, '$poster'));

                    $replace = $ac;

                    $eq = substr($ma, strpos($ma, '=')+1);
                    $eq = preg_replace('/[\'|"]/', '', $eq);
                    $other = null;

                    $eq = preg_replace("/[\{]|[\}]/", '', $eq);

                    $eq = '"'.$eq.'"';

                    $other = 'poster="<?=$assets->image('.$eq.')?>"';

                    $_ac = str_replace($ma, $other, $ac);
                    $this->interpolateContent = str_replace($replace, $_ac, $this->interpolateContent);
                }

            }

            $matches = null;

            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](script)(.*?)(hy-src)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);

                foreach ($matches[0] as $i => $ac)
                {
                    $ma = substr($ac, strpos($ac, 'hy-src'));

                    $replace = $ac;

                    $eq = substr($ma, strpos($ma, '=')+1);
                    $eq = preg_replace('/[\'|"]/', '', $eq);

                    if (!preg_match("/^[{]/", $eq))
                    {
                        $eq = ltrim($eq, '<?=');
                        $eq = rtrim($eq, '?>');

                        $eq = '"'.$eq.'"';

                        $other = 'src="<?=$assets->js('.$eq.')?>"';

                        $_ac = str_replace($ma, $other, $ac);
                        $this->interpolateContent = str_replace($replace, $_ac, $this->interpolateContent);
                    }
                }
            }

            $matches = null;

            // bind background image
            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<]([\S]+)([^>]+)?(hy-background-image)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]([^>]+|)[>|]/', $this->interpolateContent, $matches);

                if (count($matches[0]) > 0)
                {
                    foreach ($matches[0] as $index => $data)
                    {
                        $replace = $data;
                        $var = $matches[4][$index];
                        $var = preg_replace("/[\{]|[\}]/",'', $var);
                        $var = '"'.$var.'"';
                        $imgStyle = "background-image:url('<?=\$assets->image($var)?>')";
                        preg_match('/(hy-background-image)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $data, $attribute);
                        $attr = $attribute[0];

                        preg_match('/(style)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $data, $style);
                        if (count($style) > 0)
                        {
                            $styles = rtrim(trim(end($style)), ';');
                            $styles .= '; '.$imgStyle.';';
                            $data = str_replace($style[0], 'style="'.$styles.'"', $data);

                        }
                        else
                        {
                            $data = str_replace($attr, 'style="'.$imgStyle.';"', $data);
                        }

                        $data = str_replace($attr,'',$data);

                        $this->interpolateContent = str_replace($replace, $data, $this->interpolateContent);
                    }
                }

            } 
        }
    }

    // remove style
    private function removeStyle(&$data)
    {
        $styles = [];

        if (preg_match_all("/(<style)([\s\S]*?)(<\/style>)/m", $data, $matches))
        {
            foreach ($matches[0] as $index => $style)
            {
                $hash = md5($style);
                $styles[$hash] = $style;
                $data = str_replace($style, "($hash)", $data);
            }

            $this->styles = array_merge($this->styles, $styles);
        }
    }

    // add style
    private function addStyle(&$data)
    {
        if (count($this->styles) > 0)
        {
            foreach ($this->styles as $hash => $style)
            {
                $data = str_replace("($hash)", $style, $data);
            }
        }
    }

    // external
    public function interpolateExternal($data, &$interpolated = null)
    {
        $continue = true;
        $this->removeStyle($data);

        $this->interpolateContent = $data;

        static $hasScript;

        if ($hasScript == null)
        {
            $hasScript = [];
        }

        $data = html_entity_decode($data, ENT_QUOTES, 'UTF-8');

        $script = strstr($data, "<script");

        if ($script !== false)
        {
            preg_match_all('/(<script)\s*(.*?)>/', $script, $s);
            if (count($s[0]) > 0)
            {
                foreach ($s[0] as $i => $x)
                {
                    $tag = $x;
                    $block = $this->getblock($script, $tag, 'script');
                    $strip = trim(strip_tags($block));
                    if (strlen($strip) > 3)
                    {
                        $hash = md5($block);
                        $hasScript[$hash] = $block;
                        $data = str_replace($block, $hash, $data);
                    }
                }
            }
        }

        preg_match_all('/({[\s\S]*?)}/m', $data, $matches);

        if (count($matches) > 0 && count($matches[0]) > 0)
        {
            foreach ($matches[0] as $a => $m)
            {
                if (substr($m, 0, 2) != '{{')
                {
                    $brace = trim($m);
                    $m = ltrim($m, '{');
                    $m = rtrim($m, '}');
                    $m = trim($m);
                    
                    if (preg_match("/^(([\$][\S]+)|([\S]*?[\(]))/", $m))
                    {
                        $type = '=';

                        $c = trim($m);
                        if (preg_match('/[;]$/', $c))
                        {
                            $type = 'php ';
                        }
                        
                        $this->interpolateContent = str_replace($brace, '<?'.$type.$m.'?>', $this->interpolateContent);
                    }

                    $this->convertShortcuts($data);
                }
                else
                {
                    $this->convertShortcuts($data);
                }
            }

            $this->convertShortcuts($data);
        }
        else
        {
            $this->convertShortcuts($data);
        }

        if ($hasScript !== null)
        {
            if (is_array($hasScript) && count($hasScript) > 0)
            {
                foreach($hasScript as $hash => $block)
                {
                    $data = str_replace($hash, $block, $data);
                }
            }
        }

        $this->addStyle($this->interpolateContent);

        $interpolated = $this->interpolateContent;

        $data = preg_replace("/(<%:)(.*?)[@]/", str_repeat(" ", strlen('$2')) . '  @', $data);

        $this->addStyle($data);

        return $data;
    }

    // Helper methods
    private static function getAllFiles($dir)
    {
        $files = [];

        $files = self::___allfiles($dir);

        return $files;
    }

    // for get all files.
    private static function ___allfiles($dir)
    {
        $file = [];

        $glob = glob(rtrim($dir, '/') .'/{,.}*', GLOB_BRACE);

        if (count($glob) > 0)
        {
            foreach ($glob as $i => $p)
            {
                if (basename($p) != '.' && basename($p) != '..')
                {
                    $p = preg_replace("/[\/]{2}/", '/', $p);

                    if (is_file($p))
                    {
                        $file[] = $p;
                    }
                    elseif (is_dir($p))
                    {
                        $file[] = self::___allfiles($p);
                    }
                }
            }
        }
        
        $glob = null;

        return $file;
    }

    // reduce array
    private static function reduce_array($array)
    {	
        $arr = [];
        $arra = self::__reduceArray($array, $arr);

        return $arra;
    }

    private static function __reduceArray($array, $arr)
    {

        if (is_array($array))
        {
            foreach ($array as $a => $val)
            {
                if (!is_array($val))
                {
                    $arr[] = $val;
                }
                else
                {
                    foreach($val as $v => $vf)
                    {
                        if (!is_array($vf))
                        {
                            $arr[] = $vf;
                        }
                        else
                        {
                            $arr = self::__reduceArray($vf, $arr);
                        }
                    }
                }
            }
        }

        return $arr;
    }
}
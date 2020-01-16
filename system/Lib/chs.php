<?php

namespace Moorexa;

use Moorexa\View;

/**
 *@author Ifeanyi Amadi https://amadiify.com
 *@version 1.0
 *@package Moorexa Core Template Engine for HTML
*/

class CHS
{
    private static $instances = null;
    private $block = "";
    public  $interpolateContent = null;
    private $styles = [];
    public $interpolateString = true;
    public $interpolateExternal = null;


    // convert shortcuts
    public function convertShortcuts(&$content, $chs = null)
    {
        if (self::$instances === null)
        {
            self::$instances['assets'] = (new Assets());
        }

        $assets = self::$instances['assets'];

        if (isset(BootLoader::$helper['a_view']))
        {
            $vw = Bootloader::$helper['a_view'];
        }
        elseif (isset(Bootloader::$helper['ROOT_GET']) && isset(Bootloader::$helper['ROOT_GET'][1]))
        {
            $vw = Bootloader::$helper['ROOT_GET'][1];
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

        Controller::$dropbox['assets'] = $assets;

        // load directive
        Rexa::loadDirective($content, $this, $chs);
        
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
        
        preg_match_all('/[<](a)(.*)(\$href|\$action|\$shref)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $content, $matches);

        if (count($matches[0]) > 0)
        {
            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](a)(.*)(\$href|\$action|\$shref)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);
                foreach ($matches[0] as $i => $ac)
                {
                    $href = false;
                    $href2 = false;

                    if (strstr($ac, '$href') == true)
                    {
                        $ma = substr($ac, strpos($ac, '$href'));
                        $href = true;
                    }
                    elseif (strstr($ac, '$shref') == true)
                    {
                        $ma = substr($ac, strpos($ac, '$shref'));
                        $href2 = true;
                    }
                    elseif (strstr($ac, '$action') == true)
                    {
                        $ma = substr($ac, strpos($ac, '$action'));
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
        }

        $matches = null;

        preg_match_all('/[<](img)(.*)(\$src)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $content, $matches);

        if (count($matches[0]) > 0)
        {
            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](img)(.*)(\$src)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);

                foreach ($matches[0] as $i => $ac)
                {
                    
                    $ma = substr($ac, strpos($ac, '$src'));

                    $replace = $ac;


                    $eq = substr($ma, strpos($ma, '=')+1);
                    $eq = preg_replace('/[\'|"]/', '', $eq);

                    if (!preg_match("/[{]/", $eq))
                    {
                        $eq = ltrim($eq, '<?=');
                        $eq = rtrim($eq, '?>');

                        $eq = '"'.$eq.'"';

                        $other = null;
                        
                        if (env('bootstrap','use_data-src') === true)
                        {
                            $other = 'data-src="<?=$assets->image('.$eq.')?>"';
                        }
                        else
                        {
                            $other = 'src="<?=$assets->image('.$eq.')?>"';
                        }

                        $_ac = str_replace($ma, $other, $ac);
                        $this->interpolateContent = str_replace($replace, $_ac, $this->interpolateContent);

                    }
                }
            }
        }

        $matches = null;

        preg_match_all('/[<](form)(.*)(\$action)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $content, $matches);
        if (count($matches[0]) > 0)
        {
            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](form)(.*)(\$action)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);
                
                foreach ($matches[0] as $i => $ac)
                {
                    $ma = substr($ac, strpos($ac, '$action'));

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
        }

        $matches = null;

        preg_match_all('/[<](link)(.*)(\$href)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $content, $matches);
        if (count($matches[0]) > 0)
        {
            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](link)(.*)(\$href)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);
                
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
        }

        $matches = null;

        preg_match_all('/[<](video|audio|source)(.*)(\$media)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $content, $matches);
        if (count($matches[0]) > 0)
        {
            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](video|audio|source)(.*)(\$media)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);
                
                foreach ($matches[0] as $i => $ac)
                {
                    $ma = substr($ac, strpos($ac, '$media'));

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
        }


        preg_match_all('/[<](video|audio|source)(.*)(\$poster)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $content, $matches);
        if (count($matches[0]) > 0)
        {
            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](video|audio|source)(.*)(\$poster)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);
                
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
        }

        $matches = null;

        preg_match_all('/[<](script)(.*)(\$src)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $content, $matches);
        if (count($matches[0]) > 0)
        {
            if (!is_null($this->interpolateContent))
            {
                preg_match_all('/[<](script)(.*)(\$src)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $this->interpolateContent, $matches);

                foreach ($matches[0] as $i => $ac)
                {
                    $ma = substr($ac, strpos($ac, '$src'));

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
        }

        $matches = null;

        // bind background image
        preg_match_all('/[<]([\S]+)([^>]+)?(\$background-image)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]([^>]+|)[>|]/', $content, $matches);
        if (count($matches) > 0)
        {
            if (count($matches[0]) > 0)
            {
                if (!is_null($this->interpolateContent))
                {
                    preg_match_all('/[<]([\S]+)([^>]+)?(\$background-image)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]([^>]+|)[>|]/', $this->interpolateContent, $matches);

                    if (count($matches[0]) > 0)
                    {
                        foreach ($matches[0] as $index => $data)
                        {
                            $replace = $data;
                            $var = $matches[4][$index];
                            $var = preg_replace("/[\{]|[\}]/",'', $var);
                            $var = '"'.$var.'"';
                            $imgStyle = "background-image:url('<?=\$assets->image($var)?>')";
                            preg_match('/(\$background-image)\s{0,}[=][\'|"]([^\'|"]+)[\'|"]/', $data, $attribute);
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
    }

    // string has variables
    public function stringHasVars(&$data, $chs, $single = false, &$privateDump = [])
    {
        if ($chs == null)
        {
            $chs = Bootloader::$currentClass->model;
        }

        // search for binds
        preg_match_all('/({[\s\S]*?})/m', $data, $matches);

        if (isset($_SERVER['SCRIPT_FILENAME']))
        {
            $root = rtrim($_SERVER['SCRIPT_FILENAME'], basename($_SERVER['SCRIPT_FILENAME']));
        }
        else
        {
            $root = '';
        }

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

                    if (!preg_match('/([\S]*?)[:]\s*\S+/', $m))
                    {
                        // interpolate functions
                        $m = $this->interpolateFunc($m, $chs);

                        if (preg_match('/^(\$this->)/', $m))
                        {
                            $string = $this->loadThis($m, $chs, '$this->', $_var);

                            if (!method_exists($chs, $_var) && !property_exists($chs, $_var))
                            {
                                if ($string === null)
                                {
                                    if (isset(self::$chsInstances['chs']))
                                    {
                                        $_chs = self::$chsInstances['chs'];
                                        // remove the last
                                        array_pop($_chs);
                                        // start from behind
                                        $ctotal = count($_chs) -1;
                                        $keys = array_keys($_chs);
                                        for($i=$ctotal; $i!=-1; $i--)
                                        {
                                            $_chs_ = $_chs[$keys[$i]];
                                            $_chs_->props = $chs->props;
                                            $da = $this->loadThis($m, $_chs_);
                                            if ($da !== null)
                                            {
                                                $string = $da;
                                                $_chs_ = null;
                                                $da = null;

                                                break;
                                            }
                                        }
                                        $keys = null;
                                        $ctotal=null;
                                        $_chs = null;
                                    }
                                }
                            }
                        }
                        else
                        {
                            if (substr($m, 0,1) == '$')
                            {
                                $var = ltrim($m, '$');
                                $ref = new \ReflectionClass($chs);
                                $___chsName = $ref->getName();

                                $seen = false;

                                if (isset(self::$variables[$___chsName]))
                                {
                                    $vars = self::$variables[$___chsName];
                                    if (isset($vars[$var]))
                                    {
                                        $string = $vars[$var];
                                        $seen = true;
                                    }
                                    else
                                    {
                                        if (strpos($var, '->') !== false)
                                        {
                                            $hasdash = substr($var, 0, strpos($var, '-'));
                                            
                                            if (isset($vars[$hasdash]))
                                            {
                                                $data = $vars[$hasdash];
                                                $privateDump[0] = $data;

                                                $exp = explode('->', $var);
                                                $string = $data;
                                                
                                                unset($exp[0]);

                                                foreach($exp as $i => $chain)
                                                {
                                                    if (strpos($chain, '(') !== false)
                                                    {
                                                        $func = $this->loadFunc($chain, $data, true);
                                                        $string = $func;
                                                        $privateDump[1] = $chain;
                                                        $seen = true;
                                                    }
                                                    else
                                                    {

                                                        if (strpos($chain, '[') !== false)
                                                        {
                                                            $s = strpos($chain, '[');
                                                            $st = substr($chain, 0, $s);
                                                            $arr = substr($chain, $s);
                                                            $arr = stripslashes($arr);
                                                            $arr = stringToArray($arr);
                                                            $string = $data->{$st}[is_avail(0, $arr)];
                                                            $seen = true;
                                                        }   
                                                        else
                                                        {
                                                            $string = $data->{$chain};
                                                            $seen = true;
                                                        }
                                                    }
                                                }
                                            }
                                            elseif (isset(Controller::$dropbox[$hasdash]))
                                            {
                                                $data = Controller::$dropbox[$hasdash];
                                                $privateDump[0] = $data;

                                                $exp = explode('->', $var);
                                                $string = $data;
                                                
                                                unset($exp[0]);

                                                foreach($exp as $i => $chain)
                                                {
                                                    if (strpos($chain, '(') !== false)
                                                    {
                                                        $func = $this->loadFunc($chain, $data, true);
                                                        $string = $func;
                                                        $privateDump[1] = $chain;
                                                        $seen = true;
                                                    }
                                                    else
                                                    {

                                                        if (strpos($chain, '[') !== false)
                                                        {
                                                            $s = strpos($chain, '[');
                                                            $st = substr($chain, 0, $s);
                                                            $arr = substr($chain, $s);
                                                            $arr = stripslashes($arr);
                                                            $arr = stringToArray($arr);
                                                            $string = $data->{$st}[is_avail(0, $arr)];
                                                            $seen = true;
                                                        }   
                                                        else
                                                        {
                                                            $string = $data->{$chain};
                                                            $seen = true;
                                                        }
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                $string = '<php-var>'.$m.'</php-var>';
                                            }
                                        }
                                    }
                                }
                                
                                if (!$seen)
                                {
                                    if (isset(Controller::$dropbox[$var]))
                                    {
                                        $string = Controller::$dropbox[$var];
                                    }
                                    else
                                    {
                                        $failed = true;

                                        if (strpos($m, '->') !== false)
                                        {
                                            $_var = substr($var, 0, strpos($var, '->'));

                                            if (isset(Controller::$dropbox[$_var]) && is_object(Controller::$dropbox[$_var]))
                                            {
                                                $obj = Controller::$dropbox[$_var];
                                                $str = $this->loadThis($m, $obj, '$'.$_var.'->');
                                                if ($str !== null)
                                                {
                                                    $failed = false;
                                                    $string = $str;
                                                }
                                            }
                                            else
                                            {
                                                if (isset(self::$chsInstances['chs'][$_var]))
                                                {
                                                    $cs = self::$chsInstances['chs'][$_var];
                                                    $cs->props = $chs->props;
                                                    $str = $this->loadThis($m, $cs, '$'.$_var.'->');
                                                    if ($str !== null)
                                                    {
                                                        $failed = false;
                                                        $string = $str;
                                                    }
                                                }
                                            }
                                        }
                                        elseif (strpos($m, '[') !== false)
                                        {
                                            $pos = strpos($var, '[');
                                            $str = substr($var, 0, $pos);
                                            $other = substr($var, $pos);
                                            $other = preg_replace('/[\[]/','',$other);
                                            $other = explode(']', $other);

                                            $arr = null;

                                            if (isset(self::$variables[$___chsName]) && isset(self::$variables[$___chsName][$str]))
                                            {
                                                $arr = self::$variables[$___chsName][$str];
                                            }
                                            elseif (isset(Controller::$dropbox[$str]))
                                            {
                                                $arr = Controller::$dropbox[$str];
                                            }

                                            if ($arr !== null)
                                            {  
                                                foreach ($other as $i => $val)
                                                {
                                                    if ($val != '')
                                                    {
                                                        $val = preg_replace('/[\'|"]/', '', $val);
                                                        $arr = isset($arr[$val]) ? $arr[$val] : $arr;
                                                    }
                                                }

                                                $string = $arr;
                                                $failed = false;
                                                
                                            }
                                        }
                                        
                                        if ($failed)
                                        {
                                            $string = '<php-var>'.$m.'</php-var>';
                                        }
                                    }
                                }
                            }
                            else
                            {
                                if (preg_match('/([\S]*?)\s{0,}[\(]/', $m))
                                {
                                    $string = $this->loadFunc($m, $chs);
                                }
                                else
                                {
                                    $string = $m;
                                }
                            }
                            
                        }

                        if (!$single)
                        {
                            $string = is_array($string) ? json_encode($string) : $string;

                            // replace every encapsulation with it's equivalent data
                            $data = str_replace($brace, $string, $data);
                        }
                        else
                        {
                            $data = $string;
                        }
                    }
                }
            }
        }
    }   

    // get block of html code
    private function getblock($html, $tag, $tagName)
    {
        $html = strstr($html, $tag);
        $html = substr($html, strlen($tag));

        if ($tagName == 'input')
        {
            //var_dump($html);
        }

        $replace = [];
        //$html = preg_replace("/(<\s*\w.*\s*\"?\s*([\w\s%#\/\.;:_-]*)\s*\"?.*>)/", "<>\n".'$1', $html);
        $hr = $this->__getblock($html, $tag, $tagName, $replace);

        
        // get end tag now
        $end = strpos($hr, "</$tagName>");
        $endline = substr(trim($tag),-2);
        $tags = new Tag();

        $lower = strtolower($tagName);
        $selfclosing = array_flip($tags->selfClosing);

        if ($endline != '/>' && !isset($selfclosing[$lower]))
        {
            $block = $tag . substr($hr, 0, $end) . "</$tagName>";

            $repl = [];
            $gb = $this->__getblock($block, $tag, $tagName, $repl);

            $end = strpos($gb, "</$tagName>");

            if ($end !== false)
            {
                $gb = substr($gb, 0, $end);
            }
            foreach ($repl as $stamp => $rep)
            {
                $gb = str_replace($stamp, $rep, $gb);
            }
            
            $block = $gb;
        }
        else
        {
            $block = $tag . substr($hr, 0, $end);
        }
        
        // check if replace has things to do
        if (count($replace) > 0)
        {
            foreach ($replace as $stamp => $rep)
            {
                $block = str_replace($stamp, $rep, $block);
            }
        }

        //$block = preg_replace("/(<\s*\w.*\s*\"?\s*([\w\s%#\/\.;:_-]*)\s*\"?.*>)(<>\n)/",'$1', $block);

        //var_dump($block);

        return $block;
    }
    
    private function __getblock($html, $tag, $tagName, &$replace = [])
    {
        $closeTag = strpos($html, "</$tagName>");
        
        if ($closeTag !== false)
        {
            $beforecloseTag = substr($html, 0, $closeTag + strlen("</$tagName>"));
            
            // find starting tag
            $start = strpos($beforecloseTag, "<$tagName");
            if ($start !== false)
            {   
                $block = substr($beforecloseTag, $start);
                $hash = '{'.md5($block).'}';
                $before = $beforecloseTag;
                $beforecloseTag = str_replace($block, $hash, $beforecloseTag);
                $replace[$hash] = $block;
                $html = str_replace($before, $beforecloseTag, $html);
                $html = $this->__getblock($html, $tag, $tagName, $replace);
                
                return $html;
            }
            else
            {
                return $html;
            }
        }
        
        return $html;
    }

    public function __call($meth, $args)
    {
        return null;
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
    public function interpolateExternal($data, $chs, &$interpolated = null)
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

                    $this->convertShortcuts($data, $chs);
                }
                else
                {
                    $this->convertShortcuts($data, $chs);
                }
            }

            $this->convertShortcuts($data, $chs);
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
        $this->interpolateContent = str_replace('$this->', '$thisModel->', $this->interpolateContent);

        $interpolated = $this->interpolateContent;

        $data = preg_replace("/(<%:)(.*?)[@]/", str_repeat(" ", strlen('$2')) . '  @', $data);

        $this->addStyle($data);

        return $data;
    }

    // interpolate text
    public static function interpolateText(&$data, $chs=null)
    {
        static $th;

        if ($th == null)
        {
            $th = new CHS;
        }

        $th->interpolateContent = $data;

        if (!is_null($chs))
        {
            $chs = $th;
        }

        preg_match_all('/({{[\s\S]*?)}}/m', $data, $matches);
            
        if (count($matches) > 0 && count($matches[0]) > 0)
        {
            foreach ($matches[0] as $a => $m)
            {
                if (substr($m, 0, 2) == '{{')
                {
                    $brace = trim($m);
                    $m = ltrim($m, '{{');
                    $m = rtrim($m, '}}');
                    $m = trim($m);
                    
                    if (preg_match("/^(([\$][\S]+)|([\S]*?[\(]))/", $m))
                    {
                        $type = '=';

                        $c = trim($m);
                        if (preg_match('/[;]$/', $c))
                        {
                            $type = 'php ';
                        }
                        
                        $th->interpolateContent = str_replace($brace, '<?'.$type.$m.'?>', $th->interpolateContent);  
                    }

                    //$th->convertShortcuts($data, $chs);
                }
                else
                {
                    //$th->convertShortcuts($data, $chs);
                }
            }

            //$th->convertShortcuts($data, $chs);
        }
        else
        {
            //$th->convertShortcuts($data);
        }

        $data = $th->interpolateContent;
    }
}
<?php
namespace Moorexa;

/**
 * @package Moorexa HTML Tags
 * @version 0.0.1
 * @author Ifeanyi Amadi <helloamadiify@gmail.com>
 */

class Tag
{
    // supported html attributes
    private $attributes = [
        'class',
        'id',
        'href',
        'enctype',
        'action',
        'data',
        'src',
        'attr',
        'source',
        'type',
        'method',
        'name',
        'placeholder',
        'value',
        'min',
        'max',
        'autoplay',
        'mute',
        'required',
        'content',
        'charset',
        'sizes',
        'rel',
        'async',
        'property',
        'lang',
        'prefix',
        'style',
        'deffer',
        'width',
        'height',
        'accept',
        'accept-charset',
        'accesskey',
        'align',
        'allow',
        'alt',
        'async',
        'autocapitalize',
        'autocomplete',
        'autofocus',
        'autoplay',
        'background',
        'bgcolor',
        'border',
        'buffered',
        'challenge',
        'checked',
        'cite',
        'code',
        'codebase',
        'color',
        'cols',
        'colspan',
        'contenteditable',
        'contextmenu',
        'controls',
        'coords',
        'crossorigin',
        'csp',
        'data',
        'datetime',
        'decoding',
        'default',
        'defer',
        'dir',
        'dirname',
        'disabled',
        'download',
        'draggable',
        'dropzone',
        'enctype',
        'enterkeyhint',
        'for',
        'formaction',
        'formenctype',
        'formmethod',
        'formnovalidate',
        'formtarget',
        'headers',
        'height',
        'hidden',
        'high',
        'hreflang',
        'icon',
        'importance',
        'integrity',
        'intrinsicsize',
        'inputmode',
        'ismap',
        'itemprop',
        'keytype',
        'kind',
        'lang',
        'language',
        'loading',
        'list',
        'loop',
        'low',
        'manifest',
        'maxlength',
        'minlength',
        'media',
        'multiple',
        'muted',
        'novalidate',
        'open',
        'optimum',
        'radiogroup',
        'ping',
        'poster',
        'preload',
        'readonly',
        'referrerpolicy',
        'reversed',
        'rows',
        'rowspan',
        'sandbox',
        'scope',
        'scoped',
        'selected',
        'shape',
        'size',
        'sizes',
        'alot',
        'spellcheck',
        'srcdoc',
        'srclang',
        'srcset',
        'start',
        'step',
        'summary',
        'tabindex',
        'target',
        'translate',
        'usemap',
        'wrap'
    ];

    // can be single
    private $canbesingle = [
        'required',
        'autoplay',
        'async',
        'deffer',
        'loop',
        'preload',
        'readonly',
        'selected'
    ];

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

    public static $began = 0;


    // current tag
    private $construct = [];

    // previous tag
    private $previous = [];

    // dom content
    public $domDocument = null;

    // tag content
    public $htmlMarkup = null;

    // call magic method
    public function __call($meth, $args)
    {
        $meth = strtolower($meth);

        $arg = [];

        foreach ($args as $index => $v)
        {
            if (is_callable($v))
            {
                $tag = new Tag();
                $const = [];
                Route::getParameters($v, $const, [$tag]);
                $return = call_user_func_array($v, $const);

                if ($return == null)
                {
                    if (!is_null($tag->htmlMarkup))
                    {
                        $return = $tag->htmlMarkup;
                    }
                }

                $arg[$index] = $return;
            }
            else
            {
                $arg[$index] = $v;
            }
        }

        $args = $arg;

        if (!in_array($meth, $this->attributes) && substr($meth, 0, 2) != 'on')
        {
            if (count($this->construct) > 0)
            {
                $this->previous[] = $this->construct;
                $this->construct = [];
            }

            if ($meth == 'doctype')
            {
                $this->construct[$meth] = "<!doctype html>{@inner}";
            }
            elseif ($meth == 'close')
            {
                $prev = $this->previous;
                if (count($prev) > 0)
                {
                    $cn = count($prev)-1;
                    $keyb = array_keys($this->previous[$cn])[0];

                    $this->previous[$cn][$keyb] = str_replace("{@inner}", '', $this->previous[$cn][$keyb]);
                    $this->previous[$cn][$keyb] .= '{@inner}';

                    $tagName = is_avail(0, $args);
                    if (!is_null($tagName))
                    {
                        $cn -= 1;
                        for($i=$cn; $i != -1; $i--)
                        {
                            $keyb = array_keys($this->previous[$i])[0];
                            $line = $this->previous[$i][$keyb];

                            $quote = preg_quote("</$tagName>", '/');

                            if (preg_match("/($quote)/", $line))
                            {
                                $values = array_splice($this->previous, $i+1, $cn);

                                $vals = '';
                                
                                foreach ($values as $x => $arr)
                                {
                                    $val = array_values($arr);

                                    if ($vals == '')
                                    {
                                        $vals = $val[0];
                                    }
                                    else
                                    {
                                        $vals = str_replace('{@inner}', $val[0], $vals);
                                    }
                                }

                                $vals = str_replace('{@inner}', '', $vals);

                                $this->previous[$i][$keyb] = str_replace("{@inner}", $vals, $this->previous[$i][$keyb]);
                                $this->previous[$i][$keyb] .= "{@inner}";
                                
                            }
                        }
                    }
                    
                }

            }
            elseif ($meth == 'end')
            {
                if (count($this->previous) > 0)
                {
                    $cn = count($this->previous)-1;
                    $last = [];

                    for ($i=$cn; $i != -1; $i--)
                    {
                        if (isset($this->previous[$i]))
                        {   
                            $prev = $i-1;
                            if (isset($this->previous[$prev]))
                            {
                                $keyb = array_keys($this->previous[$prev])[0];
                                $val = array_values($this->previous[$i])[0];

                                $this->previous[$prev][$keyb] = str_replace("{@inner}", $val, $this->previous[$prev][$keyb]);
                                if ($prev == 0)
                                {
                                    $last = $this->previous[$prev];
                                    $last[$keyb] = str_replace("{@inner}", '', $last[$keyb]);
                                    $last[$keyb] = str_replace("{@attr}", '', $last[$keyb]);
                                    $last[$keyb] .= "\n".'{@inner}';

                                }
                            }
                        }
                    }

                    $this->previous = [];
                    $this->previous[] = $last;
                }
            }
            elseif ($meth == 'inner')
            {
                $this->construct[$meth] = implode(' ', $args)." {@inner}"; 
            }
            else
            {
                if (!in_array($meth, $this->selfClosing))
                {
                    $this->construct[$meth] = "<{$meth}{@attr}>".implode(' ', $args)." {@inner}</{$meth}>";
                }
                else
                {
                    $this->construct[$meth] = "<{$meth}{@attr}>".implode(' ', $args)." {@inner}";   
                }
            }
        }
        else
        {
            $key = array_keys($this->construct);
            $key = end($key);
            
            if (count($args) > 0)
            {
                if ($meth == 'attr')
                {
                    $value = implode(' ', $args).' {@attr}';
                }
                elseif ($meth == 'data')
                {
                    $value = ' '.$meth.'-'.$args[0].'="'.implode(' ', array_splice($args, 1)).'" {@attr}';
                }
                else
                {
                    if ($meth == 'href')
                    {
                        if (!preg_match('/^(([A-Za-z]*)[:](\/\/))/i', $args[0]))
                        {
                            $args[0] = abspath($args[0]);
                        }
                    }
                    elseif ($meth == 'src')
                    {
                        $file = trim($args[0]);
                        $quote = preg_quote($file);

                        if (!preg_match('/^(([A-Za-z]*)[:](\/\/))/i', $quote))
                        {
                            // get extension
                            $ext = explode('.', $file);
                            $ext = trim(strtolower(end($ext)));

                            $assets = new Assets();

                            if ($ext == 'css')
                            {
                                $file = $assets->css[$file];
                            }
                            elseif ($ext == 'js')
                            {
                                $file = $assets->js[$file];
                            }
                            else
                            {
                                $file = $assets->image[$file];
                            }
                        }
                        
                        $args[0] = $file;
                    }

                    $value = ' '.$meth.'="'.implode(' ', $args).'" {@attr}';
                }
            }
            else
            {
                if (in_array($meth, $this->canbesingle))
                {
                    $value = $meth.' {@attr} ';
                }
                else
                {
                    if ($meth == 'attr')
                    {
                        $value = ' {@attr}';
                    }
                    elseif ($meth == 'data')
                    {
                        $value = ' '.$meth.'-'.$key.'="" {@attr}';
                    }
                    else
                    {
                        $value = ' '.$meth.'="" {@attr}';
                    }   
                }
            }

            $this->construct[$key] = str_replace('{@attr}', $value, $this->construct[$key]);
        }

        if ($this->__lastMethod($meth) == $meth)
        {
            $construct = $this->previous;

            if (count($construct) > 1)
            {
                $cn = count($construct)-1;
                for($i=$cn; $i != 0; $i--)
                {
                    $bf = $i-1;
                    if ($bf != -1)
                    {
                        if (isset($construct[$bf]))
                        {
                            $keyb = array_keys($construct[$bf]);

                            if (isset($keyb[0]))
                            {
                                $keyb = $keyb[0];
                                if (isset($construct[$i]))
                                {
                                    $val = array_values($construct[$i])[0];

                                    $construct[$bf][$keyb] = str_replace("{@inner}", $val, $construct[$bf][$keyb]);
                                }
                            }
                        }
                    }
                }
            }
            
            if (count($construct) > 0)
            {
                $line = array_values($construct[0])[0];
                $line2 = array_values($this->construct);
                
                if (isset($line2[0]))
                {
                    $line2 = $line2[0];
                    $line = str_replace('{@inner}', $line2, $line);
                    $line = str_replace(' {@attr}', '', $line);
                    $line = str_replace('{@attr}', '', $line);
                    $line = str_replace('{@inner}', '', $line);
                }
                else
                {
                    $line = str_replace(' {@attr}', '', $line);
                    $line = str_replace('{@attr}', '', $line);
                    $line = str_replace('{@inner}', '', $line);
                }
            }
            else
            {
                $values = array_values($this->construct);

                if (isset($values[0]))
                {
                    $line = $values[0];

                    $line = str_replace(' {@attr}', '', $line);
                    $line = str_replace('{@attr}', '', $line);
                    $line = str_replace('{@inner}', '', $line);
                }
                else
                {
                    $line = '';
                }
            }

        
            $this->previous = [];
            $this->construct = [];

            $this->htmlMarkup = trim($line).PHP_EOL;

            return trim($line).PHP_EOL;
        }

        return $this;
    }

    // get last method chained
   private function __lastMethod($m, $sub = 0)
   {
        static $calledin = 0;

        $trace = debug_backtrace()[1];
        $line = $trace['line'];
        $file = $trace['file'];
        $getFile = file($file);
        $file = null;

        $getLine = trim($getFile[$line-1]);

        if (!preg_match("/(->)($m)/", $getLine))
        {
            $start = "";

            for($i=self::$began; $i <= $line; $i++)
            {
                $start .= $getFile[$i];
            }

            $start = preg_replace('/[\s]/','',$start);
            $getLine = $start;
        }

        $line = null;
        $getFile = null;
        $trace = null;

        preg_match_all("/($m)m/", $getLine, $cnt);
        $length = 0;
        
        if (count($cnt[0]) > 1)
        {
            $calledin += 1;
            $length = count($cnt[0]);
        }


        if ($length == 0)
        {
            $split = preg_split("/(->)($m)/", $getLine);
            $getLine = null;

            if (isset($split[1]))
            {
                if (!preg_match('/[)](->)(\S)/', $split[1]) && preg_match('/[;]$/', $split[1]))
                {
                    $split = null;
                    // last method called.
                
                    return $m;
                }
            }
        }
        else
        {
            
            $length = $length - (isset($this->sub) ? $this->sub : 0);

            if ($calledin == $length)
            {
                $split = preg_split("/(->)($m)/", $getLine);
                $getLine = null;

                if (isset($split[$length]))
                {
                    if (!preg_match('/[)](->)(\S)/', $split[$length]) && preg_match('/[;]$/', $split[$length]))
                    {
                        $split = null;
                        $calledin = 0;
                        $length = 0;
                        // last method called.
                        $this->sub = 0;
                    
                        return $m;
                    }
                }
            }
        }

        return null;
   }

   public function append(string $with)
   {
        if ($this->domDocument !== null)
        {
            $this->domDocument .= "\n" . chs($with);
        }
   }

   public function prepend(string $with, $before = null)
   {
        if ($this->domDocument !== null)
        {
            $with = chs($with);

            if ($before !== null)
            {
                $last = strrpos($this->domDocument, '</'.$before.'>');
                if ($last !== false)
                {
                    $content = substr($this->domDocument, 0, $last);
                    $content .= "\n" . $with . "\n" . '</'.$before.'>';
                    $this->domDocument = $content;

                }
            }
            else
            {
                $this->domDocument = $with . "\n" . $this->domDocument;
            }
        }
   }

   public function keywords(string $keywords)
   {
        $this->____updateDOM('keywords', 'meta', $keywords);    
   }

   public function description(string $text)
   {
        $this->____updateDOM('description', 'meta', $text);
   }

   private function ____updateDOM(string $search, $tag, $content)
   {
        if ($this->domDocument !== null)
        {
            $dom = $this->domDocument;
            $quote = preg_quote($search, '/');
            preg_match_all("/<\s*\w.*(name=)\s*\"?\s*($quote)\s*\"?.*>/", $dom, $matches);
            $new = $this->meta()->name($search)->content($content);
            if (count($matches) > 0 && count($matches[0]) > 0)
            {
                foreach($matches[0] as $i => $is)
                {
                    $quote = preg_quote("<$tag", '/');
                    if (preg_match("/^($quote)/", $is))
                    {
                        $dom = str_replace($is, trim($new), $dom);
                    }
                }
            }
            $this->domDocument = $dom;
        }
   }
}
<?php

namespace Component;

class Forms
{
    public  $table;
    public  $secure = false;
    public  $secure_cols = [];
    public  $where = "";
    private $header = [];
    private $column = 1;
    private $button = false;
    private $dropdown = [];
    private $primary_key = '';
    private $model = '';
    private $method = 'post';
    private $files = [];
    private $data = false; 
    private $required = false;
    private $rename = [];
    private $exclude = [];

    public function customize($array)
    {
        extract($array);

        $this->header = isset($array['header']) ? $header : $this->header;
        $this->column = isset($array['column']) ? $column : $this->column;
        $this->button = isset($array['button']) ? $button : $this->button;
        $this->dropdown = isset($array['dropdown']) ? $dropdown : $this->dropdown;
        $this->primary_key = isset($array['primary_key']) ? $primary_key : '';
        $this->model = isset($array['model']) ? $model : '';
        $this->method = isset($array['method']) ? $method : $this->method;
        $this->data = isset($array['data']) ? $data : false;
        $this->required = isset($array['required']) ? $required : false;
        $this->rename = isset($array['rename']) ? $array['rename'] : [];
        $this->exclude = isset($array['exclude']) ? $exclude : [];
    }

    public function present($show = true)
    {
        $fields = tableInfo($this->table, $this->where, $this->primary_key);
        $numbers = ['int','tinyint','smallint','mediumint','bigint','decimal','float','double','real','real','bit','serial'];
        $texts = ['text','tinytext','mediumtext','longtext','blob','mediumblob','longblob'];

        if (is_array($fields))
        {
            $form = '<div class="w1-end">';

            if (isset($this->header['title']))
            {
                $form .= '<h1 class="'.$this->header['class'].'" id="'.$this->header['id'].'">'.$this->header['title'].'</h1>';
            }

            if (is_array($this->button) || is_string($this->button))
            {

                if ($this->model != "")
                {
                    $form .= '<form action="to.model(\''.$this->model.'\')" method="'.$this->method.'" enctype="multipart/form-data"> '."\n";
                }
                else
                {
                    $form .= '<form action="" method="'.$this->method.'" enctype="multipart/form-data"> '."\n";
                }

            }

            if (strtolower($this->method) == 'post')
            {
                $form .= "\n" . csrf_token() . "\n";
            }

            $cols = $this->column;

            switch($cols)
            {
                case 1:
                    $cols = 'w1-end';
                    break;
                
                case 2:
                    $cols = 'w1-9';
                    break;
                
                case 3:
                    $cols = 'w1-4';
                    break;
                
                case 4:
                    $cols = 'w1-3';
                    break;
                
                case 5:
                    $cols = 'w1-2';
                    break;
            }

            $cols .= ' mor-form-group';

            foreach ($fields as $i => $field)
            {
                $name = $field['name'];
                $val = @$field['value'];
                $type = $field['type'];
                $extra = "";

                if (count($this->exclude) > 0 && in_array($name, $this->exclude))
                {

                }
                else
                {

                    if (is_array($this->required))
                    {
                        if (in_array($name, $this->required))
                        {
                            $extra = 'required="yes"';
                        }
                        else
                        {
                            $extra = "";
                        }
                    }
                    elseif($this->required !== false && strtolower($this->required) == "all")
                    {
                        $extra = 'required="yes"';
                    }

                    if ($this->data === false)
                    {
                        $val = "";
                    }

                    if (count($this->rename) > 0)
                    {
                        if (isset($this->rename[$name]))
                        {
                            $_name = $this->rename[$name];
                        }
                        else
                        {
                            $_name = $name;
                        }
                    }
                    else{ $_name = $name; }

                    $label = '<label for="'.$name.'">'.trim(ucfirst(preg_replace('/[^a-zA-Z0-9]/',' ', $_name))).'</label>';

                    if ($this->primary_key != "" && $this->primary_key == $i)
                    {
                        $pk = $field['name'];
                        $val = @$field['value'];

                        if($this->secure)
                        {
                            $this->secure_cols[] = $pk;
                            $pk = encrypt($pk);
                        }

                        $form .= '<input type="hidden" name="'.$pk.'" value="'.$val.'"> '."\n";
                    }
                    else
                    {
                        
                        if (isset($this->dropdown[$i]))
                        {
                            // dropdown
                            $table = explode("/",$this->dropdown[$i]);

                            $tname = $table[0];
                            $col = isset($table[1]) ? $table[1] : "";

                            if ($col == "")
                            {
                                $col = array_key($tname, $this->dropdown);
                            }

                            $query = \Moorexa\DB::serve()->{$tname}(get());

                            $_col = $col;

                            if (count($this->rename) > 0)
                            {
                                if (isset($this->rename[$col]))
                                {
                                    $_col = $this->rename[$col];
                                }
                            }

                            if($this->secure)
                            {
                                $this->secure_cols[] = $name;
                                $name = encrypt($name);
                            }


                            $label = '<label for="'.$name.'">'.trim(ucfirst(preg_replace('/[^a-zA-Z0-9]/',' ', $_col))).'</label>';

                            if ($this->required != false && $this->required == $name)
                            {
                                $extra = 'required="yes"';
                            }

                            $form .= '<div class="'.$cols.'">'.$label;
                            $form .= '<select name="'.$name.'" '.$extra.'>';
                            
                                if (is_object($query) && $query->rows > 0)
                                {
                                    while($row = $query->object())
                                    {
                                        if ($val != "")
                                        {
                                            if ($row->{$i} == $val)
                                            {
                                                if ($col != "")
                                                {
                                                    $form .= '<option value="'.$val.'" selected>'.ucfirst($row->{$col}).'</option>';
                                                }
                                                else
                                                {
                                                    $form .= '<option value="'.$val.'" selected>'.ucfirst($val).'</option>';
                                                }
                                                
                                            }
                                            else
                                            {
                                                $form .= '<option value="'.$row->{$i}.'">'.ucfirst($row->{$col}).'</option>';   
                                            }
                                        }
                                        else
                                        {
                                            $form .= '<option value="'.$row->{$i}.'">'.ucfirst($row->{$col}).'</option>';   
                                        }
                                    }
                                }
                                else
                                {
                                    $form .= '<option value="">  </option>';
                                }
                            $form .= '</select></div>';
                        }
                        elseif(in_array($i, $this->files))
                        {
                            if ($this->required != false && $this->required == $name)
                            {
                                $extra = 'required="yes"';
                            }

                            if($this->secure)
                            {
                                $this->secure_cols[] = $name;
                                $name = encrypt($name);
                            }

                            $form .= '<div class="'.$cols.'">'.$label.'<input type="file" name="'.$name.'" value="'.$val.'" '.$extra.'> </div>';
                        }
                        else
                        {
                            if ($type == 'boolean' || $type == 'bool')
                            {
                                if ($this->required != false && $this->required == $name)
                                {
                                    $extra = 'required="yes"';
                                }

                                if($this->secure)
                                {
                                    $this->secure_cols[] = $name;
                                    $name = encrypt($name);
                                }

                                $val = $this->where != '' ? @$field['value'] : '';

                                $form .= '<div class="'.$cols.'">'.$label.'<select name="'.$name.'" '.$extra.'>';
                                if ($val != "")
                                {
                                    if ($val == 1)
                                    {
                                        $form .= '<option value="1" selected> Yes </option>';
                                    }
                                    elseif ($val == 0)
                                    {
                                        $form .= '<option value="0" selected> No </option>';
                                    }
                                    
                                }
                                else
                                {
                                    $form .= '<option value="1" > Yes </option>';
                                    $form .= '<option value="0" > No </option>';
                                }

                                $form .= '</select></div>';
                            }

                            elseif (in_array($type, $numbers))
                            {
                                if ($this->required != false && $this->required == $name)
                                {
                                    $extra = 'required="yes"';
                                }

                                if($this->secure)
                                {
                                    $this->secure_cols[] = $name;
                                    $name = encrypt($name);
                                }

                                $val = $this->where != '' ? @$field['value'] : '';

                                $form .= '<div class="'.$cols.'">'.$label.'<input type="number" name="'.$name.'" value="'.$val.'" '.$extra.'> </div>';
                            }

                            elseif ($type == "date")
                            {
                                if ($this->required != false && $this->required == $name)
                                {
                                    $extra = 'required="yes"';
                                }

                                if($this->secure)
                                {
                                    $this->secure_cols[] = $name;
                                    $name = encrypt($name);
                                }

                                $val = $this->where != '' ? @$field['value'] : '';

                                $form .= '<div class="'.$cols.'">'.$label.'<input type="date" name="'.$name.'" value="'.$val.'" '.$extra.'></div>';   
                            }
                            elseif ($type == "time")
                            {
                                if ($this->required != false && $this->required == $name)
                                {
                                    $extra = 'required="yes"';
                                }

                                if($this->secure)
                                {
                                    $this->secure_cols[] = $name;
                                    $name = encrypt($name);
                                }

                                $val = $this->where != '' ? @$field['value'] : '';

                                $form .= '<div class="'.$cols.'">'.$label.'<input type="time" name="'.$name.'" value="'.$val.'" '.$extra.'></div>';   
                            }

                            elseif ($type == "datetime")
                            {
                                if ($this->required != false && $this->required == $name)
                                {
                                    $extra = 'required="yes"';
                                }

                                if($this->secure)
                                {
                                    $this->secure_cols[] = $name;
                                    $name = encrypt($name);
                                }

                                $val = $this->where != '' ? @$field['value'] : '';

                                $form .= '<div class="'.$cols.'">'.$label.'<input type="datetime" name="'.$name.'" value="'.$val.'" '.$extra.'></div>';   
                            }

                            elseif ($type == "year")
                            {
                                if ($this->required != false && $this->required == $name)
                                {
                                    $extra = 'required="yes"';
                                }

                                if($this->secure)
                                {
                                    $this->secure_cols[] = $name;
                                    $name = encrypt($name);
                                }

                                $val = $this->where != '' ? @$field['value'] : '';

                                $form .= '<div class="'.$cols.'">'.$label.'<input type="year" name="'.$name.'" value="'.$val.'" '.$extra.'></div>';   
                            }

                            elseif ($type == "char")
                            {
                                if ($this->required != false && $this->required == $name)
                                {
                                    $extra = 'required="yes"';
                                }

                                if($this->secure)
                                {
                                    $this->secure_cols[] = $name;
                                    $name = encrypt($name);
                                }

                                $val = $this->where != '' ? @$field['value'] : '';

                                $form .= '<div class="'.$cols.'">'.$label.'<input type="text" name="'.$name.'" value="'.$val.'" '.$extra.'></div>';   
                            }

                            elseif ($type == "varchar")
                            {
                                if ($this->required != false && $this->required == $name)
                                {
                                    $extra = 'required="yes"';
                                }

                                if ($name == "password"){$val = "";}

                                if($this->secure)
                                {
                                    $this->secure_cols[] = $name;
                                    $name = encrypt($name);
                                }

                                $val = $this->where != '' ? @$field['value'] : '';

                                $form .= '<div class="'.$cols.'">'.$label.'<input type="text" name="'.$name.'" value="'.$val.'" '.$extra.'></div>';   
                            }
                            elseif (in_array($type, $texts))
                            { 
                                if ($this->required != false && $this->required == $name)
                                {
                                    $extra = 'required="yes"';
                                }

                                if($this->secure)
                                {
                                    $this->secure_cols[] = $name;
                                    $name = encrypt($name);
                                }

                                $val = $this->where != '' ? @$field['value'] : '';

                                $form .= '<div class="'.$cols.'">'.$label.'<textarea name="'.$name.'" '.$extra.'>'.$val.'</textarea></div>';   
                            }
                            else
                            {
                                if ($this->required != false && $this->required == $name)
                                {
                                    $extra = 'required="yes"';
                                }
                                
                                if($this->secure)
                                {
                                    $this->secure_cols[] = $name;
                                    $name = encrypt($name);
                                }

                                $val = $this->where != '' ? @$field['value'] : '';

                                $form .= '<div class="'.$cols.'">'.$label.'<input type="text" name="'.$name.'" value="'.$val.'" '.$extra.'></div>';  
                            }
                        }
                    }
                }
            }

            if (is_array($this->button) || is_string($this->button))
            {

                if (is_array($this->button))
                {
                    $form .= '<div class="'.$cols.'"> <button name="'.strtolower($this->button[0]).'" type="submit" class="btn mor-btn '.strtolower(@$this->button[1]).'">'.ucfirst($this->button[0]).'</button></div>';
                }
                else
                {
                    $form .= '<div class="'.$cols.'"> <button name="'.strtolower($this->button).'" type="submit" class="btn mor-btn mor-success">'.ucfirst($this->button).'</button></div>';
                }

                $form .= '</form>';

            } 

            $form .= '</div>';

            if ($show === true)
            {
                echo $form;
            }
            else
            {
                return $form;
            }
            
        }
        else
        {
            return false;
        }
    }

    public static function build($template, $build)
    {
        $np = "";

        $max = 0;

        if (is_string($build))
        {
            $dec = json_decode($build);
            if (is_object($dec))
            {
                $build = toArray($dec);
            }
        }

        foreach ($build as $key => $arr)
        {
            if (is_object($arr))
            {
                $arr = toArray($arr);
            }

            if (is_array($arr))
            {
                foreach ($arr as $i => $x)
                {
                    if (preg_match('/^[{]+([^}]+)+[}]$/', $x))
                    {
                        $x = ltrim($x,'{');
                        $x = rtrim($x,'}');

                        if (isset($build[$x]) && isset($build[$x][$i]))
                        {
                            $val = $build[$x][$i];
                            $arr[$i] = $val;
                            $build[$key] = $arr;
                        }
                    }
                }

                if ($max == 0)
                {
                    $max = count($arr);
                }
            }
        }

        $arr = null;
        $key = null;

        if ($max > 0)
        {
            for($i=0; $i<$max;$i++)
            {
                $np .= $template . "\n";

                foreach ($build as $key => $arr)
                {
                    $np = str_replace('{'.$key.'}', $arr[$i], $np);
                }
            }
        }

       return $np;
    }
}
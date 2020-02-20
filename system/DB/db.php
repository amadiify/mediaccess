<?php
/** @noinspection All */

namespace Moorexa;

// Open PDO for use
use Exceptions\Database\DatabaseException;
use PDO;
use Moorexa\DatabaseHandler as handler;
use utility\Classes\BootMgr\Manager as BootMgr;

/**
 * @package Moorexa Database engine
 * @version 0.0.1
 * @author  Ifeanyi Amadi
 * @method static table(string $tablename)
 * @method static serve()
 * @method static apply($database)
 * @method static get(string $string)
 * @method static pdo()
 * @method static sql(string $string)
 */

class DB
{
    public  static $instance = null;
    private static $command = [];
    private        $method = "";
    private static $calls = 0;
    private        $allowed = [];
    private static $call = 0;
    private        $drum = [];
    private        $sub = 0;
    public  static $bindExternal = [];
    public         $cacheQuery = true;

    // pack all errors occured.
    private $errorpack = [];

    // get database driver from database handler
    private $driver;

    // sql query
    private $query = '';

    // pdo bind values
    private $bind = [];

    // operation failed
    private $failed = false;

    // skip checking
    private $skipchecking = false;

    // last where statement
    private $lastWhere = '';

    // database.table
    public $table = '';

    // packed argument
    private static $packed = [];

    // argument sent..
    private static $argument = [];

    // promise instance
    private static $promise = null;

    // query instance
    protected static $queryInstance = null;

    // pdo instance
    public $pdoInstance = null;

    // active connections
    private static $activeConnections = [];

    // set active database
    public static $connectWith = null; // do not make constant

    // set active table
    public static $activeTable = null; // do not make constant

    // current use
    public $useConnection = null;

    // private $began
    private static $began = 0;

    // pause execution
    public $pause = false;

    // allow html tags
    private $allowHTMLTags = false;

    // active connection
    public $instancedb = null;

    // insert keys
    private $insertKeys = '';

    // get query
    private $getSql = '';

    // get binds
    private $getBinds = [];

    // allow query called
    private $allowedQueryCalled = false;

    // transaction success
    public static $transactionCode = 0;

    // opened connection
    private static $openedConnection = [];

    // class using lazy method
    public $classUsingLazy;

    // pause execution
    private $pauseExecution = false;

    // add wherePrefix
    private $wherePrefix = ' ';

    // return promise
    public $returnPromise = false;

    // return new query
    public $returnNewQuery = null;

    // allow slashes
    public $allowSlashes = false;

    // hold prefix
    public static $prefix = null;

    // no prefix ?
    private static $noprefix = false;

    // insert data
    private $argumentPassed = [];

    // get query cache path
    public $queryCachePath = null;

    // cached data array for migration
    private static $cacheQueryData = [];

    // prefixed registered
    private static $prefixRegistered = [];

    // register all prefix event queries
    private static $prefixRegistry = [];

    // allow saving queries
    private $allowSaveQuery = true;

    // last successful query ran
    public static $lastQueryRan = null;

    // list of allowed methods for ORM
    private function getAllowed($val = [null], &$sql = "")
    {
        if (!isset($val[0]))
        {
            $val[0] = '';
        }

        $orginal = $val;

        foreach ($val as $i => $v)
        {
            if (is_callable($v))
            {
                $val[$i] = null;
            }
        }

        $this->allowed = [
            'bind'      => "",
            'min'       => str_replace('SELECT', 'SELECT MIN('.implode(',', $val).')', $sql),
            'max'       => str_replace('SELECT', 'SELECT MAX('.implode(',', $val).')', $sql),
            'count'     => str_replace('SELECT', 'SELECT COUNT('.implode(',', $val).')', $sql),
            'avg'       => str_replace('SELECT', 'SELECT AVG('.implode(',', $val).')', $sql),
            'sum'       => str_replace('SELECT', 'SELECT SUM('.implode(',', $val).')', $sql),
            'distinct'  => str_replace('SELECT', 'SELECT DISTINCT', $sql),
            'rand'      => ' ORDER BY RAND() ',
            'where'     => "",
            'or'        => ' OR '.implode(' OR ', $val).' ',
            'as'        => ' AS '.$val[0].' ',
            'on'        => ' ON '.$val[0].' ',
            'join'      => ' JOIN '.$val[0].' ',
            'innerjoin' => ' INNER JOIN '.$val[0].' ',
            'outerjoin' => ' FULL OUTER JOIN '.$val[0].' ',
            'leftjoin'  => ' LEFT JOIN '.$val[0].' ',
            'rightjoin' => ' RIGHT JOIN '.$val[0].' ',
            'from'      => ' FROM '.$val[0].' ',
            'fromWhere' => ' FROM '.$val[0].' WHERE ' . (isset($val[1]) ? $val[1] : null),
            'in'        => ' IN ('.implode(',', $val).') ',
            'union'     => ' UNION ',
            'into'      => str_replace('FROM', 'INTO '.$val[0].' FROM', $sql),
            'unionall'  => ' UNION ALL ',
            'union'     => ' UNION ',
            'and'       => ' AND '.implode(' AND ', $val).' ',
            'group'     => ' GROUP BY '.implode(',', $val).' ',
            'having'    => ' HAVING '.$val[0].' ',
            'exists'    => ' EXISTS ('.$val[0].') ',
            'any'       => ' ANY ('.$val[0].') ',
            'all'       => ' ALL ('.$val[0].') ',
            'not'       => ' NOT '.implode(' NOT ', $val).' ',
            'notin'     => ' NOT IN ('.implode(',', $val).') ',
            'between'   => ' BETWEEN '.implode(' AND ', $val).' ',
            'limit'     => ' LIMIT '.implode(',', $val).' ',
            'orderby'   => ' ORDER BY '.implode(' ', $val).' ',
            'sql'       => " ". (isset($val[0]) ? $val[0] : ''),
            'get'       => '',
            'insert'    => '',
            'update'    => '',
            'delete'    => '',
            'if'        => function() use ($orginal)
            {
                if ($orginal[0] === true && is_callable($orginal[1]))
                {
                    $callback = $orginal[1];
                    $current = &$this;
                    call_user_func($callback, $current);
                }
            },
            'like'      => function($obj, $m="and") use ($val, $sql){

                $a =& $val;
                $structure = $sql;

                $line = $obj->__stringBind($a[0], ' LIKE ', '');

                $where = $line['line'];
                $bind = $line['bind'];


                if (preg_match('/({where})/', $structure))
                {
                    $structure = str_replace('{where}', 'WHERE '.$where.' ', $structure);
                    $obj->query = $structure;
                    $obj->lastWhere = 'WHERE '.$where.' ';
                }
                else
                {
                    $obj->query = trim($obj->query) .' '.$m.' '. $where;
                    $w = substr($obj->query, strpos($obj->query, 'WHERE'));
                    $w = substr($w, 0, strrpos($w, $where)) . $where;
                    $obj->lastWhere = $w;
                }

                array_shift($a);

                $obj->__addBind($a, $bind, null);

                $newBind = [];

                // avoid clashes
                $obj->__avoidClashes($bind, $newBind);

                $obj->bind = array_merge($obj->bind, $newBind);
            },
        ];

        $more = [
            'orLike' => function($obj) use ($val, $sql)
            {
                $logic = 'and';

                if (strpos($sql, 'LIKE') !== false)
                {
                    $logic = 'or';
                }

                return call_user_func($this->allowed['like'], $obj, $logic);
            }
        ];

        $this->allowed = array_merge($this->allowed, $more);

        return $this->allowed;
    }

    // queries by drivers
    private function drivers($driver = null)
    {
        // supported drivers.
        $queries = [
            // mysql queries..
            'mysql' => [
                'update' => 'UPDATE {table} SET {query} {where}',
                'insert' => 'INSERT INTO {table} ({column}) VALUES {query}',
                'delete' => 'DELETE FROM {table} {where}',
                'select' => 'SELECT {column} FROM {table} {where}'
            ],
            // pgsql queries..
            'pgsql' => [
                'update' => 'UPDATE ONLY {table} SET {query} {where}',
                'insert' => 'INSERT INTO {table} ({column}) VALUES {query}',
                'delete' => 'DELETE FROM {table} {where}',
                'select' => 'SELECT {column} FROM {table} {where}'
            ],
            // sqlite queries..
            'sqlite' => [
                'update' => 'UPDATE {table} SET {query} {where}',
                'insert' => 'INSERT INTO {table} ({column}) VALUES {query}',
                'delete' => 'DELETE FROM {table} {where}',
                'select' => 'SELECT {column} FROM {table} {where}'
            ]
        ];

        if (!is_null($driver))
        {
            return isset($queries[$driver]) ? $queries[$driver] : null;
        }

        return isset($queries[$this->driver]) ? $queries[$this->driver] : null;
    }

    // get rows
    public static function getRows($arguments, $attrline)
    {
        $build = '<?php'."\n";

        // get arguments
        $table = array_shift($arguments);
        $var = array_pop($arguments);

        $sql = isset($arguments[0]) ? $arguments[0] : null;

        $tablefetch = "\Moorexa\DB::table('".$table."');";

        if (!is_null($tablefetch))
        {
            $tablefetch = "\Moorexa\DB::sql('SELECT * FROM '.(\Moorexa\DB::getTableName('".$table."')).' $sql');";
        }

        // fetch for all records
        $build .= "\$$table = $tablefetch";

        // check if we have rows
        $build .= "if (\$$table) { echo \$$table".'->rows; }';
        $build .= "?>";

        return $build;
    }

    // end get rows
    public static function fetchRowsEnd()
    {
        $end = "<?php }} ?>";

        // end while, if
        return $end;
    }

    // add static bind
    private static function __bind(&$obj, &$a)
    {
        if (count($obj->bind) > 0)
        {
            $__bind = [];

            foreach ($obj->bind as $key => $val)
            {
                if (empty($val))
                {
                    $__bind[$key] = '';
                }
            }

            if (count($__bind) > 0)
            {
                $i = 0;

                foreach ($__bind as $key => $val)
                {
                    if (isset($a[$i]) && is_string($a[$i]))
                    {
                        $obj->bind[$key] = addslashes(strip_tags($a[$i]));
                    }
                    elseif (isset($a[$i]) && (is_object($a[$i]) || is_array($a[$i])))
                    {
                        $command = end(db::$command);

                        if (isset($obj->errorpack[$command]) && is_array($obj->errorpack[$command]))
                        {
                            $obj->errorpack[$command][] = 'Invalid Bind parameter. Scaler Type expected, Compound Type passed.';
                        }
                        else
                        {
                            $obj->errorpack[$command] = [];
                            $obj->errorpack[$command][] = 'Invalid Bind parameter. Scaler Type expected, Compound Type passed.';
                        }
                    }
                    else
                    {
                        $obj->bind[$key] = isset($a[$i]) ? $a[$i] : '';
                    }

                    $i++;
                }

            }
        }
    }

    // get rows
    public static function fetchRows($arguments, $attrline)
    {
        $build = '<?php'."\n";

        // get arguments
        $table = array_shift($arguments);
        $var = array_pop($arguments);

        $sql = isset($arguments[0]) ? $arguments[0] : null;

        $other = '';
        $binds = '';

        // get table namee
        $tableInfo = explode(' as ', $table);

        list($table, $var) = $tableInfo;

        // $tableName = self::getTableName($table);
        $resetdb = false;

        if ($table[0] != '$')
        {
            $tablefetch = "\Moorexa\DB::table('".$table."');";

            if (!is_null($tablefetch))
            {
                $tablefetch = "\Moorexa\DB::sql('SELECT * FROM '.(\Moorexa\DB::getTableName('".$table."')).' $sql');";
            }
        }
        else
        {
            $tablefetch = $table .';';
            $table = substr($table, 1);
            $resetdb = true;
        }

        // fetch for all records
        $build .= "\$$table = $tablefetch";

        // check if we have rows
        $build .= "if (\$$table".'->rows > 0)';
        $build .= "{\n";
        if ($resetdb)
        {
            $build .= "\$$table".'->reset();';
        }
        $build .= "while (\$$var = \$$table".'->obj())'."\n";
        $build .= "{ ?>";

        return $build;
    }

    // get table name
    public static function getTableName(string $table)
    {
        $prefix = handler::$prefix;

        if ($prefix != '')
        {
            // check if prefix exists in table name
            $quote = preg_quote($prefix);

            if (preg_match("/($quote)/", $table))
            {
                // stop here.
                return $table;
            }
        }

        // extend checking
        $prefix = self::getPrefix();
        $quote = preg_quote($prefix);

        if (preg_match("/($quote)/", $table) == false)
        {
            if (count(self::$prefixRegistered) > 0)
            {
                foreach (self::$prefixRegistered as $prefixRegistered)
                {
                    $quote = preg_quote($prefixRegistered);

                    if (preg_match("/($quote)/", $table))
                    {
                        return $table;
                    }
                }
            }

            return $prefix . $table;
        }

        return $table;
    }

    // array as argument
    private function __arrayBind($data, $seperator = ',')
    {
        $set = '';
        $bind = [];

        foreach ($data as $key => $val)
        {
            if (!is_array($val) && !is_object($val))
            {
                $set .= $key.' = :'.$key.' '.$seperator.' ';
                $val = html_entity_decode($val);

                if (!$this->allowSlashes)
                {
                    $val = addslashes(stripslashes($val));
                }

                $bind[$key] = $val;
            }
        }

        $sep = strrpos($set, $seperator);
        $set = substr($set, 0, $sep);

        return ['set' => $set, 'bind' => $bind];
    }

    // array bind for insert
    private function __arrayInsertBody($array, $structure)
    {
        static $x = 0;
        // values
        $values = [];
        $binds = [];

        foreach ($array as $i => $data)
        {
            if (is_object($data))
            {
                $data = toArray($data);
            }

            if (is_array($data))
            {
                $xx = 0;
                $value = [];

                foreach ($data as $key => $val)
                {
                    $hkey = trim($structure[$xx]);
                    $value[] = ':'.$hkey.$x;
                    $d = isset($data[$hkey]) ? $data[$hkey] : (isset($data[$xx]) ? $data[$xx] : null);

                    if (is_string($d))
                    {
                        $d = html_entity_decode($d);

                        if (!$this->allowSlashes)
                        {
                            $d = addslashes(stripslashes($d));
                        }
                    }

                    $binds[$hkey.$x] = $d;
                    $xx++;
                }

                $values[] = '('.implode(',', $value).')';
                $x++;
            }
        }

        $x = 0;

        return ['values' => implode(',', $values), 'bind' => $binds];
    }

    private function __arrayInsertHeader($array)
    {
        $header = [];

        if (isset($array[0]) && is_array($array[0]))
        {
            foreach ($array[0] as $key => $val)
            {
                if (is_string($key))
                {
                    $header[] = $key;
                }
                else
                {
                    $header[] = $val;
                }
            }
        }

        return ['header' => implode(',', $header), 'structure' => $header];
    }

    // string insert bind
    private function __stringInsertBind($data)
    {
        // get all strings
        preg_match_all('/[\'|"]([\s\S])[\'|"|\S]{1,}[\'|"]/',$data, $match);

        $strings = [];
        if (count($match[0]) > 0)
        {
            foreach($match[0] as $i => $string)
            {
                $strings[] = $string;
                $data = str_replace($string, ':string'.$i, $data);
            }
        }

        // now split by comma
        $split = explode(',',$data);

        // replace strings now with original values.
        if (count($strings) > 0)
        {
            foreach($strings as $i => $string)
            {
                $split[$i] = str_replace(':string'.$i, $string, $split[$i]);
            }
        }

        $bind = [];
        $header = [];
        $values = [];

        // check if we don't have lvalue and rvalue
        static $xc = 0;

        foreach($split as $i => $line)
        {
            $line = trim($line);
            if (preg_match('/[=]/', $line))
            {
                // get rvalue
                $eq = strpos($line, '=');
                $rval = trim(substr($line, $eq+1));

                // get lvalue
                $lval = trim(substr($line, 0, $eq));

                if (!in_array($lval, $header))
                {
                    $header[] = $lval;
                }

                if ($rval == '?')
                {
                    $values[] = ':'.$lval.$xc;

                    $rval = ':'.$lval;
                    $bind[$lval.$xc] = '';
                }
                elseif ($rval[0] == ':')
                {
                    $values[] = $rval.$xc;
                    $bind[$rval.$xc] = '';
                }
                else
                {
                    // has values
                    $start = $rval[0];
                    if (preg_match("/[a-zA-Z0-9|'|\"]/", $start))
                    {
                        if ($start == '"')
                        {
                            $bf = $rval;
                            $end = strrpos($rval, '"');
                            $rval = substr($rval, 0, $end);
                            $line = str_replace($bf, $rval, $line);
                            $split[$i] = $line;
                        }
                        elseif ($start == "'")
                        {
                            $bf = $rval;
                            $end = strrpos($rval, "'");
                            $rval = substr($rval, 0, $end);
                            $line = str_replace($bf, $rval, $line);
                            $split[$i] = $line;
                        }
                        elseif (preg_match('/^[0-9]/', $start))
                        {
                            $bf = $rval;
                            $end = strpos($rval,' ');
                            if ($end !== false)
                            {
                                $rval = substr($rval, 0, $end);
                                $line = str_replace($bf, $rval, $line);
                                $split[$i] = $line;
                            }
                        }
                    }

                    $rval = preg_replace('/^[\'|"]/','',$rval);
                    $rval = preg_replace('/[\'|"]$/','',$rval);
                    $rval = html_entity_decode($rval);

                    if (!$this->allowSlashes)
                    {
                        $rval = addslashes(stripslashes($rval));
                    }

                    $values[] = ':'.$lval.$xc;
                    $bind[$lval.$xc] = $rval;
                }
            }

            $xc++;
        }

        $xc = 0;

        return ['values' => $values, 'bind' => $bind, 'header' => $header];
    }

    // string as argument
    private function __stringBind($data, $l = null, $r = null)
    {
        // get all strings
        preg_match_all('/[\'|"]([\s\S])[\'|"|\S]{0,}[\'|"]/',$data, $match);

        $strings = [];
        if (count($match[0]) > 0)
        {
            foreach($match[0] as $i => $string)
            {
                $strings[] = $string;
                $data = str_replace($string, ':string'.$i, $data);
            }
        }

        // now split by comma, or, and
        $split = preg_split('/(\s{1,}or\s{1,}|\s{1,}OR\s{1,}|\s{1,}and\s{1,}|[,]|\s{1,}AND\s{1,})/', $data);

        // watch out for other valid sql keywords.
        foreach($split as $i => $ln)
        {
            $ln = trim($ln);
            if (!preg_match('/[=]/',$ln) || preg_match('/^[0-9]/',$ln))
            {
                if (isset($split[$i-1]))
                {
                    if (stripos($split[$i-1], 'limit'))
                    {
                        $split[$i-1] .= ','.$ln;
                        unset($split[$i]);
                        sort($split);
                    }
                }
            }
        }

        // replace strings now with original values.
        if (count($strings) > 0)
        {
            foreach($strings as $i => $string)
            {
                $split[$i] = str_replace(':string'.$i, $string, $split[$i]);
                $data = str_replace(':string'.$i, $string,  $data);
            }
        }

        $bind = [];
        $newSplit = [];

        // check if we don't have lvalue and rvalue
        static $xy = 0;

        foreach($split as $i => $line)
        {
            $line = trim($line);
            if (!preg_match('/(=|!=|>|<|>=|<=)/', $line))
            {
                $query = implode(',', $newSplit);
                $__key = $line;

                if (preg_match("/[:]($line)/", $this->query) || preg_match("/[:]($line)/", $query))
                {
                    $line .= $xy;
                    $bind[$line] = '';

                    $xy++;

                }
                else
                {
                    $bind[$__key] = '';
                }

                $l = is_null($l) ? ' = ' : $l;
                $r = is_null($r) ? '' : $r;

                $new = $__key . $l . ':'.$line. $r;
                $line = $new;
            }
            else
            {
                // get rvalue
                $eq = strpos($line, '=');
                $sep = '=';

                if ($eq===false)
                {
                    if (preg_match('/(!=)/',$line))
                    {
                        $eq = strpos($line, '!=');
                        $sep = '!=';
                    }
                }

                if ($eq===false)
                {
                    if (preg_match('/(>)/',$line))
                    {
                        $eq = strpos($line, '>');
                        $sep = '>';
                    }
                }

                if ($eq===false)
                {
                    if (preg_match('/(<)/',$line))
                    {
                        $eq = strpos($line, '<');
                        $sep = '<';
                    }
                }

                if ($eq===false)
                {
                    if (preg_match('/(>=)/',$line))
                    {
                        $eq = strpos($line, '>=');
                        $sep = '>=';
                    }
                }

                if ($eq===false)
                {
                    if (preg_match('/(<=)/',$line))
                    {
                        $eq = strpos($line, '<=');
                        $sep = '<=';
                    }
                }

                $rval = trim(substr($line, $eq+intval(strlen($sep))));

                // get lvalue
                $lval = trim(substr($line, 0, $eq));
                $lval = trim(preg_replace('/[!|=|<|>]$/','',$lval));


                if ($rval == '?')
                {
                    static $xx = 0;

                    $query = implode(',', $newSplit);

                    if (preg_match("/[:]($lval)/", $this->query) || preg_match("/[:]($lval)/", $query))
                    {
                        $lval .= $xx;
                        $xx++;
                    }

                    $rval = ':'.$lval;
                    $line = str_replace('?', $rval, $line);
                    $bind[$lval] = '';
                }
                elseif ($rval[0] == ':')
                {
                    static $xx = 0;

                    $query = implode(',', $split);

                    if (preg_match("/($rval)/", $this->query) || preg_match("/[:]($lval)/", $query))
                    {
                        $bind[substr($rval,1).$xx] = '';
                        $xx++;
                    }
                    else
                    {
                        $bind[substr($rval,1)] = '';
                    }

                }
                else
                {
                    static $xx = 0;

                    // has values
                    $start = $rval[0];
                    if (preg_match("/[a-zA-Z0-9|'|\"]/", $start))
                    {
                        if ($start == '"')
                        {
                            $bf = $rval;
                            $end = strrpos($rval, '"');
                            $rval = substr($rval, 0, $end+1);
                            $line = str_replace($bf, $rval, $line);
                            $split[$i] = $line;
                        }
                        elseif ($start == "'")
                        {
                            $bf = $rval;
                            $end = strrpos($rval, "'");
                            $rval = substr($rval, 0, $end+1);
                            $line = str_replace($bf, $rval, $line);
                            $split[$i] = $line;
                        }
                        elseif (preg_match('/^[0-9]/', $start))
                        {
                            $bf = $rval;
                            $end = strpos($rval,' ');
                            if ($end !== false)
                            {
                                $rval = substr($rval, 0, $end);
                                $line = str_replace($bf, $rval, $line);
                                $split[$i] = $line;
                            }
                        }
                    }

                    $rval = preg_replace('/^[\'|"]/','',$rval);
                    $rval = preg_replace('/[\'|"]$/','',$rval);
                    $rval = html_entity_decode($rval);
                    $rval = addslashes(strip_tags($rval));


                    $query = implode(', ', $newSplit);

                    if (preg_match("/[:]($lval)/", $this->query) || preg_match("/[:]($lval)/", $query))
                    {
                        $line = $lval .' '.$sep.' :'.$lval.$xx;
                        $bind[$lval.$xx] = $rval;

                        $xx++;
                    }
                    else
                    {
                        $line = $lval .' '.$sep.' :'.$lval;
                        $bind[$lval] = $rval;
                    }
                }
            }

            $newSplit[] = $line;
        }

        $xy = 0;

        if (is_string($data))
        {
            $originalData = [];

            foreach ($split as $i => $line)
            {
                $q = preg_quote($line);
                $beg = strpos($data, $line);
                $sized = strlen($line);

                $with = "{".$beg.$sized.substr(md5($line),0,mt_rand(10,40))."}";
                $originalData[$with] = $newSplit[$i];

                $data = substr_replace($data, $with, $beg, $sized);
                unset($split[$i]);
            }

            foreach($originalData as $map => $val)
            {
                $data = str_replace($map, $val, $data);
            }
        }
        else
        {
            $this->failed = true;
            $this->errorpack[$this->method][] = 'Empty string passed';
        }

        return ['line' => $data, 'bind' => $bind];
    }

    // add binds silently
    private function __addBind(&$a, &$bind)
    {
        if (count($a) > 0)
        {
            $i = 0;
            foreach ($bind as $x => $b)
            {
                if (empty($b) && isset($a[$i]))
                {
                    if (is_string($a[$i]))
                    {
                        $a[$i] = html_entity_decode($a[$i]);

                        if (!$this->allowSlashes)
                        {
                            $a[$i] = addslashes(stripslashes($a[$i]));
                        }

                        $bind[$x] = $a[$i];
                    }
                    else
                    {
                        $bind[$x] = $a[$i];
                    }
                    unset($a[$i]);
                }
                $i++;
            }
        }
    }

    // avoid clashes
    private function __avoidClashes(&$bind, &$newBind)
    {
        $currentBind = $this->bind;

        static $i = 0;
        $added = false;
        $ret = 0;

        foreach($bind as $key => $val)
        {
            // avoid name clashes..
            if (isset($currentBind[$key]))
            {
                if (empty($currentBind[$key]))
                {
                    if (is_string($val))
                    {
                        $val = html_entity_decode($val);

                        if (!$this->allowSlashes)
                        {
                            $val = addslashes(stripslashes($val));
                        }

                        $newBind[$key] = $val;
                    }
                    else
                    {
                        $newBind[$key] = $val;
                    }
                }
                else
                {

                    $ret = $i;
                    if (is_string($val))
                    {
                        $val = html_entity_decode($val);

                        if (!$this->allowSlashes)
                        {
                            $val = addslashes(stripslashes($val));
                        }

                        $newBind[$key.$i] = $val;
                    }
                    else
                    {
                        $newBind[$key.$i] = $val;
                    }
                    $i++;
                    $added = true;
                }
            }
            else
            {
                if (is_string($val))
                {
                    $val = html_entity_decode($val);

                    if (!$this->allowSlashes)
                    {
                        $val = addslashes(stripslashes($val));
                    }

                    $newBind[$key] = $val;
                }
                else
                {
                    $newBind[$key] = $val;
                }
            }
        }

        if ($added)
        {
            return $ret;
        }

        return '';

    }

    // set active table
    public function setActiveTable($table)
    {
        self::$activeTable = $table;
        $this->table = $table;
    }

    // set connect with
    public function setConnectWith($with)
    {
        self::$connectWith = $with;
    }

    public function _apply($dataName = null)
    {
        if (!is_null($dataName) && !empty($dataName))
        {
            if (isset(self::$openedConnection[$dataName]))
            {
                $con = self::$openedConnection[$dataName];
                $con->table = !is_null($this->table) ? $this->table : $con->table;
                $con->bind = [];
                $con->query = '';
                $con->allowed = $this->getAllowed();

                return $con;
            }
            // switch connection
            else
            {
                $driver = DatabaseHandler::connectionConfig($dataName, 'driver');
                // get allowed
                $this->getAllowed();

                if (DatabaseHandler::connectionConfig($dataName) !== false)
                {
                    DatabaseHandler::$dbset = true;
                }
                else
                {
                    Event::emit("database.error", "Database config not found for '{$dataName}'");
                }

                if (DatabaseHandler::$dbset === true)
                {
                    if ($this->drivers($driver) !== null)
                    {
                        $this->useConnection = $dataName;

                        // save driver
                        $this->driver = $driver;

                        $this->instancedb = $dataName;

                        // extablish connection
                        $con = DatabaseHandler::active($dataName);

                        // save instance.
                        $this->pdoInstance = $con;

                        // push connection
                        self::$openedConnection[$dataName] = $this;
                    }
                    else
                    {
                        throw new \Exceptions\Database\DatabaseException('Driver you used isn\'t supported on this server. Please see documentation');
                    }
                }
            }
        }

        return $this;
    }

    // get table info
    public function _getTableInfo($instance = null, $type = null, $table = null)
    {
        $ins = $this;

        if (is_object($instance))
        {
            $ins = $instance;
        }

        $server = !is_null($ins->driver) ? $ins->driver : $this->driver;
        $tableName = !is_null($ins->table) ? $ins->table : $this->table;

        if (is_object($instance) && is_string($type))
        {
            $server = $type;
        }

        if (is_string($instance))
        {
            $server = $instance;
        }

        if (!is_null($table))
        {
            $tableName = $table;
        }

        if (is_string($instance) && is_string($type))
        {
            $tableName = $type;
        }

        $query = [
            'sqlite' => "SELECT sql FROM sqlite_master WHERE name = '{$tableName}'",
            'pgsql' => "SELECT COLUMN_NAME,COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME = '{$tableName}'",
            'mysql' => "SELECT COLUMN_NAME,COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME = '{$tableName}'",
        ];

        $structure = [];

        if (isset($query[$server]))
        {

            $run = $ins->sql($query[$server]);

            if ($run->rows > 0)
            {
                switch($server)
                {
                    case 'mysql':
                    case 'pgsql':
                        $run->array(function($row) use (&$structure)
                        {
                            if (isset($row['COLUMN_TYPE']))
                            {
                                // get size
                                $type = $row['COLUMN_TYPE'];
                                $size = preg_replace("/([^\d]*)/",'',$type);
                                $name = preg_replace("/([^a-zA-Z_]*)/",'', $type);
                                $structure[$row['COLUMN_NAME']] = ['size' => $size, 'type' => $name];
                            }
                        });
                        break;

                    case 'sqlite':
                        break;
                }
            }

        }

        return $structure;
    }

    // get request
    private function ___get($a, $structure, $table)
    {
        // set method
        $this->method = 'get';

        // run callback
        $runCallback = null;

        // check $a for any callback
        if (count($a) > 0)
        {
            foreach ($a as $index => $aData)
            {
                if ($aData !== null && is_callable($aData))
                {
                    $runCallback = $aData;
                    unset($a[$index]);
                }
            }
        }

        if (count($a) > 0)
        {
            // data passed
            $data = $a[0];

            // get rulesdata for object passed.
            if (is_object($data))
            {
                if (method_exists($data, 'rulesHasData'))
                {
                    $data = $data->rulesHasData();
                    // array shift
                    $a[0] = $data;
                }
            }

            // is object?
            if (is_object($data))
            {
                // convert to array
                $data = toArray($data);
            }

            // json data?
            if (is_string($data) && trim($data[0]) == '{' )
            {
                // conver to an object
                $data = toArray(json_decode($data));
            }

            if (is_array($data))
            {
                $cond = 'AND';

                if (isset($a[1]) && $a[1] == 'OR')
                {
                    $cond = 'OR';

                    unset($a[1]);
                }

                $arrayBind = $this->__arrayBind($data, $cond);

                $structure = str_replace('{column}', '*', $structure);
                $structure = str_replace('{where}', 'WHERE '.$arrayBind['set'].' ', $structure);

                $this->query = $structure;
                $this->bind = $arrayBind['bind'];

                array_shift($a);
            }
            else
            {

                if (preg_match('/(=|!=|>|<|>=|<=)/', $data))
                {
                    $dl = $this->__stringBind($data);
                    $bind = $dl['bind'];

                    array_shift($a);

                    $this->__addBind($a, $bind);

                    // sort($a);
                    $structure = str_replace('{where}', 'WHERE '.$dl['line'].' ', $structure);
                    $structure = str_replace('{column}', '*', $structure);

                    $this->query = $structure;
                    $this->bind = $bind;
                }
                else
                {
                    $continue = false;

                    if (preg_match('/[,]/', $data) || (isset($a[1]) && preg_match('/[=]/', $data)) || !isset($a[1]))
                    {
                        $continue = true;
                    }
                    else
                    {
                        if (!isset($a[1]))
                        {
                            $continue = true;
                        }
                        else
                        {
                            $dl = $this->__stringBind($data);
                            $bind = $dl['bind'];

                            array_shift($a);

                            $this->__addBind($a, $bind);

                            $structure = str_replace('{where}', 'WHERE '.$dl['line'].' ', $structure);
                            $structure = str_replace('{column}', '*', $structure);

                            $this->query = $structure;
                            $this->bind = $bind;
                        }
                    }


                    if ($continue)
                    {
                        $structure = str_replace('{column}', $data, $structure);
                        array_shift($a);

                        if (count($a) > 0)
                        {
                            $data = $a[0];

                            // is object?
                            if (is_object($data))
                            {
                                // convert to array
                                $data = toArray($data);
                            }

                            // json data?
                            if (is_string($data) && trim($data[0]) == '{' )
                            {
                                // conver to an object
                                $data = toArray(json_decode($data));
                            }

                            if (is_array($data))
                            {
                                $cond = 'AND';

                                if (isset($a[1]) && $a[1] == 'OR')
                                {
                                    $cond = 'OR';

                                    unset($a[1]);
                                }

                                $arrayBind = $this->__arrayBind($data, $cond);

                                $structure = str_replace('{column}', '*', $structure);
                                $structure = str_replace('{where}', 'WHERE '.$arrayBind['set'].' ', $structure);

                                $this->query = $structure;
                                $this->bind = $arrayBind['bind'];
                            }
                            else
                            {
                                $dl = $this->__stringBind($data);
                                $bind = $dl['bind'];

                                array_shift($a);

                                $this->__addBind($a, $bind);

                                $structure = str_replace('{where}', 'WHERE '.$dl['line'].' ', $structure);
                                $structure = str_replace('{column}', '*', $structure);

                                $this->query = $structure;
                                $this->bind = $bind;
                            }
                        }
                    }

                    $this->query = $structure;
                }
            }
        }
        else
        {
            $structure = str_replace('{column}', '*', $structure);

            $this->query = $structure;
        }

        if (!is_null($runCallback))
        {
            $run = $this->go();
            call_user_func($runCallback, $run);
        }

        return $this;
    }

    // insert request
    private function ___insert($a, $structure, $table)
    {
        $instance = &$this;

        // set method
        $instance->method = 'insert';

        // run callback
        $runCallback = null;

        // check $a for any callback
        if (count($a) > 0)
        {
            foreach ($a as $index => $aData)
            {
                if ($aData !== null && is_callable($aData))
                {
                    $runCallback = $aData;
                    unset($a[$index]);
                }
            }
        }

        // push insert data
        $this->argumentPassed = $a;

        if (count($a) > 0)
        {
            // check if args 2 is string and possibly an object
            if (isset($a[1]) && is_string($a[1]))
            {
                $object = json_decode($a[1]);
                $copy = $a;
                // build new args
                $newArgs = [];

                if (is_string($a[0]) && is_object($object))
                {
                    $columns = explode(',',$a[0]);

                    foreach ($object as $key => $val)
                    {
                        $row = [];

                        $row[trim($columns[0])] = $key;
                        $row[trim($columns[1])] = $val;

                        $newArgs[] = $row;
                    }

                    if (count($newArgs) > 0)
                    {
                        $a = $newArgs;
                    }
                }
            }

            // data passed
            $data = $a[0];

            // get rulesdata for object passed.
            if (is_object($data))
            {
                if (method_exists($data, 'rulesHasData'))
                {
                    $data = $data->rulesHasData();
                    // array shift
                    $a[0] = $data;
                }
            }

            // is object?
            if (is_object($data))
            {
                // convert to array
                $data = toArray($data);
            }

            // json data?
            if (is_string($data) && trim($data[0]) == '{' )
            {
                // convert to an object
                $data = toArray(json_decode($data));
                $a[0] = $data;
            }

            if (is_array($data))
            {
                $getHeader = $instance->__arrayInsertHeader($a);

                $header = $getHeader['header'];
                $struct = $getHeader['structure'];

                $instance->insertKeys = $header;

                $structure = str_replace('{column}', $header, $structure);

                $data = $instance->__arrayInsertBody($a, $struct);
                $bind = $data['bind'];
                $values = $data['values'];

                $structure = str_replace('{query}', $values, $structure);

                $instance->query = $structure;
                $instance->bind = $bind;
            }
            else
            {
                // string
                // no equal ?
                if (strpos($data,'=') === false)
                {
                    $struct = explode(',', $data);
                    $structure = str_replace('{column}', $data, $structure);

                    $instance->insertKeys = $data;

                    array_shift($a);

                    // data passed
                    if (isset($a[0]))
                    {
                        $data = $a[0];

                        // is object?
                        if (is_object($data))
                        {
                            // convert to array
                            $data = toArray($data);
                        }

                        // json data?
                        if (is_string($data) && trim($data[0]) == '{' )
                        {
                            // convert to an object
                            $data = toArray(json_decode($data));
                        }

                        $continue = true;


                        if (is_array($data))
                        {
                            if (count($a) != 1)
                            {
                                $continue = false;

                                $data = $instance->__arrayInsertBody($a, $struct);
                                $bind = $data['bind'];
                                $values = $data['values'];

                                $structure = str_replace('{query}', $values, $structure);
                                $instance->query = $structure;
                                $instance->bind = $bind;
                            }
                            else
                            {
                                $a = $data;
                            }
                        }
                        else
                        {
                            if (count($a) > 0)
                            {
                                $continue = true;
                            }
                        }


                        if ($continue)
                        {
                            static $x = 0;

                            $values = [];
                            $binds = [];
                            $len = count($struct)-1;
                            $y = 0;

                            if (count($struct) > count($a))
                            {
                                foreach ($struct as $i => $h)
                                {
                                    if (!isset($a[$i]))
                                    {
                                        $a[$i] = null;
                                    }
                                }
                            }

                            $len--;
                            $value = [];

                            foreach ($a as $i => $val)
                            {
                                $struct[$y] = trim($struct[$y]);
                                $value[$y] = ':'.$struct[$y].$x;
                                $binds[$struct[$y].$x] = addslashes(htmlentities($val, ENT_QUOTES, 'UTF-8'));

                                if ($y == count($struct)-1 || $y == count($a)-1)
                                {
                                    $y = 0;
                                    $values[] = '('.implode(',', $value).')';
                                }
                                else
                                {
                                    $y++;
                                }

                                $x++;
                            }

                            $x = 0;

                            $structure = str_replace('{query}',implode(',', $values),$structure);
                            $instance->query = $structure;
                            $instance->bind = $binds;
                        }

                    }
                    else
                    {
                        static $x = 0;

                        $values = [];
                        $binds = [];
                        $len = count($struct)-1;
                        $y = 0;

                        if (count($struct) > count($a))
                        {
                            foreach ($struct as $i => $h)
                            {
                                if (!isset($a[$i]))
                                {
                                    $a[$i] = null;
                                }
                            }
                        }

                        $len--;
                        $value = [];

                        foreach ($a as $i => $val)
                        {
                            $struct[$y] = trim($struct[$y]);
                            $value[$y] = ':'.$struct[$y].$x;
                            $binds[$struct[$y].$x] = addslashes(htmlentities($val, ENT_QUOTES, 'UTF-8'));

                            if ($y == count($struct)-1 || $y == count($a)-1)
                            {
                                $y = 0;
                                $values[] = '('.implode(',', $value).')';
                            }
                            else
                            {
                                $y++;
                            }

                            $x++;
                        }


                        $x = 0;

                        $structure = str_replace('{query}',implode(',', $values),$structure);
                        $instance->query = $structure;
                        $instance->bind = $binds;
                    }
                }
                // has equal
                else
                {
                    $data = $instance->__stringInsertBind($data);
                    $structure = str_replace('{column}', implode(',', $data['header']), $structure);
                    $structure = str_replace('{query}', '('.implode(',', $data['values']).')', $structure);

                    $bind = $data['bind'];
                    $instance->insertKeys = $data['header'];

                    array_shift($a);

                    $instance->__addBind($a, $bind);

                    $instance->bind = $bind;
                    $instance->query = $structure;
                }
            }
        }
        else
        {
            $instance->errorpack['insert'][] = 'No data to insert. You can pass compound data types.';
        }

        if (!is_null($runCallback))
        {
            $run = $this->go();
            call_user_func($runCallback, $run);
        }

        return $instance;
    }

    // delete request
    private function ___delete($a, $structure, $table)
    {
        $instance = &$this;

        // set method
        $instance->method = 'delete';

        // run callback
        $runCallback = null;

        // check $a for any callback
        if (count($a) > 0)
        {
            foreach ($a as $index => $aData)
            {
                if ($aData !== null && is_callable($aData))
                {
                    $runCallback = $aData;
                    unset($a[$index]);
                }
            }
        }

        if (count($a) > 0)
        {
            // data passed
            $data = $a[0];

            // get rulesdata for object passed.
            if (is_object($data))
            {
                if (method_exists($data, 'rulesHasData'))
                {
                    $data = $data->rulesHasData();
                    // array shift
                    $a[0] = $data;
                }
            }

            // is object?
            if (is_object($data))
            {
                // convert to array
                $data = toArray($data);
            }

            // json data?
            if (is_string($data) && trim($data[0]) == '{' )
            {
                // conver to an object
                $data = toArray(json_decode($data));
            }

            if (is_array($data))
            {
                $arrayBind = $instance->__arrayBind($data, 'OR');

                $structure = str_replace('{where}', 'WHERE '.$arrayBind['set'].' ', $structure);

                $instance->query = $structure;
                $instance->bind = $arrayBind['bind'];

                array_shift($a);
            }
            else
            {
                if (preg_match('/(=|!=|>|<|>=|<=)/', $data))
                {
                    $dl = $instance->__stringBind($data);
                    $bind = $dl['bind'];

                    array_shift($a);

                    $instance->__addBind($a, $bind);

                    $structure = str_replace('{where}', 'WHERE '.$dl['line'].' ', $structure);

                    $instance->query = $structure;
                    $instance->bind = $bind;
                }
                else
                {
                    $continue = false;

                    if (preg_match('/[,]/', $data) || (isset($a[1]) && preg_match('/[=]/', $data)) || !isset($a[1]))
                    {
                        $continue = true;
                    }
                    else
                    {
                        if (!isset($a[1]))
                        {
                            $continue = true;
                        }
                        else
                        {
                            $dl = $instance->__stringBind($data);
                            $bind = $dl['bind'];

                            array_shift($a);

                            $instance->__addBind($a, $bind);

                            $structure = str_replace('{where}', 'WHERE '.$dl['line'].' ', $structure);

                            $instance->query = $structure;
                            $instance->bind = $bind;
                        }
                    }

                    if ($continue)
                    {
                        array_shift($a);

                        if (count($a) > 0)
                        {
                            $data = $a[0];

                            // is object?
                            if (is_object($data))
                            {
                                // convert to array
                                $data = toArray($data);
                            }

                            // json data?
                            if (is_string($data) && trim($data[0]) == '{' )
                            {
                                // conver to an object
                                $data = toArray(json_decode($data));
                            }

                            if (is_array($data))
                            {
                                $arrayBind = $instance->__arrayBind($data, 'OR');

                                $structure = str_replace('{where}', 'WHERE '.$arrayBind['set'].' ', $structure);

                                $instance->query = $structure;
                                $instance->bind = $arrayBind['bind'];
                            }
                            else
                            {
                                $dl = $instance->__stringBind($data);
                                $bind = $dl['bind'];

                                array_shift($a);

                                $instance->__addBind($a, $bind);

                                $structure = str_replace('{where}', 'WHERE '.$dl['line'].' ', $structure);

                                $instance->query = $structure;
                                $instance->bind = $bind;
                            }
                        }
                        else
                        {
                            $dl = $instance->__stringBind($data);
                            $bind = $dl['bind'];

                            array_shift($a);

                            $instance->__addBind($a, $bind);

                            $structure = str_replace('{where}', 'WHERE '.$dl['line'].' ', $structure);

                            $instance->query = $structure;
                            $instance->bind = $bind;
                        }
                    }
                }
            }

            $instance->query = $structure;
        }
        else
        {
            $instance->query = $structure;
        }

        if (!is_null($runCallback))
        {
            $run = $this->go();
            call_user_func($runCallback, $run);
        }

        return $instance;
    }

    // update request
    private function ___update($a, $structure, $table)
    {
        // set method
        $this->method = 'update';

        // run callback
        $runCallback = null;

        // check $a for any callback
        if (count($a) > 0)
        {
            foreach ($a as $index => $aData)
            {
                if ($aData !== null && is_callable($aData))
                {
                    $runCallback = $aData;
                    unset($a[$index]);
                }
            }
        }

        if (count($a) > 0)
        {
            // check if args 2 is string and possibly an object
            if (isset($a[1]) && is_string($a[1]))
            {
                $object = json_decode($a[1]);
                $copy = $a;

                // build new args
                $newArgs = [];

                if (is_string($a[0]))
                {
                    $obj = json_decode($a[0]);

                    if (is_null($obj))
                    {
                        $columns = explode(',', $a[0]);

                        foreach ($object as $key => $val)
                        {
                            $row = [];

                            $row[trim($columns[0])] = $key;
                            $row[trim($columns[1])] = $val;

                            $newArgs[] = $row;
                        }

                        if (count($newArgs) > 0)
                        {
                            unset($a[0], $a[1]);
                            $a = array_merge($newArgs, $a);
                        }
                    }
                }
            }

            // data passed
            $data = $a[0];

            // get rulesdata for object passed.
            if (is_object($data))
            {
                if (method_exists($data, 'rulesHasData'))
                {
                    $data = $data->rulesHasData();
                    // array shift
                    $a[0] = $data;
                }
            }

            // is object?
            if (is_object($data))
            {
                // convert to array
                $data = toArray($data);
            }

            // json data?
            if (is_string($data) && trim($data[0]) == '{' )
            {
                // convert to an object
                $data = toArray(json_decode($data));
                $a[0] = $data;
            }

            // data passed is an array
            if (is_array($data))
            {
                $arrayBind = $this->__arrayBind($data);

                $structure = str_replace('{query}', $arrayBind['set'], $structure);

                $this->query = $structure;
                $this->bind = $arrayBind['bind'];

                array_shift($a);
            }
            else
            {
                $dl = $this->__stringBind($data);
                $bind = $dl['bind'];

                array_shift($a);

                $this->__addBind($a, $bind);

                $structure = str_replace('{query}', $dl['line'], $structure);

                $this->query = $structure;
                $this->bind = $bind;
            }

            // where added ?
            if (count($a) > 0)
            {
                if (is_array($a[0]) || is_object($a[0]) || (is_string($a[0]) && $a[0] == '{'))
                {
                    if (is_object($a[0]))
                    {
                        $a[0] = toArray($a[0]);
                    }

                    if (is_string($a[0]) && $a[0] == '{')
                    {
                        $a[0] = toArray(json_decode($a[0]));
                    }

                    if (is_array($a[0]))
                    {
                        $whereBind = $this->__arrayBind($a[0], 'AND');
                        $where = $whereBind['set'];
                        $bind = $whereBind['bind'];

                        $structure = str_replace('{where}', 'WHERE '.$where.' ', $structure);

                        $this->query = $structure;
                        $this->lastWhere = 'WHERE '.$where.' ';

                        $newBind = [];

                        // avoid clashes
                        $this->__avoidClashes($bind, $newBind);

                        $this->bind = array_merge($this->bind, $newBind);
                    }
                    else
                    {
                        $this->errorpack['update'][] = 'Where statement not valid. Must be a string, object, array or json string';
                    }
                }
                else
                {
                    if (is_string($a[0]))
                    {
                        $line = $this->__stringBind($a[0]);
                        $where = $line['line'];
                        $bind = $line['bind'];

                        $structure = str_replace('{where}', 'WHERE '.$where.' ', $structure);
                        $this->query = $structure;
                        $this->lastWhere = 'WHERE '.$where.' ';

                        array_shift($a);

                        $this->__addBind($a, $bind);

                        $newBind = [];

                        // avoid clashes
                        $this->__avoidClashes($bind, $newBind);


                        $this->bind = array_merge($this->bind, $newBind);
                    }
                }
            }


        }
        else
        {
            // error, no data passed
            $this->errorpack['update'][] = 'No data passed.';
        }

        if (!is_null($runCallback))
        {
            $run = $this->go();
            call_user_func($runCallback, $run);
        }

        return $this;
    }

    // run binding
    private function runBinding()
    {
        $a = func_get_args();

        if (count($a) == 1 && is_array($a[0]))
        {
            $this->bind = array_merge($this->bind, $a[0]);
        }

        if (count($this->bind) > 0)
        {
            $__bind = [];

            foreach ($this->bind as $key => $val)
            {
                if (empty($val))
                {
                    $__bind[$key] = '';
                }
            }

            if (count($__bind) > 0)
            {
                $i = 0;
                $bind = [];

                if (is_array($a[0]))
                {
                    foreach ($a[0] as $i => $val1)
                    {
                        if (is_string($i) && isset($__bind[$i]))
                        {
                            $bind[$i] = $val1;
                        }
                        else
                        {
                            $keys = array_keys($__bind);

                            if (isset($keys[$i]))
                            {
                                $key = $keys[$i];
                                $bind[$key] = $val1;
                            }
                        }
                    }
                }
                else
                {
                    foreach ($__bind as $key => $val)
                    {
                        if (isset($a[$i]))
                        {
                            if (is_string($a[$i]))
                            {
                                $bind[$key] = addslashes(strip_tags($a[$i]));
                            }
                            elseif (is_object($a[$i]) || is_array($a[$i]))
                            {
                                $command = $this->method;

                                if (is_array($this->errorpack[$command]))
                                {
                                    $this->errorpack[$command][] = 'Invalid Bind parameter. Scaler Type expected, Compound Type passed.';
                                }
                                else
                                {
                                    $this->errorpack[$command] = [];
                                    $this->errorpack[$command][] = 'Invalid Bind parameter. Scaler Type expected, Compound Type passed.';
                                }
                            }
                            else
                            {
                                if (isset($a[$i]))
                                {
                                    $bind[$key] = $a[$i];
                                }
                            }
                        }
                        else
                        {
                            $bind[$key] = isset($a[$i-1]) ? $a[$i-1] : '';
                        }

                        $i++;
                    }
                }

                $newBind = [];
                $this->__avoidClashes($bind, $newBind);

                $this->bind = array_merge($this->bind, $newBind);
            }
        }

        return $this;
    }

    // run where
    private function runWhere()
    {
        $a = func_get_args();

        if (count($a) > 0)
        {
            $structure = $this->query;

            if (is_array($a[0]) || is_object($a[0]) || (is_string($a[0]) && $a[0] == '{'))
            {
                if (is_object($a[0]))
                {
                    $a[0] = toArray($a[0]);
                }

                if (is_string($a[0]) && $a[0] == '{')
                {
                    $a[0] = toArray(json_decode($a[0]));
                }

                if (is_array($a[0]))
                {
                    $sep = isset($a[1]) ? $a[1] : 'and';

                    $whereBind = $this->__arrayBind($a[0], $sep);
                    $where = $whereBind['set'];
                    $bind = $whereBind['bind'];

                    if (preg_match('/({where})/', $structure))
                    {
                        $structure = str_replace('{where}', 'WHERE '.$where.' ', $structure);
                        $this->query = $structure;
                        $this->lastWhere = 'WHERE '.$where.' ';
                    }
                    else
                    {
                        $this->query = trim($this->query). $this->wherePrefix .  $where;

                        $w = substr($this->query, strpos($this->query, 'WHERE'));
                        $w = substr($w, 0, strrpos($w, $where)) . $where;
                        $this->lastWhere = $w;
                    }

                    $newBind = [];

                    // avoid clashes
                    $this->__avoidClashes($bind, $newBind);

                    $this->bind = array_merge($this->bind, $newBind);
                }
                else
                {
                    $this->errorpack[$this->method][] = 'Where statement not valid. Must be a string, object, array or json string';
                }
            }
            else
            {
                if (is_string($a[0]))
                {
                    $line = $this->__stringBind($a[0]);

                    $where = $line['line'];
                    $bind = $line['bind'];

                    if (preg_match('/({where})/', $structure))
                    {
                        $structure = str_replace('{where}', 'WHERE '.$where.' ', $structure);
                        $this->query = $structure;
                        $this->lastWhere = 'WHERE '.$where.' ';
                    }
                    else
                    {
                        $this->query = trim($this->query) . $this->wherePrefix . $where;
                        $w = substr($this->query, strpos($this->query, 'WHERE'));
                        $w = substr($w, 0, strrpos($w, $where)) . $where;
                        $this->lastWhere = $w;
                    }

                    array_shift($a);

                    $this->__addBind($a, $bind);

                    $newBind = [];

                    // avoid clashes
                    $this->__avoidClashes($bind, $newBind);

                    $this->bind = array_merge($this->bind, $newBind);
                }
            }
        }

        return $this;
    }

    // run sqlStatement
    public function _sql()
    {
        $a = func_get_args();

        // sql
        $data = $a[0];
        array_shift($a);

        $instance = new DB;
        $instance->pdoInstance = $this->pdoInstance;
        $instance->driver = $this->driver;
        $instance->table = $this->table;

        if (is_string($data) && strlen($data) > 3)
        {
            $bind = [];
            $newBind = [];
            $getAssignment = true;

            if (isset($a[0]) && $a[0] === false)
            {
                $getAssignment = false;
            }

            if ($getAssignment)
            {
                // get assignment
                if (preg_match('/(=|!=|>|<|>=|<=)/', $data))
                {
                    preg_match_all('/\s{1,}([\S]+)\s{0,}(=|!=|>|<|>=|<=)\s{0,}[:|?|\'|"|0-9]/', $data, $match);

                    foreach ($match[0] as $i => $ln)
                    {
                        $ln = trim($ln);
                        $quote = preg_quote($ln);

                        $end = substr($ln,-1);
                        if ($end == ':')
                        {
                            preg_match("/($quote)([\S]+)/", $data, $m);
                            $ln = trim($m[0]);
                        }
                        elseif (preg_match('/[0-9]/', $end))
                        {
                            preg_match("/($quote)([\S]+|)/", $data, $m);
                            $ln = trim($m[0]);
                        }
                        elseif (preg_match('/[\'|"]/', $end))
                        {
                            preg_match("/($quote)([\s\S])['|\"|\S]{0,}['|\"]/", $data, $m);
                            $ln = trim($m[0]);
                        }

                        $dl = $instance->__stringBind($ln);
                        $line = $dl['line'];
                        $bind[] = $dl['bind'];

                        $beg = strpos($data, $ln);
                        $sized = strlen($ln);

                        $data = substr_replace($data, $line, $beg, $sized);
                        unset($match[0][$i]);
                    }

                    if (count($bind) > 0)
                    {
                        foreach($bind as $i => $arr)
                        {
                            if (is_array($arr))
                            {
                                foreach($arr as $key => $val)
                                {
                                    $newBind[$key] = $val;
                                }
                            }
                        }
                    }
                }

                if (count($newBind) > 0)
                {
                    $instance->__addBind($a, $newBind);

                    $newBind2 = [];

                    // avoid clashes
                    $instance->__avoidClashes($newBind, $newBind2);

                    $instance->bind = array_merge($instance->bind, $newBind2);
                }
            }

            $instance->query = $data;

            $instance->method = 'sql';

            return $instance;
        }

        return (object) ['rows' => 0, 'row' => 0, 'error' => 'Invalid sql statement.'];

    }

    // prepare query

    /** @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpUndefinedMethodInspection
     */
    private function ___prepare($query)
    {
        if ($this->pdoInstance == null)
        {
            $instance = $this->_serve();
            $this->pdoInstance = $instance->pdoInstance;
            $this->instancedb = $instance->instancedb;
        }

        if (strlen($query) > 4)
        {
            if (DatabaseHandler::$dbset === true)
            {
                $con = $this->pdoInstance;
                $usePDO = Handler::usePDO($this->instancedb);

                if ($this->pdoInstance != null)
                {
                    // use transactions.
                    if (method_exists($con, 'inTransaction') && $con->inTransaction() === false)
                    {
                        if (method_exists($con, 'beginTransaction'))
                        {
                            $con->beginTransaction();
                        }
                    }

                    $order = [];
                    $bind = $this->bind;

                    $this->getBinds = $bind;
                    $this->getSql = $query;


                    if (!$usePDO)
                    {
                        if (preg_match('/[:]([\S]*)/', $query))
                        {
                            $_query = $query;

                            preg_match_all('/([:][\S]*?)[,|\s|)]/', $query, $matches);
                            if (count($matches[0]) > 0 && count($bind) > 0)
                            {
                                foreach ($matches[1] as $index => $param)
                                {
                                    $replace = $param;
                                    $param = trim($param);
                                    $param = preg_replace('/^[:]/','',$param);
                                    $val = isset($bind[$param]) ? $bind[$param] : null;

                                    $type = '';

                                    switch (gettype($val))
                                    {
                                        case 'integer':
                                            $type = 'i';
                                            break;
                                        case 'string':
                                            $type = 's';
                                            break;
                                        case 'double':
                                            $type = 'd';
                                            break;
                                        case 'blob':
                                            $type = 'b';
                                            break;
                                        default:
                                            $type = 'i';
                                    }

                                    $order[] = [
                                        'type' => $type,
                                        'val' => $val
                                    ];
                                }
                            }

                            $_query = preg_replace('/([:][\S]*?)([,|\s|)])/','?$2',$_query);

                            $_query = preg_replace('/[?]([a-zA-Z]*)/','? $1', $_query);
                            $query = $_query;
                        }
                    }

                    $this->query = $query;

                    $smt = $con->prepare($query);

                    // extracting from extenal bind configuration.
                    if (count(self::$bindExternal) > 0)
                    {
                        foreach (self::$bindExternal as $key => $val)
                        {
                            if (isset($bind[$key]))
                            {
                                // setting bind up.
                                $bind[$key] = $val;
                            }
                        }
                    }


                    if (count($bind) > 0)
                    {
                        $index = 0;

                        if ($usePDO)
                        {
                            foreach ($bind as $key => $val)
                            {
                                if (is_array($val) && isset($val[$index]))
                                {
                                    $val = $val[$index];
                                    $index++;
                                }

                                if (!is_null($val) && is_callable($val))
                                {
                                    $val = call_user_func($val);
                                }

                                if (is_string($val))
                                {
                                    $smt->bindValue(':'.$key, $val, PDO::PARAM_STR);
                                }
                                elseif (is_int($val))
                                {
                                    $smt->bindValue(':'.$key, $val, PDO::PARAM_INT);
                                }
                                elseif (is_bool($val))
                                {
                                    $smt->bindValue(':'.$key, $val, PDO::PARAM_BOOL);
                                }
                                elseif (is_null($val))
                                {
                                    $smt->bindValue(':'.$key, $val, PDO::PARAM_NULL);
                                }
                                elseif (!is_array($val))
                                {
                                    if (!is_object($val))
                                    {
                                        $smt->bindValue(':'.$key, $val);
                                    }
                                    else
                                    {
                                        $smt->bindValue(':'.$key, null);
                                    }
                                }
                                else
                                {
                                    $value = array_shift($val);
                                    $smt->bindValue(':'.$key, $value);
                                }
                            }
                        }
                        else
                        {
                            $binds = [];
                            $types = [];

                            if (count($order) > 0)
                            {
                                foreach($order as $i => $arr)
                                {
                                    $types[] = $arr['type'];
                                    $binds[] = $arr['val'];
                                }
                            }

                            $types = implode('', $types);
                            $refArr = $binds;
                            $smt->bind_param($types, ...$refArr);
                            $_binds = [$types];
                            $_binds = array_merge($_binds, $binds);
                            $this->bind = $_binds;
                        }
                    }

                    return $smt;
                }

                throw new \Exceptions\Database\DatabaseException('Database not serving any connection to this file.');
            }
        }

        return null;
    }

    // execute query
    private function ___execute($smt)
    {
        $promise = new DBPromise;

        $usePDO = Handler::usePDO($this->instancedb);
        $promise->usePDO = $usePDO;
        $promise->setFetchMode();
        $promise->allowSlashes = $this->allowSlashes;


        if ($this->allowQuery($smt))
        {
            if ($this->query != '')
            {
                $query = $this->query;
                $bind = $this->bind;

                if ($this->method != 'get')
                {
                    $promise->setBindData($bind);
                }

                $this->query = '';
                $this->bind = [];

                if (DatabaseHandler::$dbset === true)
                {
                    try
                    {
                        $exec = $smt->execute();

                        if ($usePDO)
                        {
                            $rows = $smt->rowCount();
                        }
                        else
                        {

                            $smt->store_result();

                            if (is_object($smt) && property_exists($smt, 'num_rows'))
                            {
                                $rows = $smt->num_rows;
                            }
                            else
                            {
                                $rows = $smt->affected_rows;
                            }

                        }

                        if ($exec)
                        {
                            switch ($this->method)
                            {
                                case 'insert':
                                case 'update':
                                case 'delete':
                                    self::$transactionCode = 200;
                                    $this->saveQueryStatement($query, $bind);
                                    $this->queryCachePath = null;
                                    break;
                            }
                        }

                        $promise->setpdoInstance($smt);
                        $promise->rows = $rows;
                        $promise->row = $rows;

                        if ($this->method == 'get')
                        {
                            if ($rows == 1)
                            {
                                $promise->row = 1;

                                if ($usePDO)
                                {
                                    $arr = $smt->fetch(PDO::FETCH_ASSOC);
                                }
                                else
                                {
                                    $promise->bind_array($smt, $row);
                                    $smt->fetch();
                                    $smt->reset();

                                    $arr = $row;
                                }

                                $promise->set('getPacked', $arr);
                            }
                        }
                        elseif ($this->method == 'insert')
                        {
                            if ($usePDO)
                            {
                                $id = $this->pdoInstance->lastInsertId();
                            }
                            else
                            {
                                $id = $this->pdoInstance->insert_id;
                            }

                            $promise->id = $id;
                            $promise->ok = true;
                        }
                        else
                        {
                            $promise->ok = true;
                        }

                        if ($promise->ok)
                        {
                            // add to migration
                            if ($this->method != 'get' &&
                                $this->method == 'insert' ||
                                $this->method == 'update' ||
                                $this->method == 'delete'
                            )
                            {
                                $query = null;
                                $bind = null;
                            }
                        }

                        // commit transaction
                        if (method_exists($this->pdoInstance, 'commit'))
                        {
                            if ($this->pdoInstance->inTransaction())
                            {
                                $this->pdoInstance->commit();
                            }
                        }
                    }
                    catch(\PDOException $e)
                    {
                        if (method_exists($this->pdoInstance, 'rollback'))
                        {
                            // rollback transaction
                            $this->pdoInstance->rollback();
                        }

                        // pack error
                        $this->errorpack[$this->method][] = $e;
                        $promise->hasError = true;
                        $promise->errors[] = $e;
                    }
                }
            }
        }
        else
        {
            $promise->hasError = true;
            $promise->errors = ['Record Exists. Failed to insert'];
            $promise->ok = false;
            $promise->error = 'Failed to insert record. Data exists.';
        }

        $table = '';

        switch (strlen($this->table) > 1)
        {
            case true:
                $table = $this->table;
                break;

            case false:
                $table = self::$activeTable;
                break;
        }

        // set table
        $promise->table = $table;

        // reset allowSlashes
        $this->allowSlashes = false;

        // return promise
        return $promise;
    }

    public static function __callStatic($method, $data)
    {
        // create instance
        $createinstance = function() use ($method)
        {
            $instance = new DB;
            $instance->query = '';
            $instance->bind = [];
            $instance = $instance->_serve();
            $instance->getSql = '';
            $instance->getBinds = [];

            return $instance;
        };

        switch (trim($method))
        {
            case 'sql':
                return $createinstance()->callMethod('_sql', $data)->go();

            case 'table':
                $instance = $createinstance();
                $instance->table = self::getTableName($data[0]);
                return \Moorexa\DB\ORMReciever::getInstance($instance);

            case 'lazy':
                $instance = $createinstance();
                $instance->table = 'lazy'.time() * 2;
                return call_user_func_array([\Moorexa\DB\ORMReciever::getInstance($instance), 'lazyLoader'], $data);

            case 'serve':
                return $createinstance();

            case 'gettableinfo':
            case 'getTableInfo':
                $instance = $createinstance();
                return $instance->callMethod('_getTableInfo', $data);

            case 'apply':
                return $createinstance()->callMethod('_apply', $data);

            case 'pdo':
                return $createinstance()->pdoInstance;

            default:
                // set table name
                $instance = $createinstance();
                $instance->table = self::getTableName($method);
                return \Moorexa\DB\ORMReciever::getInstance($instance);

        }
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function __call($method, $data)
    {
        switch (trim($method))
        {
            case 'get':
            case 'delete':
            case 'update':
            case 'insert':
                if ($this->method != '' && $this->query != '')
                {
                    // execute the previous request
                    $exec = $this->go();
                    return call_user_func_array([$exec, $method], $data);
                }
                else
                {
                    $this->bind = [];
                    $instance = $this;

                    if ($this->driver == null)
                    {
                        $instance = $this->_serve();
                        $this->driver = $instance->driver;
                        $this->getAllowed();
                    }

                    $queries = $this->drivers($this->driver);

                    $pass = $method == 'get' ? 'select' : $method;
                    $structure = isset($queries[$pass]) ? $queries[$pass] : null;

                    $this->method = $method;

                    if (strlen($this->table) > 1)
                    {
                        $structure = str_replace('{table}', $this->table, $structure);
                    }

                    $func = '___'.$method;

                    return $this->callMethod($func, [$data, $structure, $this->table]);
                }

            case 'apply':
                return $this->callMethod('_apply', $data);


            case 'sql':
                return $this->callMethod('_sql', $data)->go();


            case 'allowHTML':
                $this->allowHTMLTags = true;
                break;

            case 'allowSlashes':
                $this->allowSlashes = true;
                break;

            case 'bind':
                return $this->callMethod('runBinding', $data);


            case 'where':
                return $this->callMethod('runWhere', $data);


            case 'orWhere':
                $this->wherePrefix = ' or ';
                return $this->callMethod('runWhere', $data);


            case 'andWhere':
                $this->wherePrefix = ' and ';
                return $this->callMethod('runWhere', $data);

            case 'hasRow':
            case 'hasRows':
                // execute request
               return call_user_func_array([$this->go(), $method], $data);


            default:

                if (isset($this->allowed[$method]))
                {
                    $allowed = $this->getAllowed($data, $this->query);
                    $this->query .= is_callable($allowed[$method]) ? $allowed[$method]($this) : $allowed[$method];
                }
                else
                {

                    // check if has fetch method
                    if (DBPromise::hasFetchMethod($method))
                    {
                        $run = $this->go();
                        return call_user_func_array([$run, $method], $data);
                    }

                    // specifically where..
                    if (preg_match('/({where})/', $this->query))
                    {
                        $newBind = [];
                        $bind = [$method => ''];

                        $i = $this->__avoidClashes($bind, $newBind);

                        $where = 'WHERE '.$method.' = :'.$method.$i.' ';

                        $this->bind = array_merge($this->bind, $newBind);

                        $this->query = str_replace('{where}', $where, $this->query);
                    }
                    else
                    {

                        $newBind = [];
                        $bind = [$method => ''];

                        $i = $this->__avoidClashes($bind, $newBind);

                        $append = ' '.$method.' = :'.$method.$i.' ';

                        $this->bind = array_merge($this->bind, $newBind);

                        $this->query = trim($this->query) . $append;
                    }

                }
        }

        return $this;
    }

    // run order by primary key
    public function orderbyprimarykey($mode = 'asc')
    {
        // get table information
        $table = DB::sql('DESCRIBE '.$this->table);

        if ($table->rows > 0)
        {
            // get primary key
            while ($column = $table->obj())
            {
                if ($column->Key == 'PRI')
                {
                    $this->orderby($column->Field, $mode);
                    break;
                }
            }
        }

        return $this;
    }

    // allow query execution.
    private function allowQuery(&$con)
    {
        if ($this->method == 'insert')
        {
            $instance = &$this;

            if ($instance->allowedQueryCalled === false)
            {
                $usePDO = Handler::usePDO($instance->instancedb);

                $db = $instance->pdoInstance;
                // check if record doesn't exists.
                // to avoid repitition.
                // get columns
                $column = substr($instance->query, strpos($instance->query, '(')+1);
                $column = substr($column, 0, strpos($column, ')'));

                // convert to an array
                $array = explode(',', $column);

                $binds = $instance->bind;

                $where = [];

                if ($instance->query != '')
                {
                    // get the columns
                    preg_match('/([(].*?[)])/', $instance->query, $column);

                    if (isset($column[0]))
                    {
                        // remove bracket
                        $column = preg_replace('/[)|(]/','', $column[0]);

                        // build whare statement
                        $columnArray = explode(',', $column);

                        // where
                        foreach ($columnArray as $column)
                        {
                            $where[] = $column . ' = ?';
                        }
                    }

                    // now start from values
                    $values = stristr($instance->query, 'values');
                    $orginalValue = $values;
                    // remove "VALUES"
                    $values = ltrim($values, 'VALUES ');

                    // now get all values and check database or remove from where statement
                    preg_match_all('/([(].*?[)])/', $values, $matches);
                    $newBind = [];
                    $newValues = [];

                    // get where
                    $where = implode(' AND ', $where);
                    $select = 'SELECT * FROM '.$instance->table.' WHERE '.$where;

                    // run prepared statement
                    $sel = $db->prepare($select);

                    if (count($matches[0]) > 0)
                    {
                        foreach ($matches[0] as $value)
                        {
                            $orginal = $value;
                            // remove bracket
                            $value = preg_replace('/[)|(|:]/','', $value);
                            $valueArray = explode(',', $value);

                            // bind
                            $bind = [];

                            foreach($valueArray as $bindKey)
                            {
                                $bindVal = $instance->bind[$bindKey];

                                if (!is_null($bindVal) && is_callable($bindVal))
                                {
                                    $bindVal = call_user_func($bindVal);
                                }

                                $bind[] = $bindVal;
                            }

                            $execute = $sel->execute($bind);

                            if ($sel->rowCount() == 0)
                            {
                                $newValues[] = $orginal;

                                foreach ($valueArray as $bindKey)
                                {
                                    $bindVal = $instance->bind[$bindKey];

                                    if (!is_null($bindVal) && is_callable($bindVal))
                                    {
                                        $bindVal = call_user_func($bindVal);
                                    }

                                    $newBind[$bindKey] = $bindVal;
                                }
                            }
                        }
                    }

                    if (count($newValues) > 0)
                    {
                        $values = implode(', ', $newValues);
                        $instance->bind = $newBind;

                        $instance->query = str_replace($orginalValue, 'VALUES '.$values, $instance->query);

                        return true;
                    }

                    return false;
                }
            }
        }

        return true;
    }

    // check for potential errors
    private function __checkForErrors($command)
    {
        $free = true;
        $query = $this->query;
        $errors = [];

        switch ($command)
        {
            case 'update':
                if (preg_match('/({table})/', $query))
                {
                    $free = false;
                    $errors[] = 'Table not found. Statement Constrution failed.';
                }

                if (preg_match('/({query})/', $query))
                {
                    $free = false;
                    $errors[] = 'Query not found. Statement Constrution failed.';
                }

                if (preg_match('/({where})/', $query))
                {
                    $free = false;
                    $errors[] = 'Where statement missing. Statement Constrution failed.';
                }
                break;
        }

        if (count($errors) > 0)
        {
            if (!isset($this->errorpack[$command]))
            {
                $this->errorpack[$command] = [];
            }

            $this->errorpack[$command] = array_merge($this->errorpack[$command], $errors);
        }

        return $free;
    }

    // serve database connection
    private function _serve()
    {
        // get default data-source-name]
        $connect = DatabaseHandler::$default;

        if ($this->useConnection !== null)
        {
            $connect = $this->useConnection;
        }

        if (!is_null($connect))
        {
            if ($this->pdoInstance == null)
            {
                if (isset(self::$openedConnection[$connect]))
                {
                    $connection = new DB;
                    $currentDB = self::$openedConnection[$connect];
                    $connection->driver = $currentDB->driver;
                    $connection->instancedb = $connect;
                    $connection->allowed = $currentDB->allowed;
                    $connection->pdoInstance = $currentDB->pdoInstance;

                    return $connection;
                }

                if (is_string($connect) && strlen($connect) > 1)
                {
                    // get driver
                    $driver = DatabaseHandler::connectionConfig($connect, 'driver');

                    if (DatabaseHandler::$dbset === true)
                    {
                        // a valid driver?
                        if ($this->drivers($driver) !== null)
                        {
                            // save driver
                            $this->driver = $driver;
                            $this->instancedb = $connect;

                            // save instance.
                            $this->pdoInstance = DatabaseHandler::active($connect);

                            $this->allowed = $this->getAllowed();

                            // save instance
                            self::$openedConnection[$connect] = $this;
                        }
                        else
                        {
                            throw new \Exceptions\Database\DatabaseException('Driver you used isn\'t supported on this server. Please see documentation');
                        }
                    }
                }

            }
        }

        return $this;
    }

    // process request
    public function go()
    {
        static $queries;

        if (is_null($queries))
        {
            $queries = [];
        }

        $returnPromise = function()
        {
            $promise = BootMgr::singleton(DBPromise::class);
            $promise->table = $this->table;
            $promise->setpdoInstance($this->pdoInstance);
            $promise->rows = 0;
            $promise->row = 0;

            return $promise;
        };

        if ($this->returnPromise)
        {
            return $returnPromise();
        }

        if (!is_null($this->returnNewQuery))
        {
            return $this->returnNewQuery;
        }

        if ($this->failed === false)
        {
            // listen for channels opened
            Handler::callChannel($this->method, $this, $canContinue);

            // call Prefix Query method
            $this->callPrefixQuery($this);


            if ($canContinue === true || $canContinue === null)
            {
                // channel passed and new query returned ?
                if (!is_null($this->returnNewQuery))
                {
                    return $this->returnNewQuery;
                }

                // get method
                $name = $this->method;

                // process request.
                // handle errors
                $ok = $this->__checkForErrors($name);

                // we good?
                if ($ok)
                {
                    // good
                    if ($name == 'get')
                    {

                        // fill in the gap
                        foreach ($this->bind as $key => $val)
                        {
                            if (is_null($val) || (is_string($val) && strlen($val) == 0))
                            {
                                foreach ($this->bind as $i => $x)
                                {
                                    if (!empty($x))
                                    {
                                        $this->bind[$key] = $x;
                                        break;
                                    }
                                }
                            }
                        }

                        // remove placeholder {where}
                        $this->query = str_replace('{where}','',$this->query);
                    }

                    if (!$this->allowHTMLTags)
                    {
                        $bind = $this->bind;

                        foreach ($bind as $key => $val)
                        {
                            if (is_array($val) || is_object($val))
                            {
                                foreach ($val as $i => $x)
                                {
                                    if (is_string($x))
                                    {
                                        $val[$i] = strip_tags($x);
                                    }
                                }

                                $bind[$key] = $val;
                            }
                            elseif (is_string($val))
                            {
                                $bind[$key] = strip_tags($val);
                            }
                        }

                        $this->bind = $bind;
                    }

                    if ($this->method != '')
                    {
                        // prepare query
                        $smt = $this->___prepare($this->query);

                        $promise = $this->___execute($smt);

                        $this->method = '';

                        // save to queries
                        $queries[$this->table] = $promise;

                        // save for last query ran
                        self::$lastQueryRan = $promise;

                        // return promise
                        return $promise;
                    }

                    if (isset($queries[$this->table]))
                    {
                        return $queries[$this->table];
                    }
                }
            }

            return $returnPromise();
        }

        return (object) ['rows' => 0, 'row' => 0, 'error' => $this->errorpack];
    }

    // query method
    public function query($loadFrom)
    {
        $classCaller = $this->classUsingLazy;

        switch (gettype($loadFrom))
        {
            case 'string':
                // build method name
                $method = 'query'.ucwords($loadFrom);
                break;

            case 'array':
                // get class name and method.
                list($className, $method) = $loadFrom;
                $method = 'query'.ucwords($method);

                // check class name
                $classCaller = $className; // here we assume $className is an object

                // but let's check if it's a string
                if (is_string($classCaller))
                {
                    // build singleton
                    $classCaller = BootMgr::singleton($classCaller);
                }
                break;
        }

        // get arguments
        $args = func_get_args();
        $args = array_splice($args, 1);
        array_unshift($args, $this);

        if (is_null($classCaller))
        {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

            foreach ($trace as $traceArray)
            {
                // check if traceArray has a class
                if (isset($traceArray['class']))
                {
                    // check for class
                    $className = $traceArray['class'];

                    if ($className != 'Moorexa\DB' && $className != 'Moorexa\DB\ORMReciever')
                    {
                        $classCaller = BootMgr::singleton($className);
                        break;
                    }
                }
            }

            $trace = null;
            $traceArray = null;
        }

        // check if method exists
        if (method_exists($classCaller, $method))
        {
            call_user_func_array([$classCaller, $method], $args);
        }

        return $this;
    }

    // find method
    public function find()
    {
        // get arguments
        $arguments = func_get_args();

        // get table name
        $tableName = $this->table;

        // remove anything that's not a character
        $tableName = preg_replace('/[^a-zA-Z]/', ' ', $tableName);
        $tableName = ucwords($tableName);
        $tableName = preg_replace('/(\s*)/', '', $tableName);

        // check if class exists
        $className = '\Relationships\\'.$tableName;

        if (class_exists($className))
        {
            // create reflection class
            try {
                $ref = new \ReflectionClass($className);
                switch (count($arguments) > 0)
                {
                    case true:

                        list($firstArgs) = $arguments;

                        // build method
                        $method = $className . '::find' . ucwords($firstArgs);

                        if ($ref->hasMethod('find' . ucwords($firstArgs)))
                        {
                            $arguments = array_splice($arguments, 1);
                            array_unshift($arguments, $this);

                            // call method
                            call_user_func_array($method, $arguments);
                        }
                        else
                        {
                            $method = $className . '::find';

                            if ($ref->hasMethod('find'))
                            {
                                array_unshift($arguments, $this);

                                call_user_func_array($method, $arguments);
                            }
                        }

                        break;

                    case false:

                        $method = $className . '::find';

                        if ($ref->hasMethod('find'))
                        {
                            array_unshift($arguments, $this);

                            call_user_func_array($method, $arguments);
                        }

                        break;
                }

                if (!$this->pauseExecution)
                {
                    return $this->go();
                }
            } catch (\ReflectionException $e) {
            }

        }

        return $this;
    }

    // query should fail
    // an intentional request. forces db promise to be returned
    public function queryShouldFail()
    {
        $this->returnPromise = true;

        return $this->go();
    }

    // query should return
    // instend of returning a new promise, return promise from another query
    public function queryShouldReturn($object)
    {
        $this->returnNewQuery = $object;
        return $object;
    }

    // group method
    public function group(\closure $callback)
    {
        $this->pauseExecution = true;

        call_user_func($callback, $this);

        return $this->go();
    }

    // get __magic method
    public function __get($name)
    {
        if ($this->method != '')
        {
            return $this->go()->{$name};
        }

        return null;
    }

    // config method
    public function config(array $config) : DB
    {
        foreach ($config as $property => $value)
        {
            if (strtolower($property) == 'allowhtml')
            {
                $property = 'allowHTMLTags';
            }

            $this->{$property} = $value;
        }

        return $this;
    }

    private function callMethod($method, $data)
    {
        return call_user_func_array([$this, $method], $data);
    }

    // append primary key to sql statement
    public function primary($key, $prefix = null)
    {
        // get table information
        $table = DB::sql('DESCRIBE '.$this->table);

        if ($table->rows > 0)
        {
            // get primary key
            $primary = '';

            while ($column = $table->obj())
            {
                if ($column->Key == 'PRI')
                {
                    $primary = $column->Field;
                    break;
                }
            }

            // method
            $method = 'where';

            if ($prefix == 'or')
            {
                $method = 'orWhere';
            }

            if ($prefix == 'and')
            {
                $method = 'andWhere';
            }

            if (strlen($primary) > 0)
            {
                return $this->{$method}($primary . ' = ?', $key);
            }
        }

        // continue with previous build
        return $this;
    }

    // append 'or' primary key to sql statement
    public function orPrimary($key)
    {
        return $this->primary($key, 'or');
    }

    // append 'and' primary key to sql statement
    public function andPrimary($key)
    {
        return $this->primary($key, 'and');
    }

    // get prefix
    public static function getPrefix()
    {
        $prefix = handler::$prefix;

        if (self::$prefix !== null)
        {
            $prefix = self::$prefix;
        }

        if (self::$noprefix)
        {
            $prefix = null;
        }

        return $prefix;
    }

    // set prefix
    public static function prefix(string $prefix)
    {
        self::$prefix = $prefix;
    }

    // no prefix
    public static function noPrefix()
    {
        self::$noprefix = true;
    }

    // reset prefix
    public static function resetPrefix()
    {
        self::$noprefix = false;
        self::$prefix = null;
    }

    // set bind
    public function setBind(string $key, string $content)
    {
        $this->bind[$key] = $content;
    }

    // get argument passed
    public function getArguments()
    {
        return $this->argumentPassed;
    }

    // set argument
    public function setArgument(string $key, $value)
    {
        foreach ($this->argumentPassed as $index => $arr)
        {
            if (is_array($arr))
            {
                $this->argumentPassed[$index][$key] = $value;
            }
            elseif (is_object($arr))
            {
                $this->argumentPassed[$index]->{$key} = $value;
            }
        }
        return $this;
    }

    // rebuild query
    public function reBuild()
    {
        $method = $this->method;

        // reset query
        $this->query = '';

        // reset bind
        $this->bind = [];

        // reset getSql
        $this->getSql = '';

        // reset method
        $this->method = '';

        $this->callMethod($method, $this->argumentPassed);
    }

    // get query path
    private function getQuerySavePath(string $handler = '', string $driver = '')
    {
        // get handler
        $handler = strlen($handler) == 0 ? Handler::$connectWith : $handler;

        // get driver
        $driver = strlen($driver) == 0 ? Handler::$driver : $driver;

        // return query cache path
        if (!is_null($this->queryCachePath))
        {
            return $this->queryCachePath;
        }

        // create hash
        $hash = md5($handler . $driver) . '.php';

        // return base path
        return HOME . 'lab/Sql/' . ucfirst($driver) . '/' . $hash;
    }

    // save query
    private function saveQueryStatement(string $query, array $bind)
    {
        if ($this->allowSaveQuery)
        {
            if (env('bootstrap', 'enable.db.caching') && $this->cacheQuery)
            {
                // get handler
                $handler = Handler::$connectWith;

                // get path
                $path = $this->getQuerySavePath();

                $line = [];
                $line[] = '<?php';
                $line[] = 'return [];';
                $line[] = '?>';

                if (!file_exists($path))
                {
                    file_put_contents($path, implode("\n\n", $line));
                }

                // get data
                $data = include($path);

                if (!is_array($data))
                {
                    $data = [];
                }

                // build index
                $index = md5($query) . sha1(implode('', $bind));

                // remove slashes
                foreach ($bind as $key => $value)
                {
                    $bind[$key] = stripslashes($value);
                }

                // add data
                $data[$handler][$this->table][$index]['query'] = $query;
                $data[$handler][$this->table][$index]['bind'] = $bind;

                // export
                ob_start();
                var_export($data);
                $data = ob_get_contents();
                ob_clean();

                // add to line
                $line[1] = 'return '.$data.';';

                // save now
                file_put_contents($path, implode("\n\n", $line));
            }
        }
    }

    // run migration for cached tables
    public function runSaveCacheStatements(string $tableName, string $driver, string $handler)
    {
        if (defined('RUN_MIGRATION'))
        {
            // get path
            $path = $this->getQuerySavePath($handler, $driver);

            if (file_exists($path))
            {
                if (count(self::$cacheQueryData) == 0)
                {
                    // set data
                    $data = include_once($path);

                    if (is_array($data))
                    {
                        self::$cacheQueryData = $data;
                    }
                }

                // get data
                $data = self::$cacheQueryData;

                if (isset($data[$handler]))
                {
                    $dataHandler = $data[$handler];

                    // check for table
                    if (isset($dataHandler[$tableName]))
                    {
                        // get queries
                        $queries = array_values($dataHandler[$tableName]);

                        // set table
                        $this->table = $tableName;
                        $this->cacheQuery = false;

                        // run queries
                        foreach ($queries as $key => $data)
                        {
                            $this->query = $data['query'];
                            $this->bind = $data['bind'];

                            // get query
                            preg_match('/^(update|insert|delete|select)/i', trim($this->query), $match);

                            if (isset($match[0]))
                            {
                                // add method
                                $this->method = strtolower($match[0]);
                            }

                            // prepare statement
                            try {
                                $smt = $this->___prepare($data['query']);
                                // execute query
                                $execute = $this->___execute($smt);
                            } catch (DatabaseException $e) {
                            }


                        }
                    }
                }
            }
        }
    }

    // register prefix
    public static function registerPrefix()
    {
        // get prefix passed
        $prefixes = func_get_args();

        if (count($prefixes) > 0)
        {
            array_map(function($prefix)
            {
                self::$prefixRegistered[] = $prefix;
            }, $prefixes);
        }
    }

    // register prefix query
    public static function prefixQuery(string $prefix, \closure $callback)
    {
        self::$prefixRegistry[$prefix][] = $callback;
    }

    // call prefix callbacks
    private function callPrefixQuery(&$instance)
    {
        $prefixRegistry = self::$prefixRegistry;

        // get prefix and check in table name
        foreach ($prefixRegistry as $prefix => $arrayOfClosure)
        {
            // quote prefix
            $quote = preg_quote($prefix);

            if (preg_match("/^($quote)/i", $this->table) || preg_match("/(\s+|`)($quote)/", $this->query))
            {
                array_map(function($callback) use (&$instance)
                {
                    call_user_func_array($callback, [&$instance, &$instance->table, &$instance->query]);

                }, $arrayOfClosure);

                break;
            }
        }
    }

    // get last query
    public function lastQuery()
    {
        $lastQueryRan = DB::$lastQueryRan;

        if (!is_null($lastQueryRan))
        {
            return $lastQueryRan;
        }

        return $this;
    }
}

// ends here.
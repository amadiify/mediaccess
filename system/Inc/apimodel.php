<?php

namespace Moorexa;

/**
 * @package ApiModel
 * @author Amadi Ifeanyi <amadiify.com>
 */

class ApiModel
{
    // avoid creating a fresh instance
    public static $hasRules = null;

    // rules created
    public $__rules__ = [];

    // rules backup
    protected $__rules__backup = [];

    // errors occured
    private $errors = ['GET' => [], 'POST' => []];

    // tables
    private static $tables = [];

    // flag request
    public $flagRequest = 'NONE';

    // custom errors
    public $customErrors = [];

    // current object
    public $currentObject = null;

    // current table
    protected $table = null;

    // transactions
    public $transactions = [];

    // current instance
    protected static $modelInstance = null;

    // useRules array
    public static $useRulesCreated = [];

    // identity created
    public $identityCreated = [];

    // on success request
    private $onSuccessBox = [];

    // get set rules
    public final static function getSetRules($classObject, $method='setRules')
    {
        // set currentObject
        $classObject->currentObject = $classObject;
        $classObject->__rules__ = [];

        // check if setRules exists.
        if (method_exists($classObject, $method))
        {
            $classObject->flagRequest = 'NONE'; // reset
            $classObject->customErrors = []; // reset\
            $classObject->usingRule = true;

            $createRules = $classObject->createRule($classObject);

            $url = \ApiManager::$getUrl;

            array_unshift($url, $createRules);

            // get parameters
            Bootloader::$instance->getParameters($classObject, $method, $const, $url);

            // call method
            call_user_func_array([$classObject, $method], $const);

            // pull from storage
            self::pullFromStorage($createRules);

            // save rules.
            $classObject->__rules__ = $createRules->rules;

            if (count($createRules->rules) > 0)
            {
                // listen for http_request
                $classObject->listenForHttpRequest();
            }
        }
    }

    // listen for request
    public final function listenForHttpRequest()
    {
        static $post;
        static $get;

        // get POST data
        if (is_null($post))
        {
            $post = new HttpPost;
        }

        // get GET data
        if (is_null($get))
        {
            $get = new HttpGet;
        }

        $skip = [];

        // callbackClass
        $callbackClass = function($key)
        {
            return new class($key, $this)
            {
                private $key;
                private $object;

                public function __construct($key, &$object)
                {
                    $this->key = $key;
                    $this->object = $object;
                }

                public function setTo($value)
                {
                    $this->object->__rules__[$this->key]['value'] = $value;
                    $this->object->__rules__[$this->key]['rule'] = null;
                }
            };
        };

        // run for get
        if (!$get->isEmpty())
        {
            $validator = Plugins::validator($get->data());

            // pass custom errors
            $validator->customErrors = $this->customErrors;

            foreach ($this->__rules__ as $key => $config)
            {
                if ($get->has($key))
                {
                    if (!is_null($config['rule']))
                    {
                        if ($this->flagRequest != 'GET')
                        {
                            $validator->skipRequired = true;
                        }

                        $error = [];

                        $isValid = $validator->validate([$key => $config['rule']], $error);

                        // is valid ?
                        if ($isValid)
                        {
                            if (strlen($get->get($key)) > 0)
                            {
                                $this->__rules__[$key]['value'] = $get->get($key);

                                // skip in post
                                $skip[$key] = $get->get($key);
                            }

                            if (isset($this->onSuccessBox[$key]))
                            {
                                // call callback function
                                if (is_callable($this->onSuccessBox[$key]))
                                {
                                    $val = call_user_func($this->onSuccessBox[$key], $key, $get, $callbackClass($key));

                                    if ($val != null)
                                    {
                                        $this->__rules__[$key]['value'] = $val;
                                        $this->__rules__[$key]['rule'] = null;
                                    }
                                }
                            }
                        }
                        else
                        {
                            $this->errors['GET'][$key] = array_values($error);
                        }
                    }
                    else
                    {
                        if (strlen($get->get($key)) > 0)
                        {
                            $this->__rules__[$key]['value'] = $get->get($key);
                        }
                    }
                }
                else
                {
                    if (!is_null($config['rule']))
                    {
                        $rule = $config['rule'];

                        if (stripos($rule, 'required') !== false && $config['default'] == null && $this->flagRequest == 'GET')
                        {
                            $this->errors['GET'][$key] = [$key . ' is required.'];
                        }
                    }
                }
            }
        }
        else
        {
            foreach ($this->__rules__ as $key => $config)
            {
                if (!is_null($config['rule']))
                {
                    $rule = $config['rule'];

                    if ( (is_string($rule) && stripos($rule, 'required') !== false) && $config['default'] == null && $this->flagRequest == 'GET')
                    {
                        $this->errors['GET'][$key] = [$key . ' is required.'];
                    }
                }
            }
        }

        // run for post
        if (!$post->isEmpty())
        {
            $validator = Plugins::validator($post->data());

            // pass custom errors
            $validator->customErrors = $this->customErrors;

            foreach ($this->__rules__ as $key => $config)
            {
                if ($post->has($key) && !isset($skip[$key]))
                {
                    if (!is_null($config['rule']))
                    {
                        if ($this->flagRequest != 'POST')
                        {
                            $validator->skipRequired = true;
                        }

                        $error = [];
                        
                        $isValid = $validator->validate([$key => $config['rule']], $error);

                        // is valid ?
                        if ($isValid)
                        {
                            if (strlen($post->get($key)) > 0)
                            {
                                $this->__rules__[$key]['value'] = $post->get($key);
                            }

                            if (isset($this->onSuccessBox[$key]))
                            {
                                // call callback function
                                if (is_callable($this->onSuccessBox[$key]))
                                {
                                    $value = '';

                                    $data = [];
                                    $data[] = $key;
                                    $data[] = $post;
                                    $data[] = $callbackClass($key);

                                    $val = call_user_func_array($this->onSuccessBox[$key], $data);

                                    if ($val != null)
                                    {
                                        $this->__rules__[$key]['value'] = $val;
                                        $this->__rules__[$key]['rule'] = null;
                                    }
                                }
                            }
                        }
                        else
                        {
                            $this->errors['POST'][$key] = array_values($error);
                        }
                    }
                    else
                    {
                        if (is_string($post->get($key)) && strlen($post->get($key)) > 0)
                        {
                            $this->__rules__[$key]['value'] = $post->get($key);
                        }
                        elseif (is_array($post->get($key)))
                        {
                            $this->__rules__[$key]['value'] = $post->get($key);
                        }
                    }
                }
                elseif (!$post->has($key))
                {
                    if (!is_null($config['rule']))
                    {
                        $rule = $config['rule'];

                        if ( (is_string($rule) && stripos($rule, 'required') !== false) && $config['default'] == null && $this->flagRequest == 'POST')
                        {
                            $this->errors['POST'][$key] = [$key . ' is required.'];
                        }
                    }

                }
            }
        }
        else
        {
            foreach ($this->__rules__ as $key => $config)
            {
                if (!is_null($config['rule']))
                {
                    $rule = $config['rule'];

                    if (stripos($rule, 'required') !== false && $config['default'] == null && $this->flagRequest == 'POST')
                    {
                        $this->errors['POST'][$key] = [$key . ' is required.'];
                    }
                }
            }
        }

        // clean up
        $skip = null;
        $validator = null;
        $isValid = null;

    }

    // getter method
    public function __get($name)
    {
        if (isset($this->__rules__backup[$name]))
        {
            return $this->getBackupRule($name);
        }
        elseif (isset(\ApiManager::$storage[$name]))
		{
			return \ApiManager::$storage[$name];
		}
        else
        {
            return $this->getRule($name);
        }
    }

    // setter method
    public function __set($name, $val)
    {
        if (isset($this->__rules__[$name]))
        {
            $this->__rules__[$name]['value'] = $val;

            // revalidate and remove from required
            $this->revalidate($name, $val);
        }
        else
        {
            if ($this->usingRule)
            {
                // set new.
                $this->__rules__[$name]['value'] = $val;
                $this->__rules__[$name]['rule'] = null;

                // revalidate and remove from required
                $this->revalidate($name, $val);
            }
        }
    }

    // push data to rule
    public function pushData($data)
    {
        if (is_array($data) || is_object($data))
        {
            foreach ($data as $key => $val)
            {
                if (is_string($key))
                {
                    $this->{$key} = $val;
                }
            }
        }
    }

    // revalidate
    public function revalidate($key, $val)
    {
        // get rule
        $rule = $this->__rules__[$key]['rule'];

        if ($this->isPassed($val, $rule, $errors))
        {
            $this->freeFromCustomErrors($key);
        }
        else
        {
            $this->errors[$this->flagRequest][$key] = $errors;
        }
    }

    // free from custom error
    private function freeFromCustomErrors($key)
    {
        // remove from GET
        if (isset($this->errors['GET'][$key]))
        {
            unset($this->errors['GET'][$key]);
        }

        // remove from POST
        if (isset($this->errors['POST'][$key]))
        {
            unset($this->errors['POST'][$key]);
        }
    }

    // get backup rule method
    public function getBackupRule($name)
    {
        if (isset($this->__rules__backup[$name]))
        {
            $rule = $this->__rules__backup[$name];

            if (isset($rule['value']))
            {
                return $rule['value'];
            }

            // return default;
            return $rule['default'];
        }

        return null;
    }

    // get rule method
    public function getRule($name)
    {
        if (isset($this->__rules__[$name]))
        {
            $rule = $this->__rules__[$name];

            if (isset($rule['value']))
            {
                return $rule['value'];
            }

            // return default;
            return isset($rule['default']) ? $rule['default'] : null;
        }

        return null;
    }

    // clear rules
	public function clear($key=null)
	{
		$rules = $this->__rules__;
		$data = null;
		
		if (count($rules) > 0)
		{
			if ($key === null)
			{
				foreach ($rules as $name => $config)
				{
					if (isset($config['default']))
					{
						$rules[$name]['default'] = null;
					}

					if (isset($config['value']))
					{
						$rules[$name]['value'] = null;
					}
				}
			}
			else
			{
				if (isset($rules[$key]))
				{
					$data = $this->getRule($key);

					// remove
					$rules[$key]['default'] = null;
					$rules[$key]['value'] = null;
				}
			}

			$this->__rules__ = $rules;
		}

		return $data;
	}

    // set identity method
    public function identity($key)
    {
        $val = $this->getRule($key);

        if (!is_null($val))
        {
            $this->identityCreated[$key] = $val;
        }

        return $this;
    }

    // caller method
    public function __call($meth, $args)
    {
        if (preg_match('/([a-z]{2,})([A-Z]{1})([\S]+)/', $meth, $req))
        {
            $action = $req[1];

            $table = lcfirst($req[2].$req[3]);

            $this->getTable($c_table);

            $this->currentObject->table = $table;

            // call method
            $this->transactions[$meth]['return'] = call_user_func_array([$this, $action], $args);

            // return to current
            $this->currentObject->table = $c_table;
        }
        elseif ($meth == 'has')
        {
            return $this->_has($args[0]);
        }
        else
        {
            $req = $this->switchOptions($meth, $args);

            if ($req !== null)
            {
                return $req;
            }
        }

        return $this;
    }

    // pop method
    public function pop($key)
    {
        $args = func_get_args();

        if (count($args) > 1)
        {
            foreach ($args as $index => $key)
            {
                if (isset($this->__rules__[$key]))
                {
                    unset($this->__rules__[$key]);

                    $this->freeFromCustomErrors($key);
                }
            }
        }
        else
        {
            $data = $this->getRule($key);

            if (isset($this->__rules__[$key]))
            {
                unset($this->__rules__[$key]);

                $this->freeFromCustomErrors($key);
            }

            return $data;
        }
    }

    // switch options
    public function switchOptions($meth, $args, $defaultToDB = true)
    {
        if ($meth == 'update' && count($args) == 0)
        {
            return $this->__update();
        }
        elseif ($meth == 'fetch' || $meth == 'get' && count($args) == 0)
        {
            return $this->__fetch();
        }
        elseif ($meth == 'remove' && count($args) == 0)
        {
            return $this->__remove();
        }
        elseif (isset($this->__rules__[$meth]))
        {
            if (!isset($args[0]))
            {
                return $this->getRule($meth);
            }
            else
            {
                $this->__rules__[$meth]['value'] = $args[0];

                // revalidate and remove from required
                $this->revalidate($meth, $args[0]);
            }

            return $this;
        }
        else
        {
            if ($defaultToDB)
            {
                $this->getTable($table);

                // assign table
                $db = DB::table($table);

                // call method
                return call_user_func_array([$db, $meth], $args);
            }
        }

        return null;
    }

    // create record
    public function create(&$id=0)
    {
        if ($this->isOk())
        {
            if (is_null($this->table))
            {
                $this->getTable($table);
                $this->table = $table;
            }

            // create record
            $insert = DB::table($this->table)->insert($this);

            if ($insert->ok)
            {
                // good
                $id = $insert->id;
                $this->transactions['create'.ucfirst($this->table)]['args'] = [$id];

                return true;
            }
        }

        return false;
    }

    // update record
    public function __update()
    {
        if ($this->isOk())
        {
            static $promise;

            if (is_null($this->table))
            {
                $this->getTable($table);
                $this->table = $table;
            }

            if (is_null($promise))
            {
                // get promise
                $promise = new DBPromise();
            }

            $promise->table = $this->table;

            if (count($this->identityCreated) == 0)
            {
                // get primary key
                $promise->getTableInfo($pri);

                // get id
                $id = $this->getRule($pri);
            }
            else
            {
                $keys = array_keys($this->identityCreated);

                // get primary key
                $pri = $keys[0];

                // get id
                $id = $this->identityCreated[$pri];

                // reset
                $this->identityCreated = [];
            }

            if (!is_null($id))
            {
                // run update
                $update = DB::table($this->table)->update($this->currentObject, $pri . ' = ?', $id);

                if ($update->ok)
                {
                    return true;
                }

                return false;
            }
            
            $error = 'Primary KEY #{'.$pri.'} not found in rules';

            throw new \Exception($error);

            return false;
        }

        return false;
    }

    // delete record
    public function __remove()
    {
        if ($this->isOk())
        {
            static $promise;

            if (is_null($this->table))
            {
                $this->getTable($table);
                $this->table = $table;
            }

            if (is_null($promise))
            {
                // get promise
                $promise = new DBPromise();
            }

            $promise->table = $this->table;


            if (count($this->identityCreated) == 0)
            {
                // get primary key
                $promise->getTableInfo($pri);

                // get id
                $id = $this->getRule($pri);
            }
            else
            {
                $keys = array_keys($this->identityCreated);

                // get primary key
                $pri = $keys[0];

                // get id
                $id = $this->identityCreated[$pri];

                // reset
                $this->identityCreated = [];
            }

            if (!is_null($id))
            {
                // run delete
                $delete = DB::table($this->table)->delete($pri . ' = ?', $id);

                if ($delete->ok)
                {
                    return true;
                }

                return false;
            }
            
            $error = 'Primary KEY #{'.$pri.'} not found in rules';

            throw new \Exception($error);

            return false;
        }

        return false;
    }

    // has method
    public function _has($key)
    {
        if ($this->hasProperty($key, $prop))
        {
            // check value
            $val = isset($prop['value']) ? $prop['value'] : null;

            if ($val !== null)
            {
                return true;
            }
        }

        return false;
    }

    // fetch record
    public function __fetch()
    {
        if ($this->isOk())
        {
            static $promise;

            if (is_null($this->table))
            {
                $this->getTable($table);
                $this->table = $table;
            }

            if (is_null($promise))
            {
                // get promise
                $promise = new DBPromise();
            }

            $promise->table = $this->table;

            if (count($this->identityCreated) == 0)
            {
                // get primary key
                $promise->getTableInfo($pri);

                // get id
                $id = $this->getRule($pri);
            }
            else
            {
                $keys = array_keys($this->identityCreated);

                // get primary key
                $pri = $keys[0];

                // get id
                $id = $this->identityCreated[$pri];

                // reset
                $this->identityCreated = [];
            }

            if (!is_null($id))
            {
                $object = $this->currentObject;
                $object->{$pri} = $id;

                // run get
                $get = DB::table($this->table)->get($object);

                return $get;
            }
            elseif ($pri != null)
            {
                $get = DB::table($this->table)->get($this->currentObject);

                return $get;
            }
            
            $error = 'Primary KEY #{'.$pri.'} not found in rules';

            throw new \Exception($error);

            return false;
        }

        return false;
    }

    // listen for then
    public function then($callback)
    {
        $begin = array_shift($this->transactions);

        if ($begin['return'])
        {
            // get arguments
            $const = [];
            Route::getParameters($callback, $const, $begin['args']);

            call_user_func_array($callback, $const);
        }
    }

    public function asDefault()
    {
        if (!is_null(self::$modelInstance))
        {
            self::$modelInstance->__rules__ = $this->__rules__;
        }

        return $this;
    }

    // create rule
    protected function createRule(&$object)
    {
        static $post;

        if (is_null($post))
        {
            $post = new HttpPost();
        }

        return new class($object, $post)
        {
            public $rules = [];
            protected $model;
            private $flags = [];
            private $postData = null;
            private $ruleAppliedGlobally = false;

            // constructor
            public function __construct(&$model, $post)
            {
                $this->model =& $model;
                $this->postData = $post;
            }

            // call magic method
            public function __call($key, $args)
            {
                if ($key == 'customErrors')
                {
                    $this->model->customErrors = $args[0];
                }
                elseif ($key == 'onSuccess')
                {
                    array_unshift($args, $this->rules);
                    call_user_func_array([$this->model, $key], $args);
                }
                elseif ($key == 'flags' && is_array($args[0]))
                {
                    $this->flags = $args[0];
                }
                elseif ($key == 'allow_form_input' && count($args) == 0)
                {
                    if (!$this->postData->isEmpty())
                    {
                        $data = $this->postData->data();

                        $files = $this->postData->file();

                        array_each(function($value, $key){
                            $this->rules[$key] = [
                                'default' => null,
                                'rule' => null,
                                'value' => $value
                            ];
                        }, $data);

                        if (is_array($files) && count($files)>0)
                        {
                            array_each(function($value, $key){
                                if (isset($value['error']) && $value['error'] == 0)
                                {
                                    $this->rules[$key] = [
                                        'default' => null,
                                        'rule' => null,
                                        'value' => $value
                                    ];
                                }
                            }, $files);
                        }

                        $data = null;
                    }
                }
                elseif ($key == 'apply_global_rule')
                {
                    $rule = $args[0];
                    $this->ruleAppliedGlobally = true;
                    array_each(function($val, $key) use (&$rule){
                        $this->rules[$key]['rule'] = $rule;
                    }, $this->rules);
                }
                elseif ($key == 'except' && $this->ruleAppliedGlobally)
                {
                    if (count($args) > 0)
                    {
                        array_walk($args, function($key){
                            $this->rules[$key]['rule'] = null;
                        });
                        $this->ruleAppliedGlobally = false;
                    }
                }
                else
                {
                    $rule = $args[0];

                    if ($rule[0] == '@')
                    {
                        $tag = substr($rule, 1);
                        if (isset($this->flags[$tag]))
                        {
                            $rule = $this->flags[$tag];
                        }
                    }

                    $this->rules[$key] = [
                        'default' => isset($args[1]) ? $args[1] : null,
                        'rule' => $rule
                    ];
                }

                return $this;
            }

            // seter magic method
            public function __set($name, $val)
            {
                if (strtoupper($name) == 'FLAG_REQUIRED_IF')
                {
                    $this->model->flagRequest = strtoupper($val);
                }
                else
                {
                    $this->rules[$name] = [
                        'default' => $val,
                        'rule' => null
                    ];
                }
            }
        };
    }

    // use rule
    public function useRule($name, $object=null)
    {
        $name = ucfirst($name);

        $current = $this->currentObject;

        if (is_null(self::$modelInstance))
        {
            self::$modelInstance = $this;
        }

        if (is_null($current))
        {
            $current = $this;
        }

        $class = get_class($current);

        if (!isset(self::$useRulesCreated[$name.'/'.$class]))
        {
            $ref = new \ReflectionClass($class);

            $objNew = $ref->newInstanceWithoutConstructor();
            // create model rule
            $objNew->usingRule = true;

            $method = 'set'.$name;

            if (method_exists($objNew, $method))
            { 
                $createRules = $this->createRule($objNew);

                $url = \ApiManager::$getUrl;

                if (!is_null($object))
                {
                    array_unshift($url, $object);
                }

                array_unshift($url, $createRules);

                // get parameters
                Bootloader::$instance->getParameters($objNew, $method, $const, $url);

                // create current object
                $objNew->currentObject = $objNew;

                // call method
                call_user_func_array([$objNew, $method], $const);

                // pull from storage
                self::pullFromStorage($createRules);

                // push object
                $objNew->pushObject($object, $createRules->rules);

                // save rules.
                $objNew->__rules__ = $createRules->rules;

                // create current object
                $objNew->currentObject = $objNew;

                // create rule.
                if (count($createRules->rules) > 0)
                {
                    // listen for http_request
                    $objNew->listenForHttpRequest();
                }

                self::$useRulesCreated[$name.'/'.$class] = $objNew;
            }
            else
            {
                throw new \Exception('Method '. $method . ' doesn\'t exist. Please check rules.');
            }
        }
        else
        {
            $objNew = self::$useRulesCreated[$name.'/'.$class];
        }

        return $objNew;
    }

    // push object
    public function pushObject($object=null, &$rules=[])
    {
        if (!is_null($object) && !is_string($object))
        {
            // make object
            $object = is_array($object) ? toObject($object) : $object;

            if (count($rules) > 0)
            {
                // ilterate
                array_map(function($key) use (&$object, &$rules)
                {
                    if (method_exists($object, 'row'))
                    {
                        $row = (array) $object->row();

                        if (isset($row[$key]))
                        {
                            $rules[$key]['default'] = $row[$key];
                        }
                    }
                    else
                    {
                        if (property_exists($object, $key))
                        {
                            $rules[$key]['default'] = $object->{$key};
                        }
                    }

                }, array_keys($rules));
            }
            else
            {
                if (method_exists($object, 'row'))
                {
                    $row = (array) $object->row();

                    // create
                    foreach ($row as $key => $val)
                    {
                        $this->__rules__[$key]['default'] = $val;
                    }
                }
                else
                {
                    $className = get_class($object);
                    if ($className == 'stdClass')
                    {
                        foreach ($object as $key => $val)
                        {
                            $this->__rules__[$key]['default'] = $val;
                            $this->{$key} = $val;
                        }
                    }
                }
            }
        }
    }

    // pull from storage
    public static function pullFromStorage(&$object)
    {
        $rules = $object->rules;
        $storage = \ApiManager::$storage;

        if (count($storage) > 0)
        {
            foreach ($storage as $key => $val)
            {
                if (isset($rules[$key]))
                {
                    $object->rules[$key]['default'] = $val;
                }
            }
        }
    }

    // is ok
    public function isOk()
    {
        $get = $this->errors['GET'];
        $post = $this->errors['POST'];

        if (count($get) > 0 || count($post) > 0)
        {
            return false;
        }

        return true;
    }

    // listen for error
    public function onError($title)
    {
        static $post;
        static $get;

        // get POST data
        if (is_null($post))
        {
            $post = new HttpPost;
        }

        // get GET data
        if (is_null($get))
        {
            $get = new HttpGet;
        }

        $error = $this->getError($title);

        if (is_array($error))
        {
            if (!$post->isEmpty() || !$get->isEmpty())
            {
                $errors = is_array($error[0]) ? implode("<br>", $error[0]) : $error[0];
                return '<div style="align-self: flex-start;width: 100%;text-align: left;"><small class="text text-danger" style="text-align:left; margin-top:10px;">'.$errors.'</small></div>';
            }
        }

        return null;
    }

    // set get error
    public function setGetError($key, $error)
    {
        $this->errors['GET'][$key] = $error;

        // return object
        return $this;
    }

    // set post error
    public function setPostError($key, $error)
    {
        $this->errors['POST'][$key] = $error;

        // return object
        return $this;
    }

    // set errors
    public function setErrors($data)
    {   
        $data = is_object($data) ? toArray($data) : $data;
        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($data['errors']) && is_array($data['errors']))
        {
            $this->errors[$method] = $data['errors'];
        }
        elseif (isset($data['error']) && is_array($data['error']))
        {
            $this->errors[$method] = $data['error'];
        }
        else
        {
            $this->errors[$method] = $data;
        }
    }

    // is passed
    public function isPassed($var, $flags, &$errors)
    {
        
        if (!is_object($var) && !is_array($var))
        {
            // set data
            $data = ['isPassed' => $var];

            // set flag
            $flag = ['isPassed' => $flags];

            // get validator
            $validator = Plugins::validator($data);

            // empty dump
            $errors = [];

            // run validator
            $validate = $validator->validate($flag, $errors);

            if ($validate)
            {
                if (isset($this->onSuccessBox[$var]))
                {
                    // call callback function
                    if (is_callable($this->onSuccessBox[$var]))
                    {
                        $value = '';

                        $data = [];
                        $data[] = $var;
                        $data[] = $post;
                        $data[] = &$value;

                        call_user_func_array($this->onSuccessBox[$var], $data);

                        if (strlen($value) > 0)
                        {
                            $this->__rules__[$var]['value'] = $value;   
                        }
                    }
                }

                return true;
            }

            $errors = $errors['isPassed'];
        }

        // return false
        return false;
    }

    // get table
    private function getTable(&$table=null)
    {
        // check property
        if (property_exists($this->currentObject, 'table'))
        {
            $table = $this->currentObject->table;
        }
        else
        {
            // get table
            $className = get_class($this->currentObject);

            // get class name
            $className = substr($className, strpos($className, '\\')+1);

            // return table name
            $table = $className;
        }
    }

    // on success
    public function onSuccess($rules, $callback)
    {
        $rules = array_keys($rules); 

        // get last
        if (count($rules) > 0)
        {
            $last = end($rules);

            // save request
            $this->onSuccessBox[$last] = $callback;
        }
        return $this;
    }

    // match if not all
    public function ifNotAll()
    {
        // get table;
        $this->getTable($table);

        // get arguments
        $args = func_get_args();

        // build query
        $where = [];

        array_map(function($val) use (&$where)
        {
            $where[$val] = $this->getRule($val);

        }, $args);

        // run get request
        $check = DB::table($table)->get($where);

        if ($check->rows == 0)
        {
            return true;
        }

        // clean up
        $args = null;

        // failed
        return false;
    }

    // match if not all
    public function ifNotSome()
    {
        // get table;
        $this->getTable($table);

        // get arguments
        $args = func_get_args();

        // build query
        $where = [];

        array_map(function($val) use (&$where)
        {
            $where[$val] = $this->getRule($val);

        }, $args);

        // run get request
        $check = DB::table($table)->get($where, 'OR');

        if ($check->rows == 0)
        {
            return false;
        }

        // clean up
        $args = null;

        // success
        return true;
    }

    // exists
    public function exists(&$check=null)
    {
        // get table;
        $this->getTable($table);

        if ($this->isOk())
        {
            // run check
            $check = DB::table($table)->get($this);

            if ($check->rows > 0)
            {
                return true;
            }
        }

        return false;
    }

    // get errors
    public function getErrors()
    {
        return $this->getError();
    }

    // has property
    public function hasProperty($name, &$val)
    {
        $storage = \ApiManager::$storage;

        if (isset($storage[$name]))
        {
            $val = $storage[$name];

            return true;
        }
        else
        {
            if (isset($this->__rules__backup[$name]))
            {
                $val = $this->getBackupRule($name);

                return true;
            }
            elseif (isset($this->__rules__[$name]))
            {
                $val = $this->__rules__[$name];

                return true;
            }
        }

        return false;
    }

    // get error
    public function getError($name=null)
    {
        $get = $this->errors['GET'];
        $post = $this->errors['POST'];

        $post = !is_array($post) ? [] : $post;

        // merge them
        $array = array_merge($get, $post);

        if (!is_null($name))
        {
            if (isset($array[$name]))
            {
                return $array[$name];
            }
            
            return "Key '$name' not found.";
        }

        return $array;
    }

    // get values
    // used by the database.
    public function rulesHasData()
    {
        $data = [];
        $rules = $this->__rules__;

        // read rules
        foreach ($rules as $key => $config)
        {
            if (isset($config['value']))
            {
                $data[$key] = $config['value'];
            }
            elseif (isset($config['default']))
            {
                $data[$key] = $config['default'];
            }

            if (isset($data[$key]) && is_null($data[$key]))
            {
                unset($data[$key]);
            }
        }

        // clean up
        $rules = null;

        // return data
        return $data;
    }

    // get values mirror
    public function getData()
    {
        return $this->rulesHasData();
    }

    // factory settings
    public function toFactory($var, $val)
    {
        $this->{$var} = $val;

        return $this;
    }
}
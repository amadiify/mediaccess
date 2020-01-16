<?php

namespace Moorexa\DB;
use utility\Classes\BootMgr\Manager as BootMgr;

/*
 * @package Orm Reciever for DB Class 
 * @author wekiwork inc. 
 **/
 
class ORMReciever 
{
	// using singleton pattern
	private static $class_instance = [];

	// methods chained
	private $method_called = [];

	// db instance
	protected $instance = null;

	// id
	protected $id = 1;

	// table name
	protected $tableName = null;

	// pause 
	protected $pauseGo = false;

	// get instance
	public static function getInstance(&$db_instance, $id=1, $orm=null)
	{
		if ($orm == null)
		{
			$caller = $db_instance->table;

			if (!isset(self::$class_instance[$caller]))
			{
				self::$class_instance[$caller] = new ORMReciever;
			}

			// set db instance
			self::$class_instance[$caller]->instance = $db_instance;

			// clean up method called
			self::$class_instance[$caller]->method_called = [];
			self::$class_instance[$caller]->id = $id;

			// return instance
			return self::$class_instance[$caller];
		}
	}	

	// __call magic method
	public function __call($method, $data)
	{
		return self::pushCall($method, $data, $this);
	}

	// get magic method
	public function __get($name)
	{
		if (count($this->method_called) > 0)
		{
			return $this->executeRequest()->{$name};
		}
	}

	// __callStatic magic method
	public static function __callStatic($method, $data)
	{
		return self::pushCall($method, $data);
	}

	// lazy loader
	public function lazyLoader($callback)
	{
		$this->pauseGo = true;
		$tableName = null;

		// get arguments
		$args = func_get_args(); // get arguments
		$argStartsFrom = 1; // default pointer

		if (is_string($callback))
		{
			$tableName = $callback; // set table name
			$callback = $args[1]; // set callback function
			$argStartsFrom = 2; // shift pointer forward
		}
		
		$args = array_splice($args, $argStartsFrom);
		array_unshift($args, $this->instance);

		switch(gettype($callback))
        {
			case 'array':

				list($className, $method) = $callback;
                
                if (is_object($className))
                {
					$getClass = get_class($className);

					$this->instance->table = $getClass;

					if (strpos($getClass, '\\') !== false)
					{
						$this->instance->table = substr($getClass, strrpos($getClass, '\\') + 1);
					}

					if (property_exists($className, 'table'))
					{
						$this->instance->table = \Moorexa\DB::getTableName($className->table);
					}

					if ($tableName != null)
					{
						$this->instance->table = \Moorexa\DB::getTableName($tableName);
					}

					$this->instance->classUsingLazy = $className;

                    call_user_func_array([$className, $method], $args);

                    return $this->go();
                }

				$className = '\\' . ltrim($className, '\\');

				if (strpos($className, '\\') !== false)
				{
					$this->instance->table = substr($className, strrpos($className, '\\') + 1);
				}

				$instance = BootMgr::singleton($className);
				
				if (property_exists($instance, 'table'))
				{
					$this->instance->table = \Moorexa\DB::getTableName($instance->table);
				}

				if ($tableName != null)
				{
					$this->instance->table = \Moorexa\DB::getTableName($tableName);
				}

				$this->instance->classUsingLazy = $instance;

				call_user_func_array([$instance, $method], $args);

                return $this->go();

			case 'object':
				if (is_callable($callback))
				{
					if ($tableName != null)
					{
						$this->instance->table = \Moorexa\DB::getTableName($tableName);
					}

					call_user_func_array($callback, $args);

					return $this->go();
				}
		}
	}

	// push call
	private static function pushCall($method, $data, $instance=null)
	{
		if (is_null($instance))
		{
			static $dbinstance;

			if (is_null($dbinstance))
			{
				$dbinstance = new \Moorexa\DB();
			}

			$instance = new self;
			$debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
			$readfile = new \SplFileObject($debug['file']);
			$before = [];

			foreach ($readfile as $i => $line)
			{
				if ($i == ($debug['line']-1))
				{
					// get table
					$line = trim($line);
					preg_match('/([_a-zA-Z0-9]+?)[:]{2}/', $line, $match);
					if (isset($match[1]))
					{
						$dbinstance->table = \Moorexa\DB::getTableName($match[1]);
						$instance->instance = $dbinstance;
					}
					else
					{
						for ($x=$i; $x != 0; $x--)
						{
							$line = isset($before[$x]) ? $before[$x] : null;
							if (preg_match('/([_a-zA-Z0-9]+?)[:]{2}/', $line, $match) == true)
							{
								$dbinstance->table = \Moorexa\DB::getTableName($match[1]);
								$instance->instance = $dbinstance;
								break;
							}
						}
					}
					break;
				}
				else
				{
					$before[] = $line;
				}
			}
			$readfile=null;
			$before = null;
		}

		if ($method != 'go' && !\Moorexa\DBPromise::hasFetchMethod($method))
		{
			$instance->method_called[] = ['method' => $method, 'args' => $data];

			if (!$instance->pauseGo)
			{
				$debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, ($instance->id + 1));

				$file = $debug[$instance->id];

				if (isset($file['file']))
				{
					$path = $file['file'];

					// read file
					$readfile = new \SplFileObject($path);
					foreach ($readfile as $index => $line)
					{
						if ($index == ($file['line']-1))
						{
							$line = preg_replace('/[\s]/', '', $line);
							$line = preg_replace('/(\$)(\w*)(->)/', '@', $line);

							$line = explode('->', $line);

							array_walk($line, function($e, $i) use (&$line){
								if (preg_match('/([a-zA-Z_0-9]+)[(]/', $e, $m))
								{

									$line[$i] = str_replace($m[0], '->'.$m[0], $e);
								}
								else
								{
									$line[$i] = '@'.$e;
								}
							});

							$line = implode('', $line);

							if (isset($file['type']))
							{
								$exp = explode($file['type'], $line);

								if ($file['type'] == '::')
								{
									$line = strstr($line, '->'.$method);

									if (preg_match('/(->)([^(]+)[(]/', $line) == true)
									{
										$exp = explode('->', $line);
									}
								}

								foreach ($exp as $i => $e)
								{
									$quote = preg_quote("{$method}(", '/');

									if (preg_match("/^($quote)/", $e) == true)
									{
										
										if (strrpos($e, ');') !== false)
										{
											return $instance->executeRequest();
										}
										
									}
								}

								break;
							}
						}
					}
				}
			}

			$readfile = null;
		}
		else
		{
			if (\Moorexa\DBPromise::hasFetchMethod($method))
			{
				$instance->method_called[] = ['method' => $method, 'args' => $data];
			}

			return $instance->executeRequest();
		}

		return $instance;	
	}

	// execute request
	public function executeRequest()
	{
		$process = true;

		if (!isset($this->instance->table))
		{
			$process = false;
		}

		// execute here.
		if ($process)
		{
			$instance = &$this->instance;
			$method_called = $this->method_called;

			foreach ($method_called as $arr)
			{
				if (is_object($instance))
				{
					$instance = call_user_func_array([$instance, $arr['method']], $arr['args']);
				}
			}

			$this->method_called = [];

			if (is_object($instance))
			{
				$class = get_class($instance);

				if (is_string($class) && (strtolower($class) != 'moorexa\dbpromise'))
				{
					$send = $instance->go();
				}
				else
				{
					$send = $instance;
				}

				return $send;
			}

			return $this;
		}

		return $this;
	}
}

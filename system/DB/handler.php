<?php

namespace Moorexa;
use utility\Classes\BootMgr\Manager as BootMgr;
/**
 *
 * @package Moorexa Database connection handler
 * @author  Amadi Ifeanyi
 * @version 0.0.1
 **/

class DatabaseHandler extends Main
{
	// database connection vars
	protected $dbvars = [];

	// active connection
	private static $active = [];

	// static vars
	protected static $_vars = [];

	// connect with
	public static $connectWith;

	// default source
	public static $default;

	// current running driver
	public static $driver;

	// manage total requests
	public static $totalRequests;

	// cache failed requests
	public static $failedRequests = [];

	// extablish a new connection
	public static $newConnection = false;

	// save connection
	public static $connection = [];

	// db set
	public static $dbset = false;	

	// force prod
	public static $forceProductionMode = false;

	// app mode
	private static $isOnline = false;

	// preloaded tables
	public static $preloadedTables = null;

	// database tables
	public static $databaseTables = [];

	// channel opened 
	public static $channelOpened = null;

	// database prefix
	public static $prefix = null;

	// db-handler method
	protected function dbHandler()
	{
		// Grab Default Settings.
		DatabaseHandler::$_vars = $this->dbvars->vars;
	}

	// default settings
	protected function dbHandlerDefault()
	{
		// get live key and development key
		list($dev, $live) = array_values($this->dbvars->default);

		// check if app is online.
		switch ($this->dbvars->isonline && strlen($live) > 1)
		{
			case true:
				// online 
				DatabaseHandler::$connectWith = $live;
				DatabaseHandler::$default = $live;
				DatabaseHandler::$dbset = true;
				// app is now live
				self::$isOnline = true;
			break;

			case false:
				// offline 
				DatabaseHandler::$connectWith = $dev;
				DatabaseHandler::$default = $dev;
				DatabaseHandler::$dbset = true;
			break;
		}
	}

	// load domain
	public function domain(string $domain, array $config)
	{
		Route::domain($domain, function() use ($config)
		{
			foreach ($config as $mode => $conf)
			{
				DatabaseHandler::$connectWith = $conf;
				DatabaseHandler::$default = $conf;
				DatabaseHandler::$dbset = true;

				switch (strtolower($mode))
				{
					case 'live':
						// app is now live
						self::$isOnline = true;
					break;
				}
			}
		});

		return $this;
	}

	// load channel
	public function channel(\closure $callback)
	{
		// create channel anonymous class
		$channel = new class($callback)
		{
			private $callback; // callback function passed;
			private $table; // table calling
			private $method; // method calling
			public  $canContinue = null;
			private $query;
			
			// load constructor
			public function __construct(&$callback=null)
			{
				$this->callback = $callback;
			}

			// build object
			public function buildObject(string $method, DB &$query)
			{
				$this->canContinue = null;
				$this->method = $method;
				$this->table = $query->table;
				$this->query = &$query;

				// call callback method
				return call_user_func($this->callback, $this);
			}

			// check for method
			public function has(string $method, $fallthrough = null)
			{
				$methods = explode('|', $method);

				foreach ($methods as $method)
				{
					if ($method == $this->method)
					{
						if (is_array($fallthrough))
						{
							list($class, $method) = $fallthrough;

							$class = BootMgr::singleton($class);

							if (method_exists($class, $method))
							{
								call_user_func_array([$class, $method], [$this->table, &$this->query, &$this]);
							}
						}
						elseif (is_callable($fallthrough) && !is_null($fallthrough))
						{
							call_user_func_array($fallthrough, [$this->table, &$this->query, &$this]);
						}
						else
						{
							return true;
						}
					}
				}

				return false;
			}
			
			// check for a table
			public function table(string $table, $fallthrough = null)
			{
				$tables = explode('|', $table);

				foreach ($tables as $table)
				{
					if ($table == $this->table)
					{
						if (is_array($fallthrough))
						{
							list($class, $method) = $fallthrough;

							$class = BootMgr::singleton($class);

							if (method_exists($class, $method))
							{
								call_user_func_array([$class, $method], [$this->table, &$this->query, &$this, $this->method]);
							}
						}
						elseif (is_callable($fallthrough) && !is_null($fallthrough))
						{
							call_user_func_array($fallthrough, [$this->table, &$this->query, &$this, $this->method]);
						}
						else
						{
							return true;
						}
					}
				}

				return false;
			}

			// check if bind has data
			public function bindHas(string $key)
			{
				if (isset($this->query->bind[$key]))
				{
					return true;
				}

				return false;
			}

			// set bind
			public function setBind(string $key, $value)
			{
				$this->query->setBind($key, $value);
			}

			// lock execution
			public function lock()
			{
				$this->canContinue = false;
			}
		};

		// open channel
		self::$channelOpened = $channel;
	}

	// call channel
	public static function callChannel(string $method, DB &$query, &$canContinue=true)
	{
		if (self::$channelOpened !== null)
		{
			$canContinue = call_user_func_array([self::$channelOpened, 'buildObject'], [$method, &$query]);

			if (self::$channelOpened->canContinue !== null)
			{
				$canContinue = self::$channelOpened->canContinue;
			}
		}
	}

	// preload tables
	protected function preloadTables(array $config)
	{

	}

	// load preloaded tables
	public static function loadPreloadedTables($tables)
	{
		$newList = [];

		foreach ($tables as $index => $table)
		{
			if (is_string($index))
			{
				$newList[] = $index;
			}
			else {
				$newList[] = $table;
			}
		}

		self::$preloadedTables = $newList;
		self::$databaseTables = $tables;

		return $newList;
	}

	// create connection
	private static function createConnection( $source, $vars )
	{
		// manage production migration
		$continue = false;

		if (self::$isOnline || self::$forceProductionMode)
		{
			$continue = true;
		}

		$action = null;

		if (strrpos($source, '@') !== false)
		{
			$action = substr($source, strpos($source, '@')+1);
			$source = substr($source, 0, strpos($source, '@'));
		}

		// create connection or serve existing
		switch (!isset(self::$connection[$source]) || $continue == true)
		{
			// create new connection
			case true:
				// check if db source exists
				switch (isset($vars[$source]))
				{
					// load configuration from database.php
					case true:
						$settings = $vars[$source];
						// has production array config or string
						if (isset($settings['production']))
						{
							// continue loading production config?
							if ($continue)
							{	
								self::settingsVars($settings, $vars[$source]);
							}
						}

						// action call
						if (!is_null($action))
						{
							if (isset($settings[$action]))
							{
								$type = gettype($settings[$action]);

								if ($type == 'array')
								{
									self::settingsVars($settings, $vars[$source], $action);
								}
							}
						}

						// use attributes
						$useAttribute = isset($settings['attributes']) ? $settings['attributes'] == true ? true : false : false;
						// default handler
						$handler = isset($settings['handler']) ? strtolower($settings['handler']) : 'pdo';
						// extract dsn
						$dsn = $settings['dsn'];
						// save driver.
						self::$driver = $settings['driver'];
						// set current connection
						self::$connectWith = $source;
						// manage dsn
						preg_match_all('/[\{]\s{0}(\w{1,}\s{0})\s{0}[\}]/', $dsn, $matches);
						// walk
						array_walk($matches[1], function($val) use (&$dsn, &$settings)
						{
							if (isset($settings[$val]))
							{
								$dsn = str_replace('{'.$val.'}', $settings[$val], $dsn);
							}
						});

						// get socket if running development server.
						if ((!isset($_SERVER['REQUEST_METHOD']) || isset($_SERVER['REQUEST_QUERY_STRING'])) && !isset($settings['unix_socket']))
						{
							if (!self::$isOnline && !self::iswin())
							{
								$socks = shell_exec('netstat -ln | grep '.$settings['driver']);
								$socks = trim(substr($socks, strpos($socks, '/')));
								if (mb_strlen($socks) > 1)
								{
									$dsn .= ';unix_socket='.$socks;
								}
								// #clean up
								$socks = null;
							}
						}

						// get and set options
						$options = [];

						if ($handler == 'pdo')
						{
							$options = [
								\PDO::ATTR_CASE => \PDO::CASE_NATURAL,
								\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
								\PDO::ATTR_EMULATE_PREPARES => true
							];
						}

						// flattern options to single array
						if (isset($settings['options']))
						{
							$options = array_merge($options, $settings['options']);
						}

						// set prefix
						self::$prefix = $settings['prefix'];

						try
						{
							// make connection
							switch ($handler)
							{
								// pdo
								case 'pdo':
									$obj = new \PDO($dsn, $settings['user'], $settings['password']);
									// set attributes
									if ($useAttribute)
									{
										array_walk($options, function($val, $attr) use (&$obj){
											$obj->setAttribute($attr, $val);
										});
									}
								break;

								// mysql
								case 'mysql':
								case 'mysqli':
									$obj = new \mysqli($settings['host'], $settings['user'], $settings['password'], $settings['dbname']);
									// error occured?
									if ($obj->connect_error)
									{
										throw new \Exceptions\Database\DatabaseException("Error Connecting to database.");
									}
									
									mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
									$obj->set_charset($settings['charset']);
								break;
							}

							// save connection and return handle
							self::$active[$settings['driver']] = $obj;
								
							self::$connection[$source] = $obj;

							// connection created. create connection called
							BootMgr::method(DatabaseHandler::class . '@createConnection', null);
							BootMgr::methodGotCalled(DatabaseHandler::class . '@createConnection', BootMgr::singleton(DatabaseHandler::class));

							return $obj;
						}
						catch(\PDOException $e)
						{
							throw new \Exceptions\Database\DatabaseException($e->getMessage());
						}
					break;	

					// throw exception
					case false:
						throw new \Exceptions\Database\DatabaseException('Invalid Database Source. Not found in DB VARS. Database connection failed.');
				}
			break;

			// serve existing
			case false:
			    return self::$connection[$source];
		}
	}

	// return active connection
	public static function active($con = false)
	{	
		return self::createConnection( $con, Bootloader::getProtected('_vars'));
	}

	// return configuration settings
	public static function connectionConfig($source, $return = null)
	{
		if (strrpos($source, '@') !== false)
		{
			$source = substr($source, 0, strpos($source, '@'));
		}

		$vars = Bootloader::getProtected('_vars');

		if (isset($vars[$source]))
		{
			$settings = $vars[$source];

			if (!is_null($return))
			{
				return $settings[$return];
			}

			return $settings;
		}

		return false;
	}

	// read only
	public static function readvar($source)
	{
		if (strrpos($source, '@') !== false)
		{
			$source = substr($source, 0, strpos($source, '@'));
		}

		if (isset(self::$_vars[$source]))
		{
			return self::$_vars[$source];
		}

		return null;
	}

	public static function usePDO($source)
	{

		if (strrpos($source, '@') !== false)
		{
			$source = substr($source, 0, strpos($source, '@'));
		}

		$var = self::readvar($source);

		$usepdo = true;

		if (isset($var['handler']) && strtolower($var['handler']) != 'pdo')
		{
			$usepdo = false;
		}

		// clean up
		$var = null;

		return $usepdo;
	}

	// for development.
	private static function iswin()
    {
        if (strtolower(PHP_SHLIB_SUFFIX) == 'dll')
        {
            return true;
        }

        return false;
	}
	
	// get settings vars
	private static function settingsVars(&$settings, $vars, $action = 'production')
	{
		if (isset($vars[$action]))
		{
			$prod = $vars[$action];

			// get type
			switch (gettype($prod))
			{
				// is array?
				case 'array':
					array_walk($prod, function($val, $key) use (&$settings){
						if (isset($settings[$key]))
						{
							// remove key
							unset($settings[$key]);
						}
					});

					unset($settings[$action]);
					$settings = array_merge($settings, $prod);
				break;

				// is string?
				case 'string':
					$vars = self::readvar($prod[$action]);

					if ($vars !== null && !isset($vars[$action]))
					{
						$settings = $vars;
					}
					else
					{
						self::settingsVars($settings, $vars);
					}
				break;
			}

			// clean up
			$prod = null;
		}
	}
}

// END class 

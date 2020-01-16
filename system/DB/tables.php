<?php

namespace Moorexa\DB;

use Moorexa\DB;
use Moorexa\Structure;

class Table
{
	private $__tabledata = [];
	private $tableinfo = [];
	private $jobdone = false;
	public  $tableName = "";

	// @param string
	public static function exists($table)
	{
		$table = DB::getPrefix() . $table;

		// serve db
		$db = DB::pdo();

		if ($db !== null)
		{
			$check = $db->query("show tables");

			if (method_exists($check, 'rowCount') && $check->rowCount() > 0)
			{	
				$exists = false;

				foreach ($check->fetchAll() as $index => $row)
				{
					if (strcmp($row[0], $table) === 0)
					{
						$exists = true;

						break;		
					}
				}

				if ($exists)
				{	
					return true;
				}
			}
		}
	
		// not found, return false..
		return false;
	}

	// @param string, any
	public static function create(string $tablename, $callback)
	{
		if (is_callable($callback))
		{
			$tablename = DB::getPrefix() . $tablename;

			$default = \Moorexa\DatabaseHandler::$default;
			$struct = new Structure();
			$struct->tableName = $tablename;
			$struct->databaseSource = $default;
	        $struct->driver = \Moorexa\DatabaseHandler::connectionConfig($default, 'driver');

	        $db = null;

	        try
	        {
	            $db = DB::apply($default);
	        }
	        catch(Exception $e)
	        {
	            self::out($ass->ansii('red').$e->getMessage());
	        }

	        $db->table = $tablename;

			// call closure func
			call_user_func($callback, $struct);

			if (count($struct->buildQuery) > 0 || $struct->sqlString != "")
            {
                $struct->save;
            }

            $total = count($struct->sqljob);
            $rows = 0;
            $migration = HOME . "lab/Sql/".ucfirst($struct->driver)."/".$struct->databaseSource.'.sql';

            if (count($struct->sqljob) > 0)
            {
                foreach ($struct->sqljob as $i => $sql)
                {
                    if (strlen($sql) > 4)
                    {
                        try
                        {
                            $run = $db->sql($sql);
                            $rows += $run->rows;

                            if ($run->ok)
                            {
                                $now++;
                            }
                        }
                        catch(Exception $e)
                        {
                            // roll back
                            
                            $content = trim(file_get_contents($migration));
                            $ending = strrpos($content, $sql . ";");

                            $length = strlen($sql . ";");
                            $content = substr_replace($connect, '', $ending, $length+1);
                            file_put_contents($migration, $content);
                        }
                    }
                }
            }

            if (isset($struct->promises[$tablename]))
            {
                $promise = $struct->promises[$tablename];
                $callback = $promise[0];
                $db = $promise[1];
                $db->table = $tablename;

                call_user_func($callback, 'complete', $db);
            }

            return true;
		}

		return false;
	}	

	public function then($callback)
	{
		if (is_callable($callback))
		{
			$status = false;
			$table = \Moorexa\promise_callback(['data' => [], 'array' => [], 'rows' => 0, 'rows' => 0]);

			if ($this->jobdone)
			{
				$status = true;
				$table = DB::table($this->tableName);
			}

			return call_user_func($callback, $status, $table);
		}

		return false;
	}


	public function __get($name)
	{
		$this->__tabledata[$name] = [];
		$this->lastAdded 		  = $name;

		return $this;
	}

	// @param string
	public static function drop(string $tablename, $callback=null)
	{
		if (self::exists($tablename))
		{
			$tablename = DB::getPrefix() . $tablename;

			// get structure
			$struct = new Structure();
			$struct->tableName = $tablename;
			$struct->dropTables[$tablename] = true;

			$drop = function($drop, $records)
			{
				// drop table
				$drop();
			};

			if (is_callable($callback))
			{
				$drop = $callback;
			}

			// drop table
			$struct->drop($drop);
			$pdo = DB::pdo();

			if (count($struct->sqljob) > 0)
			{
				foreach ($struct->sqljob as $i => $sql)
				{
					if ($pdo !== null)
					{
						$pdo->query($sql);
					}
				}

				if ($pdo !== null)
				{
					return true;
				}
			}
		}

		return false;
	}

	// @param string, string
	public static function check ( $rowdata,  $tableinfo)
	{
		$toarr = explode('/', $tableinfo);

		// check
		$check = DB::table($toarr[0])->get()->where( $toarr[1] . ' = ? ')->bind($rowdata);
		$check->go();

		if ($check->rows == 0)
		{
			return false;
		}

		return true;
	}

	// @param string <table name>
	// returns a function
	public static function info($table)
	{
		if (self::exists($table))
		{
			$table = DB::getPrefix() . $table;
			
			$raw = DB::sql("show fields from $table");

			if ($raw->rows > 0)
			{
				return function($res = null) use ($raw)
				{
					if ($res !== null)
					{
						$found = [];

						while($col = $raw->array())
						{
							if (in_array(strtoupper($res), array_values($col->row())) || in_array(strtolower($res), array_values($col->row())))
							{
								$found[] = (object) $col->row();
							}
						}

						if (count($found) > 1)
						{
							return (object) $found;
						}
						else
						{
							if (isset($found[0]))
							{
								return (object) $found[0];	
							}
							else
							{
								return (object) $found;
							}
							
						}
					}
					else
					{
						$structure = [];

						while ($s = $raw->array())
						{
							$structure[] = $s->row();
						}

						return $structure;
					}
				};
			}
			else
			{
				return (object)[];
			}
		}
		else
		{
			return function($res)
			{
				$data = "Table doesn't exists";
				return (object)['status' => $data];
			};
		}
	}

	// return table schema
	public function schema()
	{
		return $this->__tabledata;
	}
}

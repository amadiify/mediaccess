<?php

namespace Moorexa;

/**
 * @package Moorexa DB Table Schema
 * @version 0.0.1
 * @author Ifeanyi Amadi
 */

class Structure
{
	public $tableName = "";
	public $buildQuery = [];
	public $seeded = 0;
	public $lastsuccess = "";
	public $queryInfo = [];
	public static $dbName = ""; 
	public $inserting = false;
	public static $seed = 0;
	public static $last = "";
	public $sqljob = [];
	public $sqlString = "";
	public $driver = "";
	public $databaseSource = "";
	private $datatypes = 'INT,VARCHAR,TEXT,DATE,TINYINT,SMALLINT,MEDIUMINT,INT,BIGINT,DECIMAL,FLOAT,DOUBLE,REAL,BIT,BOOLEAN,SERIAL,DATE,DATETIME,TIMESTAMP,TIME,YEAR,CHAR,VARCHAR,TINYTEXT,TEXT,MEDIUMTEXT,LONGTEXT,BINARY,VARBINARY,TINYBLOB,MEDIUMBLOB,BLOB,LONGBLOB,ENUM,SET,GEOMETRY,POINT,LINESTRING,POLYGON,MULTIPOINT,MULTILINESTRING,MULTIPOLYGON,GEOMETRYCOLLECTION,JSON';
	private $other = '';
	private $number = '';
	public  $promises = [];
	public  $dropTables = [];
	public  $tableOptions = [];
	private $tableOptionsUsed = false;
	public  $createSQL = '';
	public  static $forceSQL = false;

	public function __get($name)
	{
		$save = "";

		if ($name == "table")
		{
			$this->buildQuery = [];
			return $this;
		}
		elseif ($name == "save")
		{
			$_sql = "";

			if ($this->sqlString == "")
			{
				$_sql = "\n"."CREATE TABLE IF NOT EXISTS `{$this->tableName}` (". "\n";

				$firstLine = $_sql;

				foreach ( $this->buildQuery as $index => $query )
				{
					$_sql .= $query . "\n";
				}

				$_sql = rtrim($_sql, ", \n");

				$_sql .= "\n)";
			}
			else
			{
				$_sql = $this->sqlString;
			}

			$_sql = trim($_sql);

			$this->createSQL = $_sql;

			$save = $_sql;
			$build = "";
			$data = "";
			$justContinue = false;

			if (empty($this->databaseSource))
			{
				$this->databaseSource = DatabaseHandler::$connectWith;
			}

			if (empty($this->driver))
			{
				$this->driver = DatabaseHandler::connectionConfig($this->databaseSource, 'driver');
			}

			// migration file
			$migration = HOME."lab/Sql/".ucfirst($this->driver)."/".$this->databaseSource.'.sql';

			if (!file_exists($migration))
			{
				$fh = fopen($migration, 'w+');
				fwrite($fh, '');
				fclose($fh);
			}

			if (file_exists($migration))
			{
				$sql = file_get_contents($migration);

				if (empty($sql))
				{
					$exists = strstr($sql, $_sql);

					if ($exists === false)
					{
						if (strpos($_sql, '-%ques') !== false)
						{
							$_sql = str_replace('-%ques', ',', $_sql);
						}

						$fh = fopen($migration, 'a+');
						fwrite($fh, $_sql . ";\n");
						fclose($fh);

						$this->sqljob[] = $_sql;
					}

				}
				else
				{
					$firstLine = "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (";
					
					// new
					$content = trim($sql);

					// where it all start. get last entry
					$begin = strrpos($content, $firstLine);

					if ($begin !== false)
					{
						// extract content from this point.
						$entry = substr($content, $begin);
						$entry = substr($entry, 0, strpos($entry, ');')).");";

						// we hash both strings and be sure something changed
						$hash_new_entry = md5($_sql . ';');
						$hash_old_entry = md5($entry);

						// now we compare change
						if ($hash_new_entry != $hash_old_entry)
						{
							// things changed.
							// let's find out more.

							// 1# remove create table if not exists from both string
							$entry = str_replace($firstLine, '', $entry);
							$newEntry = str_replace($firstLine, '', $_sql);

							// 2# remove closing braces
							$entry = trim(rtrim($entry, ');'));
							$newEntry = trim(rtrim($newEntry, ')'));

							// 3# remove left padding from strings
							$entry = preg_replace("/\n{1,}(\s*)/","\n", $entry);
							$newEntry = preg_replace("/\n{1,}(\s*)/","\n", $newEntry);

							// 4# get column definition
							preg_match_all("/\w*\s{1,}((.*)?[,]|(.*)?\s*)/", $entry, $entryArray);
							preg_match_all("/\w*\s{1,}((.*)?[,]|(.*)?\s*)/", $newEntry, $newEntryArray);
							
							// create two new empty arrays
							$entryOld = [];
							$entryNew = [];

							// 4.1# organize array with column as key
							$this->___getEntry($entryOld, $entryArray);
							$this->___getEntry($entryNew, $newEntryArray);

							// check new entry size against old entry
							if (count($entryNew) != count($entryOld))
							{
								// ok check if new entry has a larger size
								if (count($entryNew) > count($entryOld))
								{
									$query = [];
									// get what changed first
									$this->___getChanged($entryNew, $entryOld, $query);

									// so we ilterate new entry to check what's on old column
									$entryOldLen = count($entryOld);
									$index = 0;

									// entry new keys
									$newKeys = array_keys($entryNew);

									foreach ($entryNew as $column => $line)
									{
										if ($index >= $entryOldLen)
										{
											$after = isset($newKeys[$index-1]) ? $newKeys[$index-1] : null;
											if ($after !== null)
											{
												$after = " AFTER {$after}";
											}	

											$lineInfo = $line['config'];
											$query[] = "ALTER TABLE `{$this->tableName}` ADD {$column} {$lineInfo}{$after};";
										}
										$index++;
									}

									$this->___addJobAndSave($query, $_sql.';');
								}
								elseif (count($entryNew) < count($entryOld))
								{
									$query = [];
									// get what changed first
									$this->___getChanged($entryNew, $entryOld, $query);
									$this->___addJobAndSave($query, $_sql.';');
								}
							}
							else
							{
								// new entry is smaller or equal
								// se we ilterate old entry to check what's changed
								$query = [];
								$this->___getChanged($entryNew, $entryOld, $query);
								$this->___addJobAndSave($query, $_sql.';');
							}
						}
					}
					else
					{
						if (strpos($_sql, '-%ques') !== false)
						{
							$_sql = str_replace('-%ques', ',', $_sql);
						}

						// push to end and continue
						$fh = fopen($migration, 'a+');
						fwrite($fh, "\n".ltrim($_sql,"\n"). ';');
						fclose($fh);

						$this->sqljob[] = $_sql;
					}
				}
			}

			array_unshift($this->sqljob, $this->createSQL);
		}
		elseif ($name == "drop" || $name == 'appendline')
		{
			if (empty($this->databaseSource))
			{
				$this->databaseSource = DatabaseHandler::$connectWith;
			}

			if (empty($this->driver))
			{
				$this->driver = DatabaseHandler::connectionConfig($this->databaseSource, 'driver');
			}


			// migration file
			$migration = HOME."lab/Sql/".ucfirst($this->driver)."/".$this->databaseSource.'.sql';

			File::appendToEnd("\n".$this->sql.";", $migration);
		}

		return $this;
	}

	// tableInfo
	public function ___tableInfo($name = null)
	{
		if ($name != null)
		{
			$this->tableName = $name;
		}
	}

	// database schema
	public function ___schema($tabledata = null)
	{
		if ($tabledata !== null)
		{
			$tablename = $this->tableName;

			if (is_callable($tabledata))
			{
				$schema = new DB\Table();
				$structure = new Structure();

				call_user_func($tabledata, $schema);

				if (count($schema->__tabledata) > 0)
				{
					$sql = [];

					foreach ($schema->__tabledata as $i => $data)
					{
						$sql[] = $i . ' ' . $data[0].',';
					}

					$structure->tableName = $tablename;
					$structure->buildQuery = $sql;
					$structure->save;

					$job = $structure->sqljob;

					if (count($job) > 0)
					{
						// run query
						$success = 0;
						$failed = 0;
						
						foreach ($job as $j => $sql)
						{
							$sql = rtrim($sql, "; ");
							$sql = rtrim($sql, ";");
							$sql = rtrim($sql, ";\n");

							$this->sqlString = $sql;
							$this->save;
						}

					}
				}
			}
		}
	}

	// promise method
	public function promise($callback)
	{
		if (is_callable($callback))
		{
			
			if ($this->databaseSource != '')
			{
				$db = DB::apply($this->databaseSource);
			}
			else
			{
				$db = DB::serve();
			}

			$db->setTable($this->tableName);

			$this->promises[$this->tableName] = [$callback, $db];
			
			$status = 'pending';

			call_user_func($callback, $status, $db);
		}
	}

	// drop table
	public function drop($callback)
	{
		if (isset($this->dropTables[$this->tableName]))
		{
			$const = [];
			
			$drop = function(){
				$table = $this->tableName;
				$this->sql = "DROP TABLE `$table`";
				$this->sqljob[] = $this->sql;
				$this->drop;
				$this->sql = '';
			};

			DB::apply($this->databaseSource);

			$table = DB::table($this->tableName)->get();
			Route::getParameters($callback, $const, [$drop, $table]);

			call_user_func_array($callback, $const);
		}
	}

	// table options
	public function options($callback)
	{
		if (isset($this->tableOptions[$this->tableName]))
		{
			$const = [];
			$this->tableOptionsUsed = true;
			Route::getParameters($callback, $const, [$this]);
			call_user_func_array($callback, $const);
		}
	}

	public function rename($to)
	{
		if ($this->tableOptionsUsed)
		{
			$table = $this->tableName;
			$this->sql = "RENAME TABLE `{$table}` TO `{$to}`";
			$this->sqljob[] = $this->sql;
			$this->appendline;
			$this->sql = '';
			
		}
	}

	public function engine($val)
	{
		if ($this->tableOptionsUsed)
		{
			$table = $this->tableName;
			$engine = strtoupper($val);
			$this->sql = "ALTER TABLE `{$table}` ENGINE = {$engine}";
			$this->sqljob[] = $this->sql;
			$this->appendline;
			$this->sql = '';
		}
	}

	public function collation($val)
	{
		if ($this->tableOptionsUsed)
		{
			$table = $this->tableName;
			$charset = null;

			if (strpos($val, '_') !== false)
			{
				$pos = strpos($val, '_');
				$charset = strtolower(substr($val, 0, $pos));
			}
			else
			{
				$charset = 'utf8';
			}

			$this->sql = "ALTER TABLE `{$table}` DEFAULT CHARSET={$charset} COLLATE {$val}";

			$this->sqljob[] = $this->sql;
			$this->appendline;
			$this->sql = '';
		}
	}

	public function __call($meth, $args)
	{
		$column = isset($args[0]) ? $args[0] : null;
		$number = isset($args[1]) ? is_numeric($args[1]) ? (int) $args[1] : "'$args[1]'" : '';
		$other = isset($args[2]) ? $args[2] : '';

		if ( isset($args[1]) && is_string($args[1]) )
		{
			$other .= ' '. $args[1];
		}

		$meth = strtolower($meth);

		if ($meth == 'increment')
		{
			$meth = 'bigint';
			if ($number == '')
			{
				$number = 20;
			}
			$other = 'auto_increment primary key';
		}

		$meth = $meth == 'string' ? 'varchar' : $meth;
		
		$types = explode(',', $this->datatypes);

		array_walk($types, function($e,$i) use (&$types){
			$types[$i] = strtolower($e);
		});

		if (in_array($meth, $types))
		{
			$this->number = $number;
			$this->other = $other;

			if ($meth == "varchar" && $number == "")
			{
				$number = 255;
			}

			if ($column !== null)
			{
				if ($number !== '')
				{
					$this->buildQuery[] = "\t".$column.' ' . strtoupper($meth) .'('. $number .') '. $other .', ';
					
					$this->queryInfo[$column] = [$meth, $number, $other];
				}
				else
				{
					$this->buildQuery[] = "\t".$column.' ' . strtoupper($meth).' '. $other .', ';
					$this->queryInfo[$column] = [$meth, '', $other];
				}
			}
		}
		else
		{
			if (count($this->buildQuery) > 0)
			{
				$keys = array_keys($this->buildQuery);
				$lastKey = end($keys);
				$last = end($this->buildQuery);

				$info = end($this->queryInfo);
				$infokeys = array_keys($this->queryInfo);
				$infoKey = end($infokeys);

				$before = $last;

				$last = rtrim($last, ', ');
				$endinfo = $this->queryInfo[$infoKey][2];

				if ($meth == 'not_null')
				{
					$meth = 'not null';
				}

				if ($meth == 'default')
				{	
					$data = is_string($args[0]) ? "'{$args[0]}'" : $args[0];

					$data = is_string($data) ? str_replace(',', '-%ques', $data) : $data;

					if (is_bool($data))
					{
						if ($data === false)
						{
							$data = 0;
						}
						else
						{
							$data = 1;
						}
					}

					$last .= " ".$meth .' '. $data .', ';

					$endinfo .= $meth .' '. $data .',';
				}
				elseif ($meth == 'current')
				{
					$last .= ' default CURRENT_TIMESTAMP, ';
					$endinfo .= ' default CURRENT_TIMESTAMP, ';
				}
				elseif ($meth == 'comment')
				{
					$last .= ' comment \''.$args[0].'\', ';
					$endinfo .= ' comment \''.$args[0].'\', ';
				}
				elseif ($number !== '')
				{
					$last .= " ".$meth .'('. $number .'), ';

					$endinfo .= $meth .'('. $number .'),';
				}
				else
				{
					$last .= " ".$meth.", ";
					$endinfo .= " ".$meth.", ";
				}

				$this->buildQuery[$lastKey] = $last;
				$this->queryInfo[$infoKey][2] = $endinfo;
			}
		}
		
		return $this;
	}

	public function sql($callback)
	{
		if (is_callable($callback))
		{
			$sql = call_user_func($callback, $this->tableName);
			$sql = preg_replace('/\n{1,}\s{1,}/',"\n", $sql);
			$sql = trim($sql);
			$sql = rtrim($sql, ';');
			$this->sqlString = $sql;
		}
	}

	private function ___appendf($data, $in)
	{
		$_s = file_get_contents($in);

		if (strstr($_s, $data) === false)
		{
			$fh = fopen($in, "a+");
			fwrite($fh, $data);
			fclose($fh);
		}
	}

	private function ___getEntry(&$entry, $current)
	{
		foreach ($current[0] as $index => $line)
		{
			// get column
			$line = trim($line);
			$column = substr($line, 0, strpos($line, ' '));

			// remove trailing comma
			$line = rtrim($line, ',');

			// remove column from line
			$line = trim(ltrim($line, $column));

			if (strpos($line, '-%ques') !== false)
			{
				$line = str_replace('-%ques', ',', $line);
			}

			// push entry
			$entry[$column]['config'] = $line;
		}
	}

	private function ___getChanged($entryNew, $entryOld, &$query = [])
	{
		$newKeys = array_keys($entryNew);
		$oldKeys = array_keys($entryOld);

		$oldHasColumn = true;

		$sqlArray = [];

		foreach ($newKeys as $index => $column)
		{
			if (!in_array($column, $oldKeys))
			{
				$oldHasColumn = false;
				break;
			}
		}

		// if old has column is true
		if ($oldHasColumn)
		{
			// check position of new column
			foreach ($oldKeys as $index => $column)
			{
				if (isset($newKeys[$index]))
				{
					$newcolumn = $newKeys[$index];
					if ($newcolumn != $column)
					{
						// get column line
						$line = $entryNew[$newcolumn]['config'];
						// change column
						$sqlArray[] = "ALTER TABLE `{$this->tableName}` CHANGE COLUMN {$newcolumn} {$newcolumn} {$line} AFTER {$column};";
						
					}
					else
					{
						// check line
						$newLine = $entryNew[$newcolumn]['config'];
						$oldLine = $entryOld[$newcolumn]['config'];

						if ($newLine != $oldLine)
						{
							// changed
							$sqlArray[] = "ALTER TABLE `{$this->tableName}` CHANGE COLUMN {$newcolumn} {$newcolumn} $newLine;";
							
						}
					}
				}
				else
				{
					$sqlArray[] = "ALTER TABLE `{$this->tableName}` DROP $column;";
				}
			}
		}
		else
		{
			foreach ($oldKeys as $index => $column)
			{
				$newcolumn = $newKeys[$index];
				if ($newcolumn != $column)
				{
					$newLine = $entryNew[$newcolumn]['config'];
					$sqlArray[] = "ALTER TABLE `{$this->tableName}` CHANGE COLUMN $column $newcolumn $newLine;";
					
				}
			}
		}

		$query = $sqlArray;
	}

	private function ___addJobAndSave($job, $newSql)
	{
		// migration file
		$migration = HOME."lab/Sql/".ucfirst($this->driver)."/".$this->databaseSource.'.sql';

		if (count($job) > 0)
		{
			foreach ($job as $index => $sql)
			{
				$this->sqljob[] = $sql;
				File::append("\n".$sql, $migration);
			}

		}
		
		// remove question tag
		if (strpos($newSql, '-%ques'))
		{
			$newSql = str_replace('-%ques', ',', $newSql);
		}
			
		$this->sqljob[] = $newSql;
		File::append("\n".$newSql, $migration);
		
	}
}
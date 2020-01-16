<?php

use Moorexa\DB;
use Moorexa\DB\Table;
use Opis\Closure\SerializableClosure;
use Moorexa\Bootloader;

/**
 * @package Moorexa Task Manager
 * @version 0.0.1
 * @author Ifeanyi Amadi
 */

class Task
{
	public static function create( string $name)
	{
		// check for task runner
		$task  = new Task;
		$task->taskname = $name;

		$check = Table::exists('__task_runner');

		if(!$check)
		{
			Table::create('__task_runner', [
				'taskid'   => 'int not null auto_increment primary key',
				'taskname' => 'varchar(300) not null',
				'callback' => 'text not null',
				'status'   => 'varchar(100) not null',
				'dateup'   => 'varchar(100) null',
				'datedown' => 'varchar(100) null' 
			]);
		}

		return $task;
	}

	public function push( $callback )
	{
		if (is_callable($callback))
		{
			$wrapper   = new SerializableClosure($callback);
			$serialize = serialize($wrapper);

			$post = [];
			$post['taskname'] = $this->taskname;
			$post['callback'] = $serialize;
			$post['status']   = 'pending';

			$this->post = $post;
		}

		return $this;
	}

	public function time( string $timeString )
	{
		$obj = $this;

		async::push('taskrunner', function() use ($obj, $timeString){
			if (isset($obj->post))
			{
				$obj->post['dateup'] = date('Y-m-d g:i:s a', strtotime($timeString));

				// save task
				if (Table::check($obj->taskname, '__task_runner/taskname') === false)
				{
					return async::push('taskrunner-job', function() use ($obj){

						$save = DB::table('__task_runner')->insert($obj->post);
						DB::close();
					});
				}
				else
				{
					

					$check = DB::table('__task_runner')->get('taskname = ? and status = ?')->bind($obj->taskname, 'done')->then(function($res) use ($obj){
						if ($res->rows > 0)
						{
							return async::push('taskrunner-job', function() use ($obj){
								
								$drop = task::drop($obj->taskname);
								$save = DB::table('__task_runner')->insert($obj->post);
								DB::close();
							});
							
						}
					});

				}


				return false;
				
			}
		});
	}

	public static function callback( string $taskname )
	{
		// check
		$check = DB::table('__task_runner')->get(1)->where('taskname = ?')->bind($taskname);

		$unserialize = $check->then(function($res){

			return unserialize($res->callback);
		});
		
		$obj = new Task;
		$obj->callback = $unserialize;
		$obj->taskname = $taskname;

		return $obj;
		
	}


	public function call()
	{
		if (isset($this->callback))
		{
			$callback = $this->callback;

			$get = DB::table('__task_runner')->get('taskname = :taskname and status = :status ')->bind($this->taskname, 'pending');

			$get->run();

			if ($get->rows == 1)
			{
				// update status
				DB::table('__task_runner')->update(['status' => 'done', 'datedown' => date('Y-m-d g:i:s a')])->where('taskname = :taskname')->bind($this->taskname)->run();

				return $callback();
			}
			else
			{
				return false;
			}
		}
	}


	// get task status 
	// return (pending|done)

	public static function status(string $taskname)
	{
		$status = DB::table('__task_runner')->get('taskname = ?')->bind($taskname);

		return $status->then(function($e){
			if ($e->rows > 0)
			{
				return $e->status;
			}
		});
	}

	public static function drop(string $taskname)
	{
		DB::table('__task_runner')->delete('taskname = ?')->bind($taskname)->run();
		return true;
	}

	public static function taskRunner()
	{
		if (isset(Bootloader::$helper['activedb']))
		{
			$check = Table::exists('__task_runner');

			if ($check)
			{
				// active db avaliable
				__task_runner::get('status = ?')->bind('pending')
				->then(function($res)
				{
					if ($res->rows > 0)
					{
						$current = date('Y-m-d g:i:s a');

						while ($task = $res->object())
						{
							if ($current >= $task->dateup)
							{
								async::push($task->taskname, function() use ($task){
									Task::callback($task->taskname)->call();
								});
							}
						}
					}
				});
			}
		}
		
	}
}
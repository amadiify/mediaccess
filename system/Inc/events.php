<?php 

namespace Moorexa;

/**
 * @package Moorexa Event Manager
 * @version 0.0.1
 * @author Ifeanyi Amadi
 */

class Event
{
	private static $eventInstance = null;
	private static $listening = [];
	private static $triggered = [];
	private $eventsNotTriggered = [];
	private $condition = null;
	private $event = null;

	// emit an event
	public static function emit($event, $any = null)
	{
		self::instance($object);

		$object->condition = 'emit';
		$object->event = $event;
		
		// call function
		$args = func_get_args();
		$args = array_splice($args, 1);

		$calledBy = get_called_class();
		
		if (strtolower($calledBy) != 'moorexa\event')
		{
			$ref = new \ReflectionClass($calledBy);

			if ($ref->hasMethod('emitTriggered'))
			{
				$meth = $ref->getMethod('emitTriggered');

				if ($meth->isPublic())
				{
					$ins = $ref->newInstanceWithoutConstructor();

					$register = function($event) use ($args, $calledBy, $ins)
					{
						return Event::_emitTriggered($event, $args, $calledBy, $ins);
					};

					$ins->emitTriggered($event, $register);

				}
			}
		}
		else
		{
			self::_emitTriggered($event, $args, $calledBy);
		}

		return $object;
	}

	// listen for an event
	public static function on($event, $callback)
	{
		self::instance($object);

		$calledBy = get_called_class();
		
		if (strtolower($calledBy) != 'moorexa\event')
		{
			$ref = new \ReflectionClass($calledBy);

			if ($ref->hasMethod('onTriggered'))
			{
				$meth = $ref->getMethod('onTriggered');

				if ($meth->isPublic())
				{
					$ins = $ref->newInstanceWithoutConstructor();

					$handler = function($event) use ($callback, $calledBy, $ins)
					{
						return Event::_onTriggered($event, $callback, $calledBy, $ins);
					};

					$ins->onTriggered($event, $handler);
				}
			}
		}
		else
		{
			self::_onTriggered($event, $callback, $calledBy);
		}

		$object->condition = 'on';
		$object->event = $event;
		
		return $object;
	}

	public static function _emitTriggered($event, $args, $calledBy = null, $instance = null)
	{
		// check if event is opened for listening
		$listen = self::$listening;

		if (!is_null($instance))
		{
			if (method_exists($instance, $event))
			{
				$const = [];
				Bootloader::$instance->getParameters($instance, $event, $const, $args);

				$return = call_user_func_array([$instance, $event], $const);
				if ($return !== null)
				{
					if (is_string($return))
					{
						array_unshift($args, $return);
					}
					elseif (is_array($return))
					{
						$args = array_merge($return, $args);
					}
					else
					{
						$args = array_merge([$return], $args);
					}
				}
			}
		}

		if (isset($listen[$calledBy][$event]))
		{
			// event 
			$_event = $listen[$calledBy][$event];

			$const = [];
			Route::getParameters($_event, $const, $args);

			// call function
			call_user_func_array($_event, $const);

			self::$triggered[$calledBy][$event] = $args;
		}
		else
		{
			self::$triggered[$calledBy][$event] = $args;
		}
	}

	public static function _onTriggered($event, $callback, $calledBy = null, $instance = null)
	{

		if (is_array($callback))
		{
			$obj = isset($callback[0]) ? $callback[0] : null;

			if (!is_object($obj) && is_string($obj))
			{
				if (class_exists($obj))
				{
					$obj = new $obj;
				}
			}

			if (is_object($obj))
			{
				$meth = isset($callback[1]) ? $callback[1] : null;
				
				if ($meth != null && method_exists($obj, $meth))
				{
					$args = array_splice($callback, 2);
					$callback = function() use ($obj, $meth, $args)
					{
						$args2 = func_get_args();

						$args = array_merge($args, $args2);

						$const = [];
						Bootloader::$instance->getParameters($obj, $meth, $const, $args);

						return call_user_func_array([$obj, $meth], $const);
					};
				}
				else
				{
					$callback = function(){};
				}
			}
			else
			{
				$callback = function(){};
			}
		}

		self::$listening[$calledBy][$event] = $callback;

		// check if triggered
		if (isset(self::$triggered[$calledBy][$event]))
		{
			$args = self::$triggered[$calledBy][$event];
			
			$const = [];
			Route::getParameters($callback, $const, $args);

			// call closure
			call_user_func_array($callback, $const);
		}
	}

	// listen when event not triggered
	public static function not($event, \closure $callback)
	{
		self::instance($object);
		
		$object->condition = 'not';
		$object->event = $event;

		if (!isset(self::$triggered['Moorexa\Event'][$event]))
		{
			$const = [];
			Route::getParameters($callback, $const, []);

			call_user_func_array($callback, $const);
		}

		return $object;
	}

	// else condition
	public function _else(\closure $callback)
	{
		switch($this->condition)
		{
			case 'not':
				// listen for event
				self::on($this->event, $callback);

				// delete event
				$this->event = null;
			break;

			case 'on':
				self::not($this->event, $callback);

				// delete event
				$this->event = null;
			break;
		}
	}

	// create an instance.
	public static function instance(&$instance)
	{
		if (self::$eventInstance === null)
		{
			$event = new Event();
			self::$eventInstance = $event;
		}
		
		$instance = self::$eventInstance;
		$instance->event = null;
		$instance->condition = null;
	}

	public function __call($meth, $arg)
	{
		if ($meth == 'else')
		{
			return $this->_else(
				$arg[0]
			);
		}
	}

	public static function __callStatic($meth, $data)
	{
		return call_user_func_array('\\Moorexa\Event::emit', array_unshift($data, $meth));
	}
}
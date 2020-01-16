<?php 

namespace Component;

class Loops
{
	// find loop
	public static function __callStatic($meth, $arg)
	{	
		$meth = "__".$meth;
		return self::{$meth}($arg[0], $arg[1]);
	}

	private static function __build($template, $cond)
	{
		return forms::build($template, $cond);
	}

	// while loop
	private static function __while($template, $cond)
	{
		$main = null;
		$object = null;

		$np = "";

		if (is_array($cond))
		{
			// get object..
			foreach ($cond as $key => $val)
			{
				if (is_object($val))
				{
					$class = get_class($val);

					if ($class == 'Moorexa\__promise__')
					{
						$main = $key;
						$object = $val;
						unset($cond[$key]);

						break;
					}
				}
			}

			$val = null;
			$key = null;

			if (is_object($object) && $object->rows > 0)
			{
				$_key = $main;
				$keys = [];
				$match = [];

				preg_match_all("/[{]+\s{0,}+($main)+[.]+([^}]+)+[}]/", $template, $matches);

				if (count($matches) > 0 && isset($matches[2]))
				{
					$keys = $matches[2];
					$match = $matches[0];
				}

				$called = [];
				$i_ = 0;

				while($key = $object->object())
				{
					$i_++;

					$np .= $template ."\n";

					${$_key} = $key;

					$np = str_replace('{i}', $i_, $np);

					if (count($keys) > 0)
					{
						foreach ($keys as $i => $k)
						{
							if (isset($key->{$k}))
							{
								$np = str_replace($match[$i], $key->{$k}, $np);
							}
							else
							{
								$np = str_replace($match[$i], '', $np);
							}
						}
					}

					extract($called);

					if (count($cond) > 0)
					{
						foreach ($cond as $k => $va)
						{
							$_k = $k;

							if (is_callable($va))
							{
								${$_k} = $k;

								$ref = new \ReflectionFunction($va);
								$par = $ref->getParameters();

								$args = [];

								if (count($par) > 0)
								{
									foreach ($par as $i => $obj)
									{
										$args[$obj->name] = ${$obj->name};
									}
								}

								$call = call_user_func_array($va, $args);

								${$_k} = $call;

								if (is_array($call))
								{
									$call = toObject($call);
								}

								$called[${$k}] = $call;

								$_keys = [];
								$_match = [];
								$matches = [];

								preg_match_all("/[{]+\s{0,}+($k)+[.]+([^}]+)+[}]/", $np, $matches);

								if (count($matches) > 0 && isset($matches[2]))
								{
									$_keys = $matches[2];
									$_match = $matches[0];
								}

								if (count($_keys) > 0)
								{
									foreach ($_keys as $ii => $kk)
									{
										if (is_object($call))
										{
											$np = @str_replace($_match[$ii], $call->{$kk}, $np);
										}
										else
										{
											$np = str_replace($_match[$ii], '', $np);
										}
									}
								}

								$ref = null;
								$_keys = null;
								$_match = null;
								$kk = null;

							}
						}
					}
				}
			}
		}

		return $np;
	}

	// foreach loop
	private static function __each($template, $cond)
	{
		$main = null;
		$object = null;

		$np = "";


		if (is_array($cond))
		{
			// get object..
			foreach ($cond as $key => $val)
			{
				if (is_object($val) || is_array($val))
				{
					if (is_array($val))
					{
						$com = true;

						foreach($val as $i => $v)
						{
							if (is_int($i))
							{
								$com = false;
								break;
							}
						}

						if ($com)
						{
							$val = toObject($val);
						}
					}

					$main = $key;
					$object = $val;
					unset($cond[$key]);

					break;
				}
			}

			$val = null;
			$key = null;

			if (is_object($object) && count(toArray($object)) > 0)
			{
				$_key = $main;
				$keys = [];
				$match = [];

				preg_match_all("/[{]+\s{0,}+($main)+[.|]+([^}]+)+[}]/", $template, $matches);

				if (count($matches) > 0 && count($matches[0]) > 0)
				{
					if (count($matches) > 0 && isset($matches[2]))
					{
						$keys = $matches[2];
						$match = $matches[0];
					}
				}
				else
				{
					preg_match_all("/[{]+\s{0,}+($main)+\s{0,}+[}]/", $template, $matches);

					if (count($matches) > 0 && count($matches[0]) > 0)
					{
						if (count($matches) > 0 && isset($matches[1]))
						{
							$keys = $matches[1];
							$match = $matches[0];
						}
					}
				}

				$called = [];
				$new_a = [];
				$i = 0;
				$keys_ = [];

				foreach($object as $key => $data)
				{
					if (!is_array($data) && !is_object($data))
					{
						$new_a[$i][$key] = $data;
					}
					else
					{
						$i++;
						$new_a[$i] = toArray($data);
					}

					$keys_[$i] = $key;
				}

				$data = null;
				$key = null;
				$i_ = 0;

				foreach ($new_a as $ix => $data)
				{	
					$i_++;

					$np .= $template ."\n";

					if (is_array($data))
					{
						$data = toObject($data);
					}

					$key = $data;

					if (count($keys_) > 0)
					{
						$np = str_replace('{key}', $keys_[$ix], $np);
						$np = str_replace('{i}', $i_, $np);

						if (is_object($data))
						{
							$data->key = isset($keys_[$ix]) ? $keys_[$ix] : "";
							$data->i = $i_;
						}
					}

					if (is_object($data))
					{
						${$_key} = $data;

						if (count($keys) > 0)
						{
							foreach ($keys as $i => $k)
							{
								if (isset($key->{$k}))
								{
									$np = str_replace($match[$i], $key->{$k}, $np);
								}
								else
								{
									$np = str_replace($match[$i], '', $np);
								}
							}
						}

						extract($called);

						if (count($cond) > 0)
						{
							foreach ($cond as $k => $va)
							{
								$_k = $k;

								if (is_callable($va))
								{
									${$_k} = $k;

									$ref = new \ReflectionFunction($va);
									$par = $ref->getParameters();

									$args = [];

									if (count($par) > 0)
									{
										foreach ($par as $i => $obj)
										{
											$args[$obj->name] = ${$obj->name};
										}
									}

									$call = call_user_func_array($va, $args);

									${$_k} = $call;

									if (is_array($call))
									{
										$call = toObject($call);
									}

									$called[${$k}] = $call;

									$_keys = [];
									$_match = [];
									$matches = [];

									preg_match_all("/[{]+\s{0,}+($k)+[.]+([^}]+)+[}]/", $np, $matches);

									if (count($matches) > 0 && isset($matches[2]))
									{
										$_keys = $matches[2];
										$_match = $matches[0];
									}

									if (count($_keys) > 0)
									{
										foreach ($_keys as $ii => $kk)
										{
											if (is_object($call))
											{
												$np = @str_replace($_match[$ii], $call->{$kk}, $np);
											}
											else
											{
												$np = str_replace($_match[$ii], '', $np);
											}
										}
									}

									$ref = null;
									$_keys = null;
									$_match = null;
									$kk = null;

								}
							}
						}
					}
				}
			}
			elseif (is_array($object))
			{
				$_key = $main;
				$keys = [];
				$match = [];

				preg_match_all("/[{]+\s{0,}+($main)+[.|]+([^}]+)+[}]/", $template, $matches);

				if (count($matches) > 0 && count($matches[0]) > 0)
				{
					if (count($matches) > 0 && isset($matches[2]))
					{
						$keys = $matches[2];
						$match = $matches[0];
					}
				}
				else
				{
					preg_match_all("/[{]+\s{0,}+($main)+\s{0,}+[}]/", $template, $matches);

					if (count($matches) > 0 && count($matches[0]) > 0)
					{
						if (count($matches) > 0 && isset($matches[1]))
						{
							$keys = $matches[1];
							$match = $matches[0];
						}
					}
				}

				$called = [];
				$new_a = [];
				$i = 0;
				$keys_ = [];

				foreach($object as $key => $data)
				{
					$i++;
					$new_a[$i] = $data;

					$keys_[$i] = $key;
				}

				$i_ = 0;

				foreach ($new_a as $ix => $data)
				{	
					$i_++;

					$np .= $template ."\n";

					$key = $data;

					if (count($keys_) > 0)
					{
						$np = str_replace('{key}', $keys_[$ix], $np);
						$np = str_replace('{i}', $i_, $np);

						if (is_array($data))
						{
							$data['key'] = isset($keys_[$ix]) ? $keys_[$ix] : "";
							$data['i'] = $i_;
						}
					}

					${$_key} = $data;

					if (count($keys) > 0)
					{
						foreach ($keys as $i => $k)
						{
							$np = str_replace($match[$i], $key, $np);
						}

						extract($called);

						if (count($cond) > 0)
						{
							foreach ($cond as $k => $va)
							{
								$_k = $k;

								if (is_callable($va))
								{
									${$_k} = $k;

									$ref = new \ReflectionFunction($va);
									$par = $ref->getParameters();

									$args = [];

									if (count($par) > 0)
									{
										foreach ($par as $i => $obj)
										{
											$args[$obj->name] = ${$obj->name};
										}
									}

									$call = call_user_func_array($va, $args);

									${$_k} = $call;

									if (is_array($call))
									{
										$call = toObject($call);
									}

									$called[${$k}] = $call;

									$_keys = [];
									$_match = [];
									$matches = [];

									preg_match_all("/[{]+\s{0,}+($k)+[.]+([^}]+)+[}]/", $np, $matches);

									if (count($matches) > 0 && isset($matches[2]) && count($matches[2]) > 0)
									{
										$_keys = $matches[2];
										$_match = $matches[0];
									}
									else{
										preg_match_all("/[{]+\s{0,}+($k)+\s{0,}+[}]/", $np, $matches);

										if (count($matches) > 0 && count($matches[0]) > 0)
										{
											if (count($matches) > 0 && isset($matches[1]))
											{
												$_keys = $matches[1];
												$_match = $matches[0];
											}
										}
									}

									if (count($_keys) > 0)
									{
										foreach ($_keys as $ii => $kk)
										{
											if (is_object($call))
											{
												$np = @str_replace($_match[$ii], $call->{$kk}, $np);
											}
											else
											{
												$np = str_replace($_match[$ii], $call, $np);
											}
										}
									}

									$ref = null;
									$_keys = null;
									$_match = null;
									$kk = null;

								}
							}
						}
					}


				}
			}
		}

		return $np;
	}

	// images loop
	private static function __images($template, $path = "")
	{
		$images = allImages($path);
		if (count($images) > 0)
		{
			$temp = "";

			foreach ($images as $i => $path)
			{
				$t = $template;
				$ext = explode('.', basename($path));
				array_pop($ext);
				$name = preg_replace('/[^0-9a-zA-Z]/',' ',implode('.', $ext));
				
				$t = str_replace('{path}', abspath($path), $t);
				$t = str_replace('{name}', $name, $t);
				$temp .= $t . "\n";
			}

			return $temp;
		}
	}
}
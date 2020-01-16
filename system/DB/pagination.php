<?php

namespace Moorexa\DB;

use Moorexa\DB;
use Moorexa\Location;

class Pagination
{
	private static $pages = 5;
	private static $limit = 10;
	private static $action;


	public static function __callStatic($table, $params)
	{
		$config = isset($params[0]) ? (object) $params[0] : (object) ['page' => 10, 'limit' => 10];

	}

	public static function start($config, $rows, $_page = false)
	{
		self::$action = isset($config['action']) ?  $config['action'] : 'pg';
		self::$limit  = isset($config['limit'])  ?  $config['limit']  : self::$limit;

		$key = array_key(self::$action, Location::paths());

		if ($key == "")
		{
			return 0;
		}
		else
		{
			$id = (int) $key + 1;
			$page = Location::paths($id);
			$links = ceil($rows / self::$limit);

			if ($page == 1)
			{
				$page = 0;
			}
			elseif ($page > $links)
			{
				$page = $links;
			}
			else
			{

				if (!$_page)
				{
					$page = filter_var($page, FILTER_VALIDATE_INT);

					if ($page === false)
					{
						$page = 1;
					}

					$limit = (int) self::$limit;

					$page = (int) ($page - 1) * $limit;
				}
			}

			return $page;
		}
	}

	public static function listen($orm, $config = 10, $rows = 0)
	{
		if (is_array($config))
		{
			self::$pages  = isset($config['pages'])  ?  $config['pages']  : self::$pages;
			self::$limit  = isset($config['limit'])  ?  $config['limit']  : self::$limit;
			self::$action = isset($config['action']) ?  $config['action'] : 'pg';
		}

		if ($orm->rows > 0)
		{
			$a = "";

			$links = ceil($rows / self::$limit);

			$floor = floor(self::$pages / 2);

			$page = (int) self::start($config, $rows, true);

			$page = $page == 0 ? 1 : $page;

			$pages =  ($links - $page);

			$div = ceil($links / self::$pages);

			$breakpoint = [];

			$id = 0;

			for($i=1; $i <= $div; $i++)
			{
				$breakpoint[] = (self::$pages * $i) - $id;
				$id++;
			}

			$breakpoint[] = (self::$pages * $i) - $id;

			$last = end($breakpoint);

			$remains = $links - $last;

			if ($remains > 1)
			{
				for ($x=1; $x <= $remains; $x++)
				{
					$cal = (self::$pages * $i) - $id;

					if ($links > $cal)
					{
						$breakpoint[] = $cal;	
						$i ++;
						$id++;
					}
				}
			}

			if (in_array($page, $breakpoint))
			{
				$key = array_key($page, $breakpoint);

				$start = $breakpoint[$key];
			}
			else
			{
				if (self::$pages > $page)
				{
					$start = 1;
				}
				else
				{
					for ($i=$page; $i != 0; $i--)
					{
						if (in_array($i, $breakpoint))
						{
							$key = array_key($i, $breakpoint);

							$start = $breakpoint[$key];

							break;
						}
					}
				}
			}

			$view = \Moorexa\Bootloader::$helper['active_v'];


			for($i=1; $i <= self::$pages; $i++)
			{
				$id = ($start - 1) + $i;

				if ($id <= $links)
				{
					if ($id == $page && $id != 0)
					{
						$a .= '<a class="active push-link" title="current page">'.$id.'</a>';
					}
					else
					{
						$a .= '<a href="'.url($view . '/'.self::$action.'/' . $id).'" class="push-link">'.$id.'</a>';
					}
				}
			}


			$pagination = function() use ($a, $links, $view, $page )
			{
				$wrapper = '<div class="mor-pagination wrapper">';
				$wrapper .= '<div class="pagination-links w1-12">';
				if ($page > 1){
					$wrapper .= '<a href="'.url($view . '/'.self::$action.'/' . ($page-1)).'" class="prev">Prev</a>';
				}
				else
				{
					$wrapper .= '<a href="'.url($view . '/'.self::$action.'/' . ($links)).'" class="prev"> < </a>';
				}
				$wrapper .= $a;
				if ($page < $links)
				{
					$wrapper .= '<a href="'.url($view . '/'.self::$action.'/' . ($page+1)).'" class="next">Next</a>';
				}
				else
				{
					$wrapper .= '<a href="'.url($view . '/'.self::$action.'/' . (1)).'" class="next"> > </a>';
				}
				$wrapper .= '</div>';
				$wrapper .= '<div class="pagination-goto w12-end">';
				$wrapper .= '<div class="pull-right"> <span> Goto Page </span> <input type="text" id="pagination-goto"> <span> / '.$links.'</span> </div>';
				$wrapper .= '</div>';
				$wrapper .= '</div>';

				return $wrapper;

			};	

			$orm->pagination = $pagination();

			return $orm;
		}

		return $orm;
	}
} 
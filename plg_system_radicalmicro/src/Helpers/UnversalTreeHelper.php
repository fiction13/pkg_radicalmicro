<?php namespace RadicalMicro\Helpers;
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Closure;

class UnversalTreeHelper
{


	protected static $_instances = [];


	protected $_map = [];


	protected $_override = [];


	protected static $default_priority = 0.5;


	public static function getInstance($name = 'default')
	{
		if (isset(static::$_instances[$name]))
		{
			return static::$_instances[$name];
		}

		static::$_instances[$name] = new static();
		static::$_instances[$name]->setMap(['uid' => 'root', 'child' => []]);

		return static::$_instances[$name];
	}


	public function getBuild($uid = null)
	{
		$output = [];
		$map    = &$this->_map;

		$override = &$this->_override;
		$this->findNode($map, $map, '', static function (&$element) use (&$output, $uid, $override) {

			foreach ($element as $key => $value)
			{
				if ($key === 'child')
				{
					continue;
				}

				$item[$key] = $value;
			}

			if (isset($item['uid'], $override[$item['uid']]))
			{
				$override = $override[$element['uid']];

				foreach ($override as $key => $value)
				{
					$item[$key] = $value;
				}

			}

			if (
				isset($item['uid']) &&
				$item['uid'] === $uid
			)
			{
				return;
			}

			$output[] = (object) $item;

		}, false);

		return $output;
	}


	public function setMap($map)
	{
		$this->_map = $map;
	}


	public function override($index, $value)
	{
		$this->_override[$index] = $value;
	}


	public function addChild($name, $item)
	{
		$map = &$this->_map;
		$this->findNode($map, $map, $name, static function (&$element) use ($item) {
			if (!isset($element['child']))
			{
				$element['child'] = [];
			}

			if (!isset($item['priority']))
			{
				$item['priority'] = static::$default_priority;
			}

			$element['child'][] = $item;
		});

		return $this;
	}


	public function replace($name, $item)
	{
		$map = &$this->_map;

		$this->findNode($map, $map, $name, static function (&$element) use ($item) {
			$element = array_merge($element, $item);
		});

		return $this;
	}


	public function replaceChild($name, $item)
	{
		$map = &$this->_map;

		$this->findNode($map, $map, $name, static function (&$element) use ($item) {

			if (!isset($element['child']))
			{
				$element['child'] = [];
			}

			$item['child']    = $element['child'];
			$element['child'] = [$item];
		});

		return $this;
	}


	protected function findNode(&$element, &$parent, $uid, Closure $callback, $first = true)
	{
		if (empty($uid))
		{
			$result = $callback($element, $parent);

			if ($result instanceof Closure)
			{
				return $result;
			}
		}
		else
		{
			if (isset($element['uid']) && ($element['uid'] === $uid))
			{
				$result = $callback($element, $parent);

				if ($first)
				{
					if ($result instanceof Closure)
					{
						return $result;
					}
				}
			}
		}


		if (isset($element['child']))
		{
			foreach ($element['child'] as &$child)
			{
				$result = $this->findNode($child, $element, $uid, $callback, $first);

				if ($result instanceof Closure)
				{
					return $result($child, $element);
				}

			}
		}
	}

}
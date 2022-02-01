<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

namespace RadicalMicro\Helpers;

use Closure;

class RadicalMicroHelper
{
	protected static $instances = [];


	protected $map = [];


	protected $_override = [];


	public static function getInstance($name = 'default')
	{
		if (isset(static::$instances[$name]))
		{
			return static::$instances[$name];
		}

		static::$instances[$name] = new static();
		static::$instances[$name]->setMap(['name' => 'root', 'child' => []]);

		return static::$instances[$name];
	}


	public function getBuild($name = null)
	{
		$output = [];
		$map    = &$this->map;

		$override = &$this->_override;
		$this->findNode($map, $map, '', static function (&$element) use (&$output, $name, $override) {

			foreach ($element as $key => $value)
			{
				if ($key === 'child')
				{
					continue;
				}

				$item[$key] = $value;
			}

			if (isset($item['name'], $override[$item['name']]))
			{
				$override = $override[$element['name']];

				foreach ($override as $key => $value)
				{
					$item[$key] = $value;
				}

			}

			if(
				isset($item['name']) &&
				$item['name'] === $name
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
		$this->map = $map;
	}


	public function override($index, $value)
	{
		$this->_override[$index] = $value;
	}


	public function addChild($name, $item)
	{
		$map = &$this->map;
		$this->findNode($map, $map, $name, static function (&$element) use ($item) {
			if (!isset($element['child']))
			{
				$element['child'] = [];
			}

			$element['child'][] = $item;
		});

		return $this;
	}


	public function replace($name, $item)
	{
		$map = &$this->map;

		$this->findNode($map, $map, $name, static function (&$element) use ($item) {
			$element = array_merge($element, $item);
		});

		return $this;
	}


	public function replaceChild($name, $item)
	{
		$map = &$this->map;

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


	protected function findNode(&$element, &$parent, $name, Closure $callback, $first = true)
	{
		if (empty($name))
		{
			$result = $callback($element, $parent);

			if ($result instanceof Closure)
			{
				return $result;
			}
		}
		else
		{
			if (isset($element['name']) && ($element['name'] === $name))
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
				$result = $this->findNode($child, $element, $name, $callback, $first);

				if ($result instanceof Closure)
				{
					return $result($child, $element);
				}

			}
		}
	}
}
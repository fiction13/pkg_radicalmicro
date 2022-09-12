<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Helpers\Tree;

defined('_JEXEC') or die;

use Closure;

class UnversalTreeHelper
{

	protected static $instances = [];


	protected $map = [];


	protected $override = [];


	protected static $default_priority = 0.5;


	public static function getInstance($name = 'default')
	{
		if (isset(static::$instances[$name]))
		{
			return static::$instances[$name];
		}

		static::$instances[$name] = new static();
		static::$instances[$name]->setMap(['uid' => 'root', 'child' => []]);

		return static::$instances[$name];
	}


	public function getBuild($uid = null)
	{
		$output = [];
		$map    = &$this->map;

		$override = &$this->override;
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

		// Sort by result element
		uasort($output, static function ($a, $b) {
			return $a->priority <=> $b->priority;
		});

		// Сollapse element with the same name value
		$output = array_column($output, null, 'uid');

		return $output;
	}


	public function setMap($map)
	{
		$this->map = $map;
	}


	public function getMap()
	{
		return $this->map;
	}


	public function override($index, $value)
	{
		$this->override[$index] = $value;
	}


	public function addChild($name, $item)
	{
		$map = &$this->map;
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
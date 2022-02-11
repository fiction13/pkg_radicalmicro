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

class MetaHelper
{

	public static function execute(string $type, $data, $priority = 0.5)
	{
		if (empty($type))
		{
			return;
		}

		$class_name = '\\RadicalMicro\\Meta\\Collections\\'.ucfirst($type);

		if(!class_exists($class_name) )
		{
			return false;
		}

		$typeClass = new $class_name();

		$result = $typeClass->execute($data, $priority);

		return $result;
	}

	public static function getConfig($type)
	{
		$class_name = '\\RadicalMicro\\Meta\\Collections\\'.ucfirst($type);

		if(!class_exists($class_name) )
		{
			return false;
		}

		$typeClass = new $class_name();

		$result = $typeClass->getConfig();

		return $result;
	}

}
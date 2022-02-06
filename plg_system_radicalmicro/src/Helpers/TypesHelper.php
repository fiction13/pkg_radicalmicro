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

class TypesHelper
{

	public static function execute(string $type, object $data)
	{
		if (empty($type))
		{
			return;
		}

		$class_name = '\\RadicalMicro\\Types\\Classes\\'.ucfirst($type);

		if(!class_exists($class_name) )
		{
			return false;
		}

		$typeClass = new $class_name($data);

		$result = $typeClass->execute($data);

		return $result;
	}


	public static function createType()
	{

	}
}
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

	/**
	 * @param   string  $collectionType - schema, meta, extra
	 * @param           $type - type of metadata
	 * @param           $data - config of the metadata
	 * @param   float   $priority
	 *
	 * @return false|mixed|void
	 *
	 * @since version
	 */
	public static function execute(string $collectionType, $type, $data, $priority = 0.5)
	{
		if (empty($type))
		{
			return;
		}

		$class_name = '\\RadicalMicro\\Types\\Collections\\'. ucfirst($collectionType) . '\\' . ucfirst($type);

		if(!class_exists($class_name) )
		{
			$class_name = '\\RadicalMicro\\Types\\Collections\\'. ucfirst($collectionType) . '\\Extra\\' . ucfirst($type);

			if(!class_exists($class_name) )
			{
				return false;
			}
		}

		$typeClass = new $class_name($data);

		$result = $typeClass->execute($data, $priority);

		return $result;
	}


	/**
	 * Get config for type of collection type
	 *
	 * @param $collectionType - schema, meta, extra
	 * @param $type - type of metadata
	 *
	 * @return false|mixed
	 *
	 * @since version
	 */
	public static function getConfig($collectionType, $type)
	{
		$class_name = '\\RadicalMicro\\Types\\Collections\\' . ucfirst($collectionType) . '\\' . ucfirst($type);

		if(!class_exists($class_name) )
		{
			$class_name = '\\RadicalMicro\\Types\\Collections\\'. ucfirst($collectionType) . '\\Extra\\' . ucfirst($type);

			if(!class_exists($class_name) )
			{
				return false;
			}
		}

		$typeClass = new $class_name();

		$result = $typeClass->getConfig();

		return $result;
	}
}
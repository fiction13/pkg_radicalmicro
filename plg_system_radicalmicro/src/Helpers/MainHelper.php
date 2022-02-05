<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Helpers;

use DateTimeZone;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

class MainHelper
{
	// Build Schema.org
	public static function buildSchema(&$to_string, $data)
	{
		$jsonLd = array();

		// Set data to tree helper
		self::setSchemaData($data);

		// Get data from tree
		$schemaData = SchemaHelper::getInstance()->getBuild('root');

		#TODO Проверить текущие схемы на странице

		foreach ($schemaData as $schema) {
			$jsonLd[] = '<script type="application/ld+json">' . json_encode($schema) . '</script>';
		}

		return implode("\n", $jsonLd);

	}

	// Build Opengrapgh

	public static function buildOpengraph(&$to_string, $data)
	{
		$output = '';

		// собираем метатеги

		return $output;
	}

	// Set Schema via schema helper

	public static function setSchemaData($data)
	{
		foreach ($data as $item)
		{
			// Get data by current schema type
			$function   = 'getSchema'.ucfirst($item->type);
			$schemaData = SchemaHelper::$function($item);

			// Set data to tree
			$micro      = SchemaHelper::getInstance();
            $micro->addChild('root', $schemaData);
		}

		return;
	}

	// Transform date

	public static function date($date, $modify_offset = false)
	{
		$date = is_string($date) ? trim($date) : $date;

		if (empty($date) || is_null($date) || $date == '0000-00-00 00:00:00')
		{
			return $date;
		}

		// Skip if date is already in ISO8601 format
		if (strpos($date, 'T') !== false)
		{
			return $date;
		}

		try {
			$timeZone = new DateTimeZone(Factory::getConfig()->get('offset', 'UTC'));

			$date = new Date($date, $timeZone);

			return $date->toISO8601(true);

		} catch (\Exception $e) {
			return $date;
		}
	}
}
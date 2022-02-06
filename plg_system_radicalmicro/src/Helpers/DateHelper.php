<?php namespace RadicalMicro\Helpers;
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

use DateTimeZone;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

class DateHelper
{

	public static function format($date)
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
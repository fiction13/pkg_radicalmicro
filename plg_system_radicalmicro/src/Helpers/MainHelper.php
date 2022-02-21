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

use RadicalMicro\Helpers\Tree\OGHelper;
use RadicalMicro\Helpers\Tree\SchemaHelper;

class MainHelper
{
	/**
	 * Method to build JSON-LD schema.org
	 *
	 * @param $body
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function buildSchema(&$body)
	{
		$jsonLd = array();

		// Get data from tree
		$schemaData = SchemaHelper::getInstance()->getBuild('root');

		#TODO Проверить текущие схемы на странице

		foreach ($schemaData as $schema) {
			$jsonLd[] = '<script type="application/ld+json">' . json_encode($schema) . '</script>';
		}

		return implode("\n", $jsonLd);
	}

	/**
	 * Method to build opengraph metatags
	 *
	 * @param $body
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function buildOpengraph(&$body)
	{
		$meta = [];

		// Get data from tree
		$ogData = OGHelper::getInstance()->getBuild('root');

		#TODO Проверить текущие схемы на странице

		foreach ($ogData as $og)
		{
			foreach ($og as $property => $content)
			{
				if (!empty($content))
				{
					$meta[] = '<meta property="' . $property . '" content="' . $content . '" />';
				}
			}
		}

		return implode("\n", $meta);
	}

}
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

class MainHelper
{
	/**
	 * Method to build JSON-LD schema.ord
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
		$output = '';

		// собираем метатеги

		return $output;
	}

}
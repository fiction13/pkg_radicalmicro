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

	public static function buildSchema(&$to_string)
	{
		$output = [];
		$schema = SchemaHelper::getBuild('root');

		// достаем из $to_string уже записи про json+ld и удаляем лишнее

		// проходим массив и собираем

		return '<script type="application/ld+json">' . json_encode($output) . '</script>';
	}


	public static function buildOpengraph(&$to_string)
	{
		$output = '';

		// собираем метатеги

		return $output;
	}

}
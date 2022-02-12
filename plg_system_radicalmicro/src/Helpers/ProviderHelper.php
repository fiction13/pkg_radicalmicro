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

use Joomla\CMS\Uri\Uri;
use stdClass;

defined('_JEXEC') or die;

class ProviderHelper
{

	/**
	 * Method for add website schema.org microdata
	 *
	 * @since 1.0.0
	 */
	public static function website()
	{
		$object = new stdClass();

		// Type
		$type = 'website';

		// Get schema data
		$schemaData = TypesHelper::execute($type, $object);

		// Set data
		$micro      = SchemaHelper::getInstance();
		$micro->addChild('root', $schemaData);

		return;
	}

	/**
	 * Method for add logo schema.org microdata
	 *
	 * @since 1.0.0
	 */
	public static function logo($logoUrl)
	{
		$object = new stdClass();
		$object->image = Uri::root().ltrim($logoUrl, '/');

		// Type
		$type = 'logo';

		// Get schema data
		$schemaData = TypesHelper::execute($type, $object);

		// Set data
		$micro      = SchemaHelper::getInstance();
		$micro->addChild('root', $schemaData);

		return;
	}

}
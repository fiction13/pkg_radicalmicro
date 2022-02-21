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

use Joomla\CMS\Language\Text;
use SimpleXMLElement;

defined('_JEXEC') or die;

class XMLHelper
{


	/**
	 * @param   string  $fieldName
	 * @param   string  $dependFieldName
	 * @param   string  $type
	 * @param   string  $default
	 * @param   array   $options
	 *
	 * @return SimpleXMLElement
	 *
	 * @since 1.0.0
	 */
	public static function createField(string $fieldName, string $dependFieldName = '', string $type = '', $default = '', array $options = array())
	{
		if (empty($type))
		{
			$type = self::getFieldType($fieldName);
		}

		// Create simple xml element
		$element = new SimpleXMLElement('<field />');

		// Add name
		$element->addAttribute('name', $fieldName);

		// Add type
		$element->addAttribute('type', $type);

		// Add default
		$element->addAttribute('default', $default);

		// Add type
		$element->addAttribute('label', Text::_('PLG_SYSTEM_RADICALMICRO_PARAM_' . strtoupper($fieldName)));


		// If field is depended on
		if ($dependFieldName)
		{
			$element->addAttribute('showon', $dependFieldName . '!:0');
		}

		// Add options
		if ($options)
		{
			foreach ($options as $option)
			{
				$element->addChild('option', ucfirst($option))->addAttribute('value', $option);
			}
		}

		return $element;
	}

	/**
	 * @param $fieldName
	 *
	 * @return SimpleXMLElement
	 *
	 * @since 1.0.0
	 */
	public static function createBoolField(string $fieldName)
	{
		$element = new SimpleXMLElement('<field default="0" type="radio" class="btn-group" />');

		// Add name
		$element->addAttribute('name', $fieldName);

		// Add type
		$element->addAttribute('label', Text::_('PLG_SYSTEM_RADICALMICRO_PARAM_' . strtoupper($fieldName)));

		// Add options
		$element->addChild('option', Text::_('JNO'))->addAttribute('value', 0);
		$element->addChild('option', Text::_('JYES'))->addAttribute('value', 1);

		return $element;
	}

	/**
	 * @param $fieldName
	 *
	 * @return string|void
	 *
	 * @since 1.0.0
	 */
	public static function getFieldType($fieldName)
    {
		if (strpos($fieldName, 'image') !== FALSE || strpos($fieldName, 'logo'))
		{
			return 'media';
		}

		if (strpos($fieldName, 'description') !== FALSE)
		{
			return 'textarea';
		}

		return 'text';
    }

}
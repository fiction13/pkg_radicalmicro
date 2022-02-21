<?php namespace RadicalMicroYootheme\Helpers;
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

use Joomla\CMS\Language\Text;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\TypesHelper;

defined('_JEXEC') or die;

class YooHelper
{

	/**
     * Transform callback.
     * Use for Front-end translation
     *
     * @param object $node
     * @param array  $params
     */
//    public function __invoke($node, array $params)
//    {
//
//    }

	/**
	 *
	 * @return mixed|YooHelper
	 *
	 * @since 1.0.0
	 */
	public static function getInstance()
    {
        static $instance;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }

	/**
     * Init Yootheme Source
     *
     * @param object $node
     * @param array  $params
     *
     * @since 1.0.0
     */
    public static function initSource(array $type)
    {
        if (isset($type) && isset($type['group']) && $type['group'] === 'RadicalMicro')
		{
			list(, $radicalType) = explode('.', $type['name']);

			// Get all collections of types
			$collections = PathHelper::getInstance()->getTypes($radicalType);

			// Add extra schema types
			if ($radicalType == 'schema')
			{
				$collections = array_merge($collections, PathHelper::getInstance()->getTypes('schema_extra'));
			}

			$config      = [$radicalType];

			// Add core field for meta type choice
			$typeField   = [
				'label'       => 'Type',
	            'description' => Text::_('PLG_SYSTEM_RADICALMICRO_YOOTHEME_TYPE'),
	            'type'        => 'select',
				'options'     => []
			];

			// Get config fields from collections
			foreach ($collections as $collection)
			{
				$typeField['options'][ucfirst($collection)] = $collection;

				$collectionConfig = TypesHelper::getConfig($radicalType, $collection);

				unset($collectionConfig['uid']);

				$collectionConfig = array_keys($collectionConfig);

				if (!empty($collectionConfig))
				{
					foreach ($collectionConfig as $field)
					{
						// Configure new field by collection source
						$newField = [
							'label'  => ucfirst($field),
							'source' => true,
							'show'   => $radicalType . ' == "' . $collection . '"',
							'enable' => $radicalType . ' == "' . $collection . '"'
						];

						if ($fieldType = self::getFieldType($field))
						{
							$newField['type'] = $fieldType;
						}

						// Check if field exist
						if (!isset($type['fields'][$field]))
						{
							$type['fields'][$field] = $newField;
						} else
						{
							$type['fields'][$field]['enable'] .= ' || ' . $radicalType . ' == "'.$collection.'"';
							$type['fields'][$field]['show'] .= ' || ' . $radicalType . ' == "'.$collection.'"';
						}
					}
				}

				$config = array_merge($config, $collectionConfig);
			}

			$type['fields'][$radicalType] = $typeField;

			// Add new fieldset settings tab
			$newFieldset = [
				'title'  => Text::_('PLG_SYSTEM_RADICALMICRO_YOOTHEME_SETTINGS'),
				'fields' => array_unique($config)
			];

			array_unshift($type['fieldset']['default']['fields'] , $newFieldset);
        }

        return $type;
    }


	/**
	 * @param $label
	 *
	 * @return string|void
	 *
	 * @since 1.0.0
	 */
	public static function getFieldType($label)
    {
		if (strpos($label, 'image') !== FALSE)
		{
			return 'image';
		}

		if (strpos($label, 'description') !== FALSE)
		{
			return 'textarea';
		}

		if (strpos($label, 'url') !== FALSE || strpos($label, 'site') !== FALSE)
		{
			return 'link';
		}

		return 'text';
    }

}
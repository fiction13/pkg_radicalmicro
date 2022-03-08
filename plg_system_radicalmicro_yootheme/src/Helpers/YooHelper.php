<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicroYootheme\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\TypesHelper;

final class YooHelper
{
    /**
     * @var
     * @since 1.0.0
     */
    protected static $instance;

    /**
     *
     * @return mixed|YooHelper
     *
     * @since 1.0.0
     */
    public static function getInstance()
    {
        if (is_null(static::$instance))
        {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Init Yootheme Source
     *
     * @param   object  $node
     * @param   array   $params
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

            $config = [$radicalType];

            // Add core field for meta type choice
            $typeField = [
                'label'       => 'Type',
                'description' => Text::_('PLG_SYSTEM_RADICALMICRO_YOOTHEME_TYPE'),
                'type'        => 'select',
                'options'     => []
            ];

            // Get config fields from collections
            foreach ($collections as $collection)
            {
                $typeField['options'][ucfirst($collection)] = $collection;

                $collectionConfig = TypesHelper::getConfig($radicalType, $collection, false);

                if (!empty($collectionConfig))
                {
                    $collectionConfig = array_keys($collectionConfig);

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
                        }
                        else
                        {
                            $type['fields'][$field]['enable'] .= ' || ' . $radicalType . ' == "' . $collection . '"';
                            $type['fields'][$field]['show']   .= ' || ' . $radicalType . ' == "' . $collection . '"';
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

            array_unshift($type['fieldset']['default']['fields'], $newFieldset);
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
        if (strpos($label, 'image') !== false)
        {
            return 'image';
        }

        if (strpos($label, 'description') !== false)
        {
            return 'textarea';
        }

        if (strpos($label, 'url') !== false || strpos($label, 'site') !== false)
        {
            return 'link';
        }

        return 'text';
    }

}
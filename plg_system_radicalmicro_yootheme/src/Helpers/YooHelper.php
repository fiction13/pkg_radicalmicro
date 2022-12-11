<?php
/*
 * @package   pkg_radicalmicro
 * @version   0.2.4
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Addon\Yootheme\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\TypesHelper;
use YOOtheme\Config;
use YOOtheme\Path;
use YOOtheme\Translator;

final class YooHelper
{
    /**
     * @var
     * @since 0.2.2
     */
    protected static $instance;

    /**
     *
     * @return mixed|YooHelper
     *
     * @since 0.2.2
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
     * @param   Config      $config
     * @param   Translator  $translator
     *
     *
     * @since 0.2.2
     */
    public static function initCustomizer(Config $config, Translator $translator)
    {
        $locale = str_replace('_', '-', $config('locale.code'));

        $translator->addResource(Path::get('../../../../../administrator/language/' . $locale . '/' . $locale . '.plg_system_radicalmicro_yootheme.json'));
        $translator->addResource(Path::get('../../../../../administrator/language/' . $locale . '/' . $locale . '.plg_system_radicalmicro_yootheme_custom.json'));
    }

    /**
     * Init Yootheme Source
     *
     * @param   object  $node
     * @param   array   $params
     *
     * @since 0.2.2
     */
    public static function initSource(array $type)
    {
        if (isset($type) && isset($type['group']) && $type['group'] === 'RadicalMicro')
        {
            list(, $radicalType) = explode('.', $type['name']);

            // Get all collections of types
            $collections = PathHelper::getInstance()->getTypes($radicalType);

            // Add extra schema types
//            if ($radicalType == 'schema')
//            {
//                $collections = array_merge($collections, PathHelper::getInstance()->getTypes('schema_extra'));
//            }

            $config = [$radicalType];

            // Add core field for meta type choice for schema.org
            if (self::isSchemaOrg($radicalType))
            {
                $typeField = [
                    'label'       => 'Type',
                    'description' => Text::_('PLG_SYSTEM_RADICALMICRO_YOOTHEME_TYPE'),
                    'type'        => 'select',
                    'options'     => []
                ];
            }

            // Get config fields from collections
            foreach ($collections as $collection)
            {
                // Add schema.org type options to select field
                if ($radicalType === 'schema')
                {
                    $typeField['options'][ucfirst($collection)] = $collection;
                }

                $collectionConfig = TypesHelper::getConfig($radicalType, $collection, false);

                if (!empty($collectionConfig))
                {
                    $collectionConfig = array_keys($collectionConfig);

                    foreach ($collectionConfig as $field)
                    {
                        // Configure new field by collection source
                        $newField = [
                            'label'  => ucfirst($field) . '_' . $collection,
                            'source' => true
                        ];

                        // Disable Twitter site field
                        if ($field === 'type')
                        {
                            $newField['enable'] = '';
                        }

                        // Set showon conditions for schema.org
                        if (self::isSchemaOrg($radicalType))
                        {
                            $newField['label']  = ucfirst($field);
                            $newField['show']   = $radicalType . ' == "' . $collection . '"';
                            $newField['enable'] = $radicalType . ' == "' . $collection . '"';
                        }

                        // Set type for field
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
                            // Add extra conditions for schema.org
                            if (self::isSchemaOrg($radicalType))
                            {
                                $type['fields'][$field]['enable'] .= ' || ' . $radicalType . ' == "' . $collection . '"';
                                $type['fields'][$field]['show']   .= ' || ' . $radicalType . ' == "' . $collection . '"';
                            }
                            else
                            {
                                $type['fields'][$field]['label'] .= '_' . $collection;
                            }
                        }
                    }
                }

                $config = array_merge($config, $collectionConfig);
            }

            // Add schema.org select with options to config
            if (self::isSchemaOrg($radicalType))
            {
                $type['fields'][$radicalType] = $typeField;
            }

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
     * @since 0.2.2
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

    public static function isSchemaOrg($type)
    {
        if ($type === 'schema')
        {
            return true;
        }

        return false;
    }

}
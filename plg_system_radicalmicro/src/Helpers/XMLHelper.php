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

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use SimpleXMLElement;

class XMLHelper
{


    /**
     * @param   string  $fieldName
     * @param   null    $dependFieldName
     * @param   null    $type
     * @param   string  $default
     * @param   null    $options
     * @param   array   $addToName  - extra array of text, added to field label
     *
     * @return SimpleXMLElement
     *
     * @since version
     */
    public static function createField(string $fieldName, $dependFieldName = null, $type = null, $default = '', $options = null, $addToName = array())
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

        // Add label
        $label = Text::_('PLG_RADICALMICRO_PARAM_' . strtoupper($fieldName)) . ($addToName ? ' (' . implode(', ', $addToName) . ')' : '');
        $element->addAttribute('label', $label);

        // If field is depended on
        if ($dependFieldName)
        {
            $element->addAttribute('showon', $dependFieldName . '!:0');
        }

        // Add options
        if ($options)
        {
            foreach ($options as $key => $option)
            {
                if (is_array($option))
                {
                    $group = $element->addChild('group', ucfirst($key));
                    $group->addAttribute('label', ucfirst($key));

                    foreach ($option as $label => $value)
                    {
                        if (!is_string($label))
                        {
                            $label = $value;
                        }

                        $group->addChild('option', ucfirst($label))->addAttribute('value', $value);
                    }
                }
                else
                {
                    $element->addChild('option', ucfirst($option))->addAttribute('value', $option);
                }

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
        $element->addAttribute('label', Text::_('PLG_RADICALMICRO_PARAM_' . strtoupper($fieldName)));

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
        if (strpos($fieldName, 'image') !== false || strpos($fieldName, 'logo'))
        {
            return 'media';
        }

        if (strpos($fieldName, 'description') !== false)
        {
            return 'textarea';
        }

        if (strpos($fieldName, 'date') !== false)
        {
            return 'calendar';
        }

        return 'text';
    }

}
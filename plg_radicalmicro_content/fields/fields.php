<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

FormHelper::loadFieldClass('groupedList');

class JFormFieldFields extends JFormFieldGroupedList
{
    /**
     * @var array
     * @since 1.0.0
     */
    protected $accessFields = [
        'text',
        'textarea',
        'list',
        'groupedlist',
        'select',
        'editor',
        'email',
        'integer',
        'number',
        'checkbox',
        'checkboxes',
        'tel'
    ];

    /**
     * @var array
     * @since 1.0.0
     */
    protected $optionsList = [
        'content' => [
            'title',
            'introtext',
            'fulltext',
            'image_intro',
            'image_fulltext',
            'created_by',
            'created',
            'modified',
            'publish_up',
            'publish_down',
        ]
    ];

    /**
     * @var array
     *
     * @since 1.0.0
     */
    protected $params = [];

    /**
     * The form field type.
     *
     * @var  string
     *
     * @since  1.4.0
     */
    protected $type = 'fields';

    /**
     * Method to get the field option groups.
     *
     * @return  array  The field option objects as a nested array in groups.
     *
     * @throws  UnexpectedValueException
     * @since   1.7.0
     */
    protected function getGroups()
    {
        $groups = $this->getOptions();
        $result = [];

        // First group
        $result[Text::_('PLG_RADICALMICRO_CONTENT_GROUP_EXTRA')] = [
            [
                'text'  => Text::_('PLG_RADICALMICRO_CONTENT_GROUP_EXTRA_OPTION_NO_SELECT'),
                'value' => '_noselect_'
            ]
        ];

        // Other groups
        foreach ($groups as $groupKey => $group)
        {
            $groupLabel = Text::_('PLG_RADICALMICRO_CONTENT_GROUP_' . strtoupper($groupKey));

            foreach ($group as $label => $option)
            {
                if (!is_string($label))
                {
                    $label = $option;
                }

                $tmp = [
                    'text'  => ucfirst(str_replace('_', ' ', $label)),
                    'value' => $option
                ];

                $result[$groupLabel][] = $tmp;
            }
        }

        // Add extra options to custom first group
        if ($addOptions = $this->getAttribute('addoptions'))
        {
            $extraArray = [];
            $addOptions = explode(';', $addOptions);

            foreach ($addOptions as $option)
            {
                $optionList = explode(':', $option);

                if (count($optionList) == 1)
                {
                    $tmp = [
                        'text'  => ucfirst($optionList[0]),
                        'value' => $optionList[0]
                    ];
                }
                else
                {
                    $tmp = [
                        'text'  => ucfirst($optionList[0]),
                        'value' => $optionList[1]
                    ];
                }

                $extraArray[Text::_('PLG_RADICALMICRO_CONTENT_GROUP_EXTRA')][] = $tmp;
            }

            $result = array_merge($extraArray, $result);
        }

        return $result;
    }

    /**
     * @param   null  $item
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function getFields($item = null)
    {
        if (!$this->fields)
        {
            if ((new Version())->isCompatible('4.0'))
            {
                $this->fields = \Joomla\Component\Fields\Administrator\Helper\FieldsHelper::getFields('com_content.article', $item);
            }
            else
            {
                JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

                $this->fields = FieldsHelper::getFields('com_content.article', $item);
            }
        }

        return $this->fields;
    }

    /**
     * Add fields to options
     *
     * @since 1.0.0
     */
    public function getOptions()
    {
        $fields = $this->getFields();

        if ($fields)
        {
            foreach ($fields as $field)
            {
                if (!in_array($field->type, $this->accessFields))
                {
                    continue;
                }

                $fieldsArray[$field->label] = 'field.' . $field->id;
            }
        }

        return array_merge($this->optionsList, ['fields' => $fieldsArray]);
    }
}
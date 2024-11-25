<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalMicro\Content\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\CMS\Language\Text;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

class ContentField extends GroupedlistField
{
    /**
     * @var array
     *
     * @since 0.2.2
     */
    protected $params = [];

    /**
     * The form field type.
     *
     * @var  string
     *
     * @since  0.2.2
     */
    protected $type = 'content';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  0.2.2
     */
    protected $layout = 'plugins.radicalmicro.content.fields.contentfield';
    /**
     * @var array
     * @since 0.2.2
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
     * @since 0.2.2
     */
    protected $optionsList = [
        'content' => [
            'Title'             => 'core.title',
            'Page title'        => 'core.attribs.article_page_title',
            'Intro Text'        => 'core.introtext',
            'Full Text'         => 'core.fulltext',
            'Meta Description'  => 'core.metadesc',
            'Image Intro'       => 'core.image_intro',
            'Image Fulltext'    => 'core.image_fulltext',
            'Created By'        => 'core.created_by',
            'Created Date'      => 'core.created',
            'Modified Date'     => 'core.modified',
            'Publish Up Date'   => 'core.publish_up',
            'Publish Down Date' => 'core.publish_down',
        ]
    ];

    /**
     * @var array|null
     * @since 0.2.5
     */
    protected $fields;

    /**
     * Method to get the field option groups.
     *
     * @return  array  The field option objects as a nested array in groups.
     *
     * @throws  \Exception
     * @since   0.2.2
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
            ],
            [
                'text'  => Text::_('PLG_RADICALMICRO_CONTENT_GROUP_EXTRA_OPTION_CUSTOM'),
                'value' => '_custom_'
            ]
        ];

        // Other groups
        foreach ($groups as $groupKey => $group)
        {
            $groupLabel = Text::_('PLG_RADICALMICRO_CONTENT_GROUP_' . strtoupper($groupKey));

            if (empty($group))
            {
                continue;
            }

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
     * @return array
     *
     * @since 0.2.2
     */
    public function getFields($item = null)
    {
        if (is_null($this->fields))
        {
            $this->fields = FieldsHelper::getFields('com_content.article', $item);
        }

        return $this->fields;
    }

    /**
     * Add fields to options
     *
     * @since 0.2.2
     */
    public function getOptions()
    {
        $fieldsArray = [];
        $fields      = $this->getFields();

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

    /**
     * Method to get the field input markup fora grouped list.
     * Multiselect is enabled by using the multiple attribute.
     *
     * @return  string  The field input markup.
     *
     * @since   0.2.2
     */
    protected function getInput()
    {
        $data = $this->getLayoutData();

        // Get the field groups.
        $data['groups'] = (array) $this->getGroups();

        return $this->getRenderer($this->layout)->render($data);
    }
}
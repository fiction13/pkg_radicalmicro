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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use RadicalMicro\Helpers\TypesHelper;
use RadicalMicro\Helpers\XMLHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;


/**
 * @package     pkg_radicalmicro
 *
 * @since       1.0.0
 */
class plgRadicalMicroContentHelper
{

    /**
     * @var array
     *
     * @since 1.0.0
     */
    protected $defaultFields = [
        'title',
        'description',
        'image'
    ];


    /**
     * @var array
     *
     * @since 1.0.0
     */
    protected $params = [];

    /**
     * @var array
     *
     * @since 1.0.0
     */
    protected $fields = array();

    /**
     * @param   Registry  $params
     *
     * @throws Exception
     */
    public function __construct(Registry $params)
    {
        $this->params = $params;
        $this->app    = Factory::getApplication();
    }

    /**
     * Method get provider data
     *
     * @since 1.0.0
     */
    public function getProviderData()
    {
        // Check is article view
        if (!$this->isArticleView())
        {
            return;
        }

        $item_id = (int) $this->app->input->get('id', 0);

        // Get Article
        $item = Table::getInstance('Content', 'JTable');
        $item->load($item_id);

        // Prepare fields
        $item->jcfields       = $this->getFields($item);
        $item->image_intro    = $this->getImage($item->images, 'image_intro');
        $item->image_fulltext = $this->getImage($item->images, 'image_fulltext');

        // Data object
        $object     = new stdClass();
        $object->id = $item_id;

        // Config field for current schema type
        $configFields = array_keys(TypesHelper::getConfig('schema', $this->params->get('type'), false));

        foreach ($configFields as $configField)
        {
            $object->{$configField} = $this->getData($this->params->get($configField), $item);
        }

        // Check if YooTheme Pro is loaded
        if ($this->isYoothemeBuider($object->description))
        {
            $object->description = '';
        }

        return $object;
    }

    /**
     * @param $value
     * @param $item
     *
     * @return mixed|string|void
     *
     * @since 1.0.0
     */
    public function getData($value, $item)
    {
        // Set empty value if default value was selected
        if ($value === '_default_')
        {
            return;
        }

        if (strpos($value, 'field.') !== false)
        {
            list(, $fieldId) = explode('.', $value);

            $fields   = $this->getFields($item);
            $fieldKey = array_search($fieldId, array_column($fields, 'id'));

            if (isset($fields[$fieldKey]))
            {
                if (is_array($fields[$fieldKey]->value))
                {
                    return implode(', ', $fields[$fieldKey]->value);
                }

                return $fields[$fieldKey]->value;
            }

            return '';
        }
        else
        {
            return $item->{$value};
        }
    }

    /**
     * Check article page view
     *
     * @since  1.0.0
     */
    public function isArticleView()
    {
        return $this->app->input->getCmd('option') === 'com_content'
            && $this->app->input->getCmd('view') === 'article'
            && is_null($this->app->input->getCmd('task'));
    }

    /**
     * Get image from Article object
     *
     * @return void|string.
     *
     * @since  1.1.0
     */
    public function getImage(string $images, string $type)
    {
        $image = new Registry($images);

        return $image->get($type);
    }

    /**
     * @param $description
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isYoothemeBuider($description)
    {
        if (strlen($description) == 0)
        {
            return false;
        }

        if (substr($description, 0, 4) === '<!--' && substr($description, -3) == '-->')
        {
            return true;
        }

        return false;
    }

    /**
     * @param $form
     *
     * @since 1.0.0
     */
    public function setShemaFields($form)
    {
        if ($type = $this->params->get('type'))
        {
            $configFields = array_keys(TypesHelper::getConfig('schema', $type, false));
            $paramsArray  = $this->params->toArray();

            if ($configFields)
            {
                foreach ($configFields as $configField)
                {
                    if (in_array($configField, $this->defaultFields))
                    {
                        continue;
                    }

                    $element = XMLHelper::createField($configField, '', 'fields', $configField);
                    $form->setField($element, null, false, 'basic');
                }
            }
        }

        return;
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
            $this->fields = FieldsHelper::getFields('com_content.article', $item);
        }

        return $this->fields;
    }
}
<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Version;
use Joomla\Registry\Registry;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\TypesHelper;
use RadicalMicro\Helpers\XMLHelper;


/**
 * @package     pkg_radicalmicro
 *
 * @since       __DEPLOY_VERSION__
 */
class plgRadicalMicroContentHelper
{
    /**
     * Param prefix
     *
     * @since  __DEPLOY_VERSION__
     */
    const PREFIX_SCHEMA = 'schema_';

    /**
     * Param prefix
     *
     * @since  __DEPLOY_VERSION__
     */
    const PREFIX_META = 'meta_';

    /**
     * @var array
     *
     * @since __DEPLOY_VERSION__
     */
    protected $params = [];

    /**
     * @var array
     *
     * @since __DEPLOY_VERSION__
     */
    protected $fields = array();

    /**
     * @var
     * @since __DEPLOY_VERSION__
     */
    protected $item;

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
     * @return void|object
     *
     * @since __DEPLOY_VERSION__
     */
    public function getSchemaObject()
    {
        // Check is article view
        if (!$this->isArticleView())
        {
            return;
        }

        // Get item object
        $item       = $this->getItem();
        $itemParams = new Registry($item->params);

        // Data object
        $object     = new stdClass();
        $object->id = $item->id;

        // Get and set schema.org data for current article from article params or from plugin
        if ($itemParams->get('radicalmicro_schema_content_enable', 0))
        {
            // Config field for current schema type
            $configFields = array_keys(TypesHelper::getConfig('schema', $itemParams->get('radicalmicro_schema_content_type'), false));

            foreach ($configFields as $configField)
            {
                $object->{$configField} = $itemParams->get('radicalmicro_schema_content_' . $configField);
            }
        }
        else
        {
            // Config field for current schema type
            $configFields = array_keys(TypesHelper::getConfig('schema', $this->params->get('type'), false));

            foreach ($configFields as $configField)
            {
                $object->{$configField} = $this->getData($this->params->get(self::PREFIX_SCHEMA . $configField), $item);
            }

            // Check if YooTheme Pro is loaded
            if ($this->isYoothemeBuider($object->description))
            {
                $object->description = '';
            }
        }

        return $object;
    }

    /**
     * Method get provider data
     *
     * @return void|object
     *
     * @since __DEPLOY_VERSION__
     */
    public function getMetaObject()
    {
        // Check is article view
        if (!$this->isArticleView())
        {
            return;
        }

        // Get item object
        $item       = $this->getItem();
        $itemParams = new Registry($item->params);

        // Data object
        $object     = new stdClass();
        $object->id = $item->id;

        // Config field for meta type
        $configFields = $this->getMetaFields();

        // Get and set schema.org data for current article from article params or from plugin
        if ($itemParams->get('radicalmicro_meta_content_enable', 0))
        {
            foreach ($configFields as $key => $field)
            {
                $object->{$key} = $itemParams->get('radicalmicro_meta_content_' . $field['name'], $itemParams->get($field['name']));
            }
        }
        else
        {
            foreach ($configFields as $key => $field)
            {
                $object->{$key} = (empty($field['default'])) ? $this->getData($this->params->get($field['name']), $item) : $this->params->get($field['name']);
            }

            // Check if YooTheme Pro is loaded
            if ($this->isYoothemeBuider($object->description))
            {
                $object->description = '';
            }
        }

        return $object;
    }

    /**
     * Get Article
     *
     * @return bool|Table
     *
     * @since __DEPLOY_VERSION__
     */
    public function getItem()
    {
        if (!$this->item)
        {
            $item_id = (int) $this->app->input->get('id', 0);

            // Get Article
            $item = Table::getInstance('Content', 'JTable');
            $item->load($item_id);

            // Prepare fields
            $item->jcfields       = $this->getFields($item);
            $item->image_intro    = $this->getImage($item->images, 'image_intro');
            $item->image_fulltext = $this->getImage($item->images, 'image_fulltext');

            $this->item = $item;
        }

        return $this->item;
    }

    /**
     * Get data from Article object
     *
     * @param $value
     * @param $item
     *
     * @return mixed|string|void
     *
     * @since __DEPLOY_VERSION__
     */
    public function getData($value, $item)
    {
        // Set empty value if default value was selected
        if ($value === '_noselect_')
        {
            return '';
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
            return $item->{$value} ?? '';
        }
    }

    /**
     * Check article page view
     *
     * @since  __DEPLOY_VERSION__
     */
    public function isArticleView()
    {
        return $this->app->input->getCmd('option') === 'com_content'
            && $this->app->input->getCmd('view') === 'article'
            && is_null($this->app->input->getCmd('task'));
    }

    /**
     * Get image from json images object
     *
     * @return void|string.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getImage(string $images, string $type)
    {
        $image = new Registry($images);

        return $image->get($type);
    }

    /**
     * Check YOOtheme builder enabled in article
     *
     * @param $description
     *
     * @return bool
     *
     * @since __DEPLOY_VERSION__
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
     * Set schema.org fields to Form
     *
     * @param   Form  $form
     *
     * @since __DEPLOY_VERSION__
     */
    public function setSchemaFields(Form $form, $isArticle = false)
    {
        $dependField = '';
        $group       = 'schema';

        if ($isArticle)
        {
            $dependField = 'radicalmicro_schema_content_enable';
            $group       = 'radicalmicro_schema';
        }

        if ($type = $this->params->get('type'))
        {
            $configFields = array_keys(TypesHelper::getConfig('schema', $type, false));

            if ($configFields)
            {
                foreach ($configFields as $configField)
                {
                    $element = XMLHelper::createField(self::PREFIX_SCHEMA . $configField, $dependField, 'fields', $configField);
                    $form->setField($element, null, false, $group);
                }
            }
        }

        return;
    }

    /**
     * Set meta fields to Form
     *
     * @param   Form  $form
     *
     * @since __DEPLOY_VERSION__
     */
    public function setMetaFields(Form $form, $isArticle = false)
    {
        // Add fields to fieldset
        $addFields   = $this->getMetaFields();
        $dependField = '';
        $group       = 'meta';

        if ($isArticle)
        {
            $dependField = 'radicalmicro_meta_content_enable';
            $group       = 'radicalmicro_meta';
        }

        // In result - add fields to form
        if ($addFields)
        {
            foreach ($addFields as $key => $field)
            {
                $element = XMLHelper::createField($field['name'], $dependField, (empty($field['default']) ? 'fields' : ''), $field['default'], null, $field['type']);
                $form->setField($element, null, false, $group);
            }
        }

        return;
    }

    /**
     * Get all article fields
     *
     * @param   null  $item
     *
     * @return mixed
     *
     * @since __DEPLOY_VERSION__
     */
    public function getFields($item = null)
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

        return $this->fields;
    }

    /**
     * Get all config fields for all meta collections
     *
     * @return array
     *
     * @since __DEPLOY_VERSION__
     */
    public function getMetaFields()
    {
        $addFields = [];

        // Get all collections of types
        $collections = PathHelper::getInstance()->getTypes('meta');

        foreach ($collections as $collection)
        {
            // Get config of each meta type
            $collectionConfig = TypesHelper::getConfig('meta', $collection, false);

            if (!empty($collectionConfig))
            {
                $fields = array_keys($collectionConfig);

                // Add each field of config
                foreach ($fields as $field)
                {
                    if (!isset($addFields[$field]))
                    {
                        $addFields[$field] = [
                            'name'    => self::PREFIX_META . $field,
                            'default' => $collectionConfig[$field],
                        ];
                    }

                    $addFields[$field]['type'][] = ucfirst($collection);
                }
            }
        }

        return $addFields;
    }
}
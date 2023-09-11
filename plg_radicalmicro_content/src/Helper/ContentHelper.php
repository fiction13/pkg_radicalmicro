<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalMicro\Content\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Event\Event;
use Joomla\Plugin\System\RadicalMicro\Helper\PathHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\TypesHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\XMLHelper;
use Joomla\Registry\Registry;


/**
 * @package     pkg_radicalmicro
 *
 * @since       0.2.2
 */
class ContentHelper
{
    /**
     * Param prefix
     *
     * @since  0.2.2
     */
    const PREFIX_SCHEMA = 'schema_';

    /**
     * Param prefix
     *
     * @since  0.2.2
     */
    const PREFIX_META = 'meta_';

    /**
     * @var array
     *
     * @since 0.2.2
     */
    protected $params = [];

    /**
     * @var \Joomla\CMS\Application\CMSApplication
     *
     * @since __DEPLOY_VERSION__
     */
    protected $app;

    /**
     * @var
     *
     * @since 0.2.2
     */
    protected $item;

    /**
     * @var mixed
     *
     * @since __DEPLOY_VERSION__
     */
    protected $fields = null;

    /**
     * @param   Registry  $params
     *
     * @throws \Exception
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
     * @since 0.2.2
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
        $itemParams = new Registry($item->attribs);

        // Data object
        $object     = new \stdClass();
        $object->id = $item->id;

        // Config field for current schema type
        $configFields = array_keys(TypesHelper::getConfig('schema', $this->params->get('type'), false));

        foreach ($configFields as $configField)
        {
            $object->{$configField} = $this->getData($this->params->get(self::PREFIX_SCHEMA . $configField), $item);
        }

        // Check if YooTheme Pro is loaded
        if (isset($object->description) && $this->isYoothemeBuider($object->description))
        {
            $object->description = '';
        }

        // Get and set schema.org data from article settings
        if ($itemParams->get('radicalmicro_schema_content_enable', 0))
        {
            // Config field for current schema type
            $configFields = array_keys(TypesHelper::getConfig('schema', $itemParams->get('radicalmicro_schema_content_type'), false));

            foreach ($configFields as $configField)
            {
                if ($configValue = $itemParams->get(self::PREFIX_SCHEMA . $configField))
                {
                    $object->{$configField} = $configValue;
                }
            }
        }

        return $object;
    }

    /**
     * Method get provider data
     *
     * @return void|object
     *
     * @since 0.2.2
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
        $itemParams = new Registry($item->attribs);

        // Data object
        $object     = new \stdClass();
        $object->id = $item->id;

        // Config field for meta type
        $configFields = $this->getMetaFields();

        foreach ($configFields as $key => $field)
        {
            $object->{$key} = (empty($field['default'])) ? $this->getData($this->params->get($field['name']), $item) : $this->params->get($field['name']);
        }

        // Check if YooTheme Pro is loaded
        if (isset($object->description) && $this->isYoothemeBuider($object->description))
        {
            $object->description = '';
        }

        // Get and set schema.org data for current article from article params or from plugin
        if ($itemParams->get('radicalmicro_meta_content_enable', 0))
        {
            foreach ($configFields as $key => $field)
            {
                if ($configValue = $itemParams->get(self::PREFIX_META . $field['name'], $itemParams->get($field['name'])))
                {
                    $object->{$key} = $configValue;
                }
            }
        }

        return $object;
    }

    /**
     * Get Article
     *
     * @return bool|Table
     *
     * @since 0.2.2
     */
    public function getItem()
    {
        if (!$this->item)
        {
            $item_id = (int) $this->app->input->get('id', 0);

            // Get Article
            $model = Factory::getApplication()->bootComponent('com_content')->getMVCFactory()->createModel('Article', 'Site', ['ignore_request' => true]);
            $model->setState('params', ComponentHelper::getParams('com_content'));
            $item = $model->getItem($item_id);

            // Prepare fields
            $item->jcfields       = $this->getFields($item);
            $item->image_intro    = $item->images ? $this->getImage($item->images, 'image_intro') : '';
            $item->image_fulltext = $item->images ? $this->getImage($item->images, 'image_fulltext') : '';

            // Process the content plugins.
            PluginHelper::importPlugin('system');
            Factory::getApplication()->getDispatcher()->dispatch('onContentPrepare',
                (new Event('onContentPrepare', ['com_content.article', &$item, &$item->params, 0])
                )
            );

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
     * @since 0.2.2
     */
    public function getData($value, $item)
    {
        // Set empty value if default value was selected
        if ($value === '_noselect_')
        {
            return '';
        }

        if (strpos($value ?? '', 'field.') !== false)
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
        else if (strpos($value ?? '', 'core.') !== false)
        {
            $value = str_replace('core.', '', $value);

            // Get Registry values if value contains dot like attribs.param
            if (strpos($value ?? '', '.') !== false)
            {
                list($level, $val) = explode('.', $value, 2);

                return (new Registry($item->{$level}))->get($val, '');
            }

            return $item->{$value} ?? '';
        }
        else
        {
            # TODO Убрать при релизе!!! Только для обратной совместимости v0.2.0 и ниже

            if (str_word_count($value ?? '') === 1 && strpos($value, '@') !== 0)
            {
                return $item->{$value} ?? '';
            }

            return $value;
        }
    }

    /**
     * Check article page view
     *
     * @since  0.2.2
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
     * @since  0.2.2
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
     * @since 0.2.2
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
     * @param         $data
     *
     * @since 0.2.2
     */
    public function setSchemaFields(Form $form, $data = null)
    {
        $isArticle   = !empty($data);
        $dependField = !$isArticle ? '' : 'radicalmicro_schema_content_enable';
        $group       = !$isArticle ? 'schema' : 'radicalmicro_schema';
        $fieldType   = 'content';
        $type        = $this->params->get('type');

        if ($data && $data->get('attribs.radicalmicro_schema_content_enable', 0))
        {
            $type = $data->get('attribs.radicalmicro_schema_content_type');
        }

        if ($type)
        {
            $configFields = array_keys(TypesHelper::getConfig('schema', $type, false));

            if ($configFields)
            {
                foreach ($configFields as $configField)
                {
                    $element = XMLHelper::createField(self::PREFIX_SCHEMA . $configField, $dependField, $fieldType);
                    $form->setField($element, null, false, $group);
                }
            }
        }

        return true;
    }

    /**
     * Set meta fields to Form
     *
     * @param   Form  $form
     *
     * @since 0.2.2
     */
    public function setMetaFields(Form $form, $isArticle = false)
    {
        // Add fields to fieldset
        $addFields   = $this->getMetaFields();
        $dependField = !$isArticle ? '' : 'radicalmicro_meta_content_enable';
        $group       = !$isArticle ? 'meta' : 'radicalmicro_meta';

        // In result - add fields to form
        if ($addFields)
        {
            foreach ($addFields as $key => $field)
            {
                $element = XMLHelper::createField($field['name'], $dependField, (empty($field['default']) && !$isArticle ? 'content' : ''), $field['default'], null, $field['type']);
                $form->setField($element, null, false, $group);
            }
        }

        return true;
    }

    /**
     * Get all article fields
     *
     * @param   null  $item
     *
     * @return mixed
     *
     * @since 0.2.2
     */
    public function getFields($item = null)
    {
        $this->fields = FieldsHelper::getFields('com_content.article', $item);

        return $this->fields;
    }

    /**
     * Get all config fields for all meta collections
     *
     * @return array
     *
     * @since 0.2.2
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


    /**
     * Method for get current schema type from article or plugin params
     *
     * @return mixed|\stdClass
     *
     * @since 0.2.2
     */
    public function getActualSchemaType()
    {
        // Check is article view
        if (!$this->isArticleView())
        {
            return false;
        }

        // Get item object
        $item       = $this->getItem();
        $itemParams = new Registry($item->attribs);

        // Data object
        $object     = new \stdClass();
        $object->id = $item->id;

        // Get and set schema.org data for current article from article params or from plugin
        if ($itemParams->get('radicalmicro_schema_content_enable', 0))
        {
            return $itemParams->get('radicalmicro_schema_content_type', 0);
        }

        return $this->params->get('type', 'article');
    }
}
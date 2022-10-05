<?php
/*
 * @package   pkg_radicalmicro
 * @version   0.2.1
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Menu;
use Joomla\Registry\Registry;
use RadicalMicro\Helpers\TypesHelper;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\XMLHelper;


/**
 * @package     pkg_radicalmicro
 *
 * @since       __DEPLOY_VERSION__
 */
class plgRadicalMicroMenuHelper
{
    /**
     * Param prefix
     *
     * @since  __DEPLOY_VERSION__
     */
    const PREFIX_SCHEMA = 'radicalmicro_schema_menu_';

    /**
     * Param prefix
     *
     * @since  __DEPLOY_VERSION__
     */
    const PREFIX_META = 'radicalmicro_meta_menu_';

    /**
     * @var array
     *
     * @since __DEPLOY_VERSION__
     */
    protected $params = [];

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
     * @return object|void
     *
     * @since __DEPLOY_VERSION__
     */
    public function getMetaObject(Registry $params)
    {
        // Data object
        $object     = new stdClass();

        // Config field for meta type
        $configFields = $this->getMetaFields();

        foreach ($configFields as $key => $field)
        {
            $object->{$key} = $params->get(self::PREFIX_META . $field['name'], $params->get($field['name']));
        }

        return $object;
    }

    /**
     * Method get provider data
     *
     * @since __DEPLOY_VERSION__
     */
    public function getSchemaObject(Registry $params)
    {
        // Data object
        $object     = new stdClass();

        // Config field for current schema type
        $configFields = array_keys(TypesHelper::getConfig('schema', $params->get(self::PREFIX_SCHEMA . 'type'), false));

        foreach ($configFields as $configField)
        {
            $object->{$configField} = $params->get(self::PREFIX_SCHEMA . $configField);
        }

        return $object;
    }

    /**
     * Set schema.org fields to Form
     *
     * @param   Form  $form
     *
     * @since __DEPLOY_VERSION__
     */
    public function setSchemaFields(Form $form, $data)
    {
        $params = new Registry($data->params);

        if ($type = $params->get(self::PREFIX_SCHEMA . 'type'))
        {
            $configFields = array_keys(TypesHelper::getConfig('schema', $type, false));

            if ($configFields)
            {
                foreach ($configFields as $configField)
                {
                    $element = XMLHelper::createField(self::PREFIX_SCHEMA . $configField, self::PREFIX_SCHEMA . 'enable', '');
                    $form->setField($element, null, false, 'radicalmicro_schema');
                }
            }
        }

        return;
    }

    /**
     * @param $form
     *
     * @since __DEPLOY_VERSION__
     */
    public function setMetaFields($form)
    {
        // Add fields to fieldset

        $addFields = $this->getMetaFields();

        // In result - add fields to form
        if ($addFields)
        {
            foreach ($addFields as $key => $field)
            {
                $element = XMLHelper::createField($field['name'], self::PREFIX_META . 'enable', null, $field['default'], null, $field['type']);
                $form->setField($element, null, false, 'radicalmicro_meta');
            }
        }

        return;
    }

    /**
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
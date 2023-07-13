<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalMicro\Menu\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;
use Joomla\Plugin\System\RadicalMicro\Helper\TypesHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\PathHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\XMLHelper;
use stdClass;


/**
 * @package     pkg_radicalmicro
 *
 * @since       0.2.2
 */
class MenuHelper
{
    /**
     * Param prefix
     *
     * @since  0.2.2
     */
    const PREFIX_SCHEMA = 'radicalmicro_schema_menu_';

    /**
     * Param prefix
     *
     * @since  0.2.2
     */
    const PREFIX_META = 'radicalmicro_meta_menu_';

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
     * @return object|void
     *
     * @since 0.2.2
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
     * @since 0.2.2
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
     * @since 0.2.2
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
     * @since 0.2.2
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
     * Method for check current menu page
     *
     * @return mixed|stdClass
     *
     * @since 0.2.2
     */

    public static function checkActive($menu)
    {
        if ($menu === null)
        {
            return false;
        }

        $current   = true;
        $inputVars = Factory::getApplication()->input->getArray();

        foreach ($menu->query as $key => $value)
        {
            if (!isset($inputVars[$key]) || $inputVars[$key] !== $value)
            {
                $current = false;
                break;
            }
        }

        return $current;
    }
}
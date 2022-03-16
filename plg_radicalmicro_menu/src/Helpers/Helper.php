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
use Joomla\Registry\Registry;
use RadicalMicro\Helpers\TypesHelper;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\XMLHelper;


/**
 * @package     pkg_radicalmicro
 *
 * @since       1.0.0
 */
class plgRadicalMicroMenuHelper
{
    /**
     * Param prefix
     *
     * @since  1.0.0
     */
    const PREFIX = 'radicalmicro_menu_';

    /**
     * @var array
     *
     * @since 1.0.0
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
     * @since 1.0.0
     */
    public function getProviderData()
    {
        $menu = Factory::getApplication()->getMenu()->getActive();

        // Check is article view
        if ($menu === null)
        {
            return;
        }

        $menuParams = $menu->getParams();

        // Data object
        $object     = new stdClass();
        $object->id = $menu->id;

        // Config field for meta type
        $configFields = $this->getMetaFields();

        foreach ($configFields as $key => $field)
        {
            $object->{$key} = $menuParams->get(self::PREFIX . $field['name'], $menuParams->get($field['name']));
        }

        return $object;
    }

    /**
     * @param $form
     *
     * @since 1.0.0
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
                $element = XMLHelper::createField($field['name'], 'radicalmicro_menu_enable', null, $field['default'], null, $field['type']);
                $form->setField($element, null, false, 'radicalmicro');
            }
        }

        return;
    }

    /**
     *
     * @return array
     *
     * @since 1.0.0
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
                            'name'    => self::PREFIX . $field,
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
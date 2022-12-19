<?php
/*
 * @package   pkg_radicalmicro
 * @version   0.2.4
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\Tree\OGHelper;
use RadicalMicro\Helpers\Tree\SchemaHelper;
use RadicalMicro\Helpers\TypesHelper;
use RadicalMicro\Provider\Menu\Helpers\MenuHelper;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Radicalmicro
 *
 * @package   plgRadicalmicroContent
 * @since     0.2.2
 */
class plgRadicalMicroMenu extends CMSPlugin
{
    /**
     * Application object
     *
     * @var    CMSApplication
     * @since  0.2.2
     */
    protected $app;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  0.2.2
     */
    protected $autoloadLanguage = true;

    /**
     * @param          $subject
     * @param   array  $config
     *
     * @throws Exception
     */
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        // Helper
        $this->helper = new MenuHelper($this->params);
    }

    /**
     * Adds forms for override
     *
     * @param   Form   $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  boolean
     *
     * @since   0.2.2
     */
    public function onContentPrepareForm(Form $form, $data)
    {
        // Check menu edit form
        if ($this->app->isClient('administrator') && $form->getName() === 'com_menus.item' && !in_array($data->type, ['heading', 'url', 'separator']))
        {
            // Add fieldset for menu
            Form::addFormPath(__DIR__ . '/forms');
            $form->loadFile('menu', true);

            // Set schema.org fields
            $this->helper->setSchemaFields($form, $data);

            // Set meta fields
            $this->helper->setMetaFields($form);

            // Add css
            Factory::getDocument()->addStyleDeclaration(
                '#attrib-radicalmicro[active] {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);;
                    gap: 2vw;
                    grid-auto-rows: minmax(100px, auto);
                }
                @media (max-width: 769px) {
                    #attrib-radicalmicro {
                        grid-template-columns: repeat(1, 1fr);
                    }
                }'
            );
        }

        return true;
    }

    /**
     * OnRadicalmicroProvider event
     *
     * @return array|void
     *
     * @since  0.2.2
     */
    public function onRadicalMicroProvider($params)
    {
        $menu = Factory::getApplication()->getMenu()->getActive();

        // Check is article view
        if (!MenuHelper::checkActive($menu))
        {
            return;
        }

        $menuParams = $menu->getParams();

        // Get and set schema.org data
        if ($menuParams->get('radicalmicro_schema_menu_enable', 0))
        {
            // Get schema type
            $type = $menuParams->get('radicalmicro_schema_menu_type', 'article');

            // Get and set schema data
            $schemaObject = $this->helper->getSchemaObject($menuParams);

            if ($schemaObject && $type)
            {
                $schemaData = TypesHelper::execute('schema', $type, $schemaObject, 0.7);
                SchemaHelper::getInstance()->addChild('root', $schemaData);
            }
        }

        // Get and set opengraph data
        if ($menuParams->get('radicalmicro_meta_menu_enable', 0))
        {
            $metaObject = $this->helper->getMetaObject($menuParams);

            if ($metaObject)
            {
                $collections = PathHelper::getInstance()->getTypes('meta');

                foreach ($collections as $collection)
                {
                    $ogData = TypesHelper::execute('meta', $collection, $metaObject, 0.7);
                    OGHelper::getInstance()->addChild('root', $ogData);
                }
            }
        }

        // Replace title and description
        if ($this->params->get('replace_title', 1) || $this->params->get('replace_description', 1))
        {
            $menuComponent = str_replace('com_', '', $menu->component);
            $currentPlugin = PluginHelper::getPlugin('radicalmicro', $menuComponent);

            if ($currentPlugin)
            {
                $currentPluginParams = new Registry($currentPlugin->params);
                $schemaType          = $currentPluginParams->get('type');

                $object = array();

                // Replace title data
                if ($this->params->get('replace_title', 1))
                {
                    $source          = $this->params->get('replace_title_source', 'page_title');
                    $object['title'] = $menuParams->get($source);
                }

                // Replace description data
                if ($this->params->get('replace_description', 1))
                {
                    $object['description'] = $menuParams->get('menu-meta_description');
                }

                // Set meta data
                $collections = PathHelper::getInstance()->getTypes('meta');

                foreach ($collections as $collection)
                {
                    $ogData = TypesHelper::execute('meta', $collection, $object, 0.7);
                    OGHelper::getInstance()->addChild('root', $ogData);
                }

                // Set schema data
                if ($schemaType)
                {
                    $schemaData = TypesHelper::execute('schema', $schemaType, $object, 0.7);
                    SchemaHelper::getInstance()->addChild('root', $schemaData);
                }
            }
        }
    }
}

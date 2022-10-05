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

use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\Tree\OGHelper;
use RadicalMicro\Helpers\Tree\SchemaHelper;
use RadicalMicro\Helpers\TypesHelper;
use RadicalMicro\Helpers\UtilityHelper;

/**
 * Radicalmicro
 *
 * @package   plgRadicalmicroContent
 * @since     __DEPLOY_VERSION__
 */
class plgRadicalMicroMenu extends CMSPlugin
{
    /**
     * Application object
     *
     * @var    CMSApplication
     * @since  __DEPLOY_VERSION__
     */
    protected $app;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
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

        // Include helper
        JLoader::register('plgRadicalMicroMenuHelper', __DIR__ . '/src/Helpers/Helper.php');

        // Helper
        $this->helper = new plgRadicalMicroMenuHelper($this->params);
    }

    /**
     * Adds forms for override
     *
     * @param   Form  $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onContentPrepareForm(Form $form, $data)
    {
        $component = $this->app->input->get('option');
        $layout    = $this->app->input->get('layout');

        // Check menu edit form
        if ($this->app->isClient('administrator') && $component === 'com_menus' && $layout === 'edit')
        {
            // Add fieldset for menu
            Form::addFormPath(__DIR__ . '/forms');
            $form->loadFile('menu', true);

            // Set schema.org fields
            $this->helper->setSchemaFields($form, $data);

            // Set meta fields
            $this->helper->setMetaFields($form);
        }

        return true;
    }

    /**
     * OnRadicalmicroProvider event
     *
     * @return array|void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function onRadicalMicroProvider($params)
    {
        $menu = $this->app->getMenu()->getActive();

        // Check is article view
        if ($menu === null)
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
                $schemaData   = TypesHelper::execute('schema', $type, $schemaObject, 0.7);
                SchemaHelper::getInstance()->addChild('root', $schemaData);
            }
        }

        // Get and set opengraph data
        if ($menuParams->get('radicalmicro_meta_menu_enable', 0))
        {
            $metaObject  = $this->helper->getMetaObject($menuParams);

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
    }
}

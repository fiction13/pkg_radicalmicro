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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use RadicalMicro\Helpers\Tree\SchemaHelper;
use RadicalMicro\Helpers\Tree\OGHelper;
use RadicalMicro\Helpers\TypesHelper;
use RadicalMicro\Helpers\PathHelper;

/**
 * Radicalmicro
 *
 * @package   plgRadicalmicroContent
 * @since     __DEPLOY_VERSION__
 */
class plgRadicalMicroContent extends CMSPlugin
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
     *
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
        JLoader::register('plgRadicalMicroContentHelper', __DIR__ . '/src/Helpers/Helper.php');

        // Helper
        $this->helper = new plgRadicalMicroContentHelper($this->params);
    }


    /**
     * OnRadicalmicroRegisterTypes for init your types for each collection
     *
     * @since __DEPLOY_VERSION__
     */
    public function onRadicalMicroRegisterTypes()
    {
        // $path - absolute path of directory with your types of each collection
        //
        // PathHelper::getInstance()->register($path, 'schema');
        // PathHelper::getInstance()->register($path, 'schema_extra');
        // PathHelper::getInstance()->register($path, 'meta');
    }

    /**
     * OnRadicalmicroRegisterTypes for init your types for each collection
     *
     * @since __DEPLOY_VERSION__
     */
    public function onRadicalMicroLoadLanguages()
    {
        // For translate dynamic fields label you can load your language to main plugin
        //
        // Factory::getLanguage()->load('plg_radicalmicro_PLUGIN_NAME', JPATH_PLUGINS . '/radicalmicro/PLUGIN_NAME', null, true);
    }

    /**
     * Adds forms for override
     *
     * @param   Form   $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onContentPrepareForm(Form $form, $data)
    {
        // Check current plugin form edit
        if ($this->app->isClient('administrator') && $form->getName() === 'com_plugins.plugin')
        {
            $plugin = PluginHelper::getPlugin('radicalmicro', 'content');

            if ($this->app->input->getInt('extension_id') === (int) $plugin->id)
            {
                // Set Schema.org params fields
                $this->helper->setSchemaFields($form);

                // Set Meta params fields
                $this->helper->setMetaFields($form);
            }
        }

        $component = $this->app->input->get('option');
        $layout    = $this->app->input->get('layout');
        $view      = $this->app->input->get('view');

        // Check article edit form
        if ($this->app->isClient('administrator') && $component === 'com_content' && $view === 'article' && $layout === 'edit')
        {
            // Add fieldset for menu
            Form::addFormPath(__DIR__ . '/forms');
            $form->loadFile('content', true);

            // Set schema.org fields
            $this->helper->setSchemaFields($form, true);

            // Set meta fields
            $this->helper->setMetaFields($form, true);
        }

        return true;
    }

    /**
     * OnRadicalmicroProvider event
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function onRadicalmicroProvider($params)
    {
        // Get schema type
        $type = $this->params->get('type', 'article');

        // Get and set schema data
        $schemaObject = $this->helper->getSchemaObject();

        if ($schemaObject)
        {
            $schemaData   = TypesHelper::execute('schema', $type, $schemaObject);
            SchemaHelper::getInstance()->addChild('root', $schemaData);
        }

        // Get and set opengraph data
        $metaObject  = $this->helper->getMetaObject();

        if ($metaObject)
        {
            $collections = PathHelper::getInstance()->getTypes('meta');

            foreach ($collections as $collection)
            {
                $ogData = TypesHelper::execute('meta', $collection, $metaObject);
                OGHelper::getInstance()->addChild('root', $ogData);
            }
        }

        return;
    }
}

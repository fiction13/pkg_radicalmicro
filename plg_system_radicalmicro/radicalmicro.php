<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use RadicalMicro\Helpers\MainHelper;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\Tree\SchemaHelper;
use RadicalMicro\Helpers\TypesHelper;
use RadicalMicro\Helpers\XMLHelper;

#TODO Изменить изображение по умолчанию

/**
 * Radicalmicro
 *
 * @package   plgSystemRadicalmicro
 * @since     1.0.0
 */
class plgSystemRadicalMicro extends CMSPlugin
{
    /**
     * Application object
     *
     * @var    CMSApplication
     * @since  1.0.0
     */
    protected $app;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  1.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * OnAfterInitialise event
     *
     * Register RadicalMicro namespace.
     *
     * @since  1.0.0
     */
    public function onAfterInitialise()
    {
        JLoader::registerNamespace('RadicalMicro', __DIR__ . '/src', false, false, 'psr4');

        // Init collections
        $this->initCollections();

        // Init Languages (for dynamic field translation)
        if ($this->app->isClient('administrator'))
        {
            $this->initLanguages();
        }
    }

    /**
     * Adds forms for override
     *
     * @param   Form   $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  boolean
     *
     * @since   1.0
     */
    public function onContentPrepareForm(Form $form, $data)
    {
        // Check current plugin form edit
        if (!$this->app->isClient('administrator') || $form->getName() !== 'com_plugins.plugin')
        {
            return true;
        }

        $plugin = PluginHelper::getPlugin('system', 'radicalmicro');

        if ($this->app->input->getInt('extension_id') === (int) $plugin->id)
        {
            // Get all collections of types
            $collections = PathHelper::getInstance()->getTypes('meta');

            foreach ($collections as $collection)
            {
                $element = XMLHelper::createBoolField('meta_enable_' . $collection);
                $form->setField($element, null, false, 'meta');
            }
        }

        return true;
    }

    /**
     * OnAfterRender event.
     *
     * @return  bool|void
     *
     * @since   2.5
     */
    public function onAfterRender()
    {
        // Check site client
        if ($this->app->isClient('administrator') || $this->app->input->get('option') == 'com_ajax')
        {
            return false;
        }

        // Get provider plugins
        $params = $this->params;

        // Trigger for get data from provider plugins
        PluginHelper::importPlugin('radicalmicro');
        $this->app->triggerEvent('onRadicalMicroProvider', [$params]);

        // Set Schema.org and Opengraph to the end of body
        $body   = $this->app->getBody();
        $schema = $opengraph = '';

        // Add Schema.org
        if ($this->params->get('enable_schema', 1))
        {
            // Add website schema type
            if ($this->params->get('schema_enable_type_website', 0))
            {
                $websiteData = TypesHelper::execute('schema', 'website', []);
                SchemaHelper::getInstance()->addChild('root', $websiteData);
            }

            // Add logo schema type
            if ($this->params->get('schema_enable_type_organization', 0) && $logoUrl = $this->params->get('schema_type_organization_image'))
            {
                $logoData = TypesHelper::execute('schema', 'organization', ['image' => $logoUrl]);
                SchemaHelper::getInstance()->addChild('root', $logoData);
            }

            // Add breadcrumbs schema type
            if ($this->params->get('schema_enable_type_breadcrumblist', 0))
            {
                $breadcrumbsData = TypesHelper::execute('schema', 'breadcrumblist', []);
                SchemaHelper::getInstance()->addChild('root', $breadcrumbsData);
            }

            $schema = MainHelper::buildSchema($body, $this->params);
        }

        // Add Opengraph
        if ($this->params->get('enable_meta', 1))
        {
            $opengraph = MainHelper::buildOpengraph($body, $this->params);
        }

        // Place
        $place = '</' . $this->params->get('extra_insert_place', 'body') . '>';
        $textBefore = "\n<!-- RadicalMicro: start -->\n";
        $textAfter = "\n<!-- RadicalMicro: end -->\n";

        // Insert microdata
        $body = str_replace($place, $textBefore . $opengraph . $schema .  $textAfter . $place, $body);

        $this->app->setBody($body);
    }

    /**
     * Init types of each collections
     *
     * @throws Exception
     * @since 1.0.0
     */
    protected function initCollections()
    {
        // Trigger onRadicalMicroRegisterTypes to collect paths and register types classes
        PluginHelper::importPlugin('radicalmicro');
        Factory::getApplication()->triggerEvent('onRadicalMicroRegisterTypes');
    }

    /**
     * Init languages for plugins
     *
     * @throws Exception
     * @since 1.0.0
     */
    protected function initLanguages()
    {
        // Trigger onRadicalMicroRegisterTypes to collect paths and register types classes
        PluginHelper::importPlugin('radicalmicro');
        Factory::getApplication()->triggerEvent('onRadicalMicroLoadLanguages');
    }

}
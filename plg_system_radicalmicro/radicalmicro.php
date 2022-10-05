<?php
/*
 * @package   pkg_radicalmicro
 * @version   0.2.1
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

/**
 * Radicalmicro
 *
 * @package   plgSystemRadicalmicro
 * @since     __DEPLOY_VERSION__
 */
class plgSystemRadicalMicro extends CMSPlugin
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
     * OnAfterInitialise event
     *
     * Register RadicalMicro namespace.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function onAfterInitialise()
    {
        JLoader::registerNamespace('RadicalMicro', __DIR__ . '/src', false, false, 'psr4');

        // Init collections
        $this->initCollections();
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
                $element = XMLHelper::createBoolField('meta_enable_' . $collection, 1);
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
     * @since   __DEPLOY_VERSION__
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
            if ($this->params->get('schema_enable_type_organization', 0))
            {
                $organizationData = [
                    'image'               => $this->params->get('schema_type_organization_image'),
                    'title'               => $this->params->get('schema_type_organization_title'),
                    'addressCountry'      => $this->params->get('schema_type_organization_country'),
                    'addressLocality'     => $this->params->get('schema_type_organization_locality'),
                    'addressRegion'       => $this->params->get('schema_type_organization_region'),
                    'streetAddress'       => $this->params->get('schema_type_organization_address'),
                    'postalCode'          => $this->params->get('schema_type_organization_code'),
                    'postOfficeBoxNumber' => $this->params->get('schema_type_organization_post'),
                    'hasMap'              => $this->params->get('schema_type_organization_map'),
                    'phone'               => $this->params->get('schema_type_organization_phone'),
                    'contactType'         => $this->params->get('schema_type_organization_contact_type'),
                ];

                $organization = TypesHelper::execute('schema', 'organization', $organizationData);
                SchemaHelper::getInstance()->addChild('root', $organization);
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
        $place      = '</' . $this->params->get('extra_insert_place', 'body') . '>';
        $textBefore = "\n<!-- RadicalMicro: start -->\n";
        $textAfter  = "\n<!-- RadicalMicro: end -->\n";

        // Insert microdata
        $body = str_replace($place, $textBefore . $opengraph . $schema . $textAfter . $place, $body);

        $this->app->setBody($body);
    }

    /**
     * Init types of each collections
     *
     * @throws Exception
     * @since __DEPLOY_VERSION__
     */
    protected function initCollections()
    {
        // Trigger onRadicalMicroRegisterTypes to collect paths and register types classes
        PluginHelper::importPlugin('radicalmicro');
        Factory::getApplication()->triggerEvent('onRadicalMicroRegisterTypes');
    }
}
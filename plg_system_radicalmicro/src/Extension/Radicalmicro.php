<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\System\RadicalMicro\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\System\RadicalMicro\Helper\EventHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\MainHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\PathHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\Tree\SchemaHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\TypesHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\XMLHelper;

class Radicalmicro extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    bool
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise'    => 'onAfterInitialise',
            'onContentPrepareForm' => 'onContentPrepareForm',
            'onAfterRender'        => 'onAfterRender'
        ];
    }

    /**
     * OnAfterInitialise event
     *
     * Register RadicalMicro namespace.
     *
     * @since  0.2.2
     */
    public function onAfterInitialise()
    {
        // Init collections
        $this->initCollections();
    }

    /**
     * Adds forms for override
     *
     * @param   Event  $event  Event.
     *
     * @return  boolean
     *
     * @since   0.2.2
     */
    public function onContentPrepareForm(Event $event)
    {
        $form = $event->getArgument(0);
        $data = $event->getArgument(1);
        $app  = Factory::getApplication();

        // Check current plugin form edit
        if (!$app->isClient('administrator') || $form->getName() !== 'com_plugins.plugin')
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
     * @since   0.2.2
     */
    public function onAfterRender()
    {
        $app = Factory::getApplication();

        // Check site client
        if ($app->isClient('administrator') || $app->getInput()->get('option') == 'com_ajax')
        {
            return false;
        }

        // Get provider plugins
        $params = $this->params;

        // Trigger for get data from provider plugins
        EventHelper::provider($this, $params);

        // Set Schema.org and Opengraph to the end of body
        $body   = $app->getBody();
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

        $app->setBody($body);
    }

    /**
     * Init types of each collections
     *
     * @throws \Exception
     *
     * @since 0.2.2
     */
    protected function initCollections()
    {
        // Trigger onRadicalMicroRegisterTypes to collect paths and register types classes
        EventHelper::registerTypes($this);
    }
}
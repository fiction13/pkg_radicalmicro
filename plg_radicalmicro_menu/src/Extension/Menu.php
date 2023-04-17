<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalMicro\Menu\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\Plugin\System\RadicalMicro\Helper\PathHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\Tree\OGHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\Tree\SchemaHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\TypesHelper;
use Joomla\Plugin\RadicalMicro\Menu\Helper\MenuHelper;
use Joomla\CMS\Plugin\PluginHelper;

class Menu extends CMSPlugin implements SubscriberInterface
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
            'onContentPrepareForm'   => 'onContentPrepareForm',
            'onRadicalMicroProvider' => 'onRadicalMicroProvider'
        ];
    }

    /**
     * @param          $subject
     * @param   array  $config
     *
     * @throws \Exception
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

        // Check menu edit form
        if ($app->isClient('administrator') && $form->getName() === 'com_menus.item' && !in_array($data->type, ['heading', 'url', 'separator']))
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
     * OnRadicalMicroProvider event
     *
     * @param   Event  $event  Event.
     *
     * @return  void
     *
     * @since   0.2.2
     */
    public function onRadicalMicroProvider(Event $event)
    {
        $params = $event->getArgument('params');
        $menu   = Factory::getApplication()->getMenu()->getActive();

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
<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalMicro\Content\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\RadicalMicro\Content\Helper\ContentHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\CheckHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\PathHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\Tree\OGHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\Tree\SchemaHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\TypesHelper;
use Joomla\Registry\Registry;

class Content extends CMSPlugin implements SubscriberInterface
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
     * @var ContentHelper
     *
     * @since __DEPLOY_VERSION__
     */
    protected $helper;

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
        $this->helper = new ContentHelper($this->params);
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
        if ($app->isClient('administrator') && $form->getName() === 'com_plugins.plugin')
        {
            $plugin = PluginHelper::getPlugin('radicalmicro', 'content');

            if ($app->getInput()->getInt('extension_id') === (int) $plugin->id)
            {
                FormHelper::addFieldPrefix('RadicalMicro\\Provider\\Content\\Fields');

                // Set Schema.org params fields
                if (CheckHelper::isSchemaEnabled() && CheckHelper::isEnabled())
                {
                    $this->helper->setSchemaFields($form);
                }
                else
                {
                    $form->removeField('type', 'params');
                    $form->setFieldAttribute('schema_note', 'class', 'alert alert-danger w-100', 'params');
                    $form->setFieldAttribute('schema_note', 'description', Text::_('PLG_RADICALMICRO_CONTENT_PARAM_DISABLED_ERROR_SCHEMA'), 'params');
                }

                // Set Meta params fields
                if (CheckHelper::isMetaEnabled() && CheckHelper::isEnabled())
                {
                    $this->helper->setMetaFields($form);
                }
                else
                {
                    $form->setFieldAttribute('meta_note', 'class', 'alert alert-danger w-100', 'params');
                    $form->setFieldAttribute('meta_note', 'description', Text::_('PLG_RADICALMICRO_CONTENT_PARAM_DISABLED_ERROR_SCHEMA'), 'params');
                }
            }
        }

        // Check article edit form
        if ($app->isClient('administrator') && $form->getName() === 'com_content.article')
        {
            // Add fieldset for article
            $path = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name;
            Form::addFormPath($path . '/forms');
            $form->loadFile('content', true);

            // Add css
            Factory::getApplication()->getDocument()->addStyleDeclaration(
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

            // Set Schema.org params fields
            if (CheckHelper::isSchemaEnabled())
            {
                $this->helper->setSchemaFields($form, new Registry($data));
            }
            else
            {
                $form->removeField('radicalmicro_schema_content_type', 'attribs');
                $form->removeField('radicalmicro_schema_content_note', 'attribs');
                $form->setValue('radicalmicro_schema_content_enable', 'attribs', 0);
                $form->setFieldAttribute('radicalmicro_schema_content_enable', 'readonly', true, 'attribs');
            }

            // Set Meta params fields
            if (CheckHelper::isMetaEnabled())
            {
                $this->helper->setMetaFields($form, true);
            }
            else
            {
                $form->setValue('radicalmicro_meta_content_enable', 'attribs', 0);
                $form->setFieldAttribute('radicalmicro_meta_content_enable', 'readonly', true, 'attribs');
            }
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

        // Get schema type
        $type = $this->helper->getActualSchemaType();

        // Get and set schema data
        $schemaObject = $this->helper->getSchemaObject();

        if ($schemaObject)
        {
            $schemaData = TypesHelper::execute('schema', $type, $schemaObject);
            SchemaHelper::getInstance()->addChild('root', $schemaData);
        }

        // Get and set opengraph data
        $metaObject = $this->helper->getMetaObject();

        if ($metaObject)
        {
            $collections = PathHelper::getInstance()->getTypes('meta');

            foreach ($collections as $collection)
            {
                $ogData = TypesHelper::execute('meta', $collection, $metaObject);
                OGHelper::getInstance()->addChild('root', $ogData);
            }
        }
    }
}
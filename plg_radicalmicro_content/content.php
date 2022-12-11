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
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use RadicalMicro\Helpers\Tree\SchemaHelper;
use RadicalMicro\Helpers\Tree\OGHelper;
use RadicalMicro\Helpers\TypesHelper;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\CheckHelper;
use RadicalMicro\Provider\Content\Helpers\ContentHelper;

/**
 * Radicalmicro
 *
 * @package   plgRadicalmicroContent
 * @since     0.2.2
 */
class plgRadicalMicroContent extends CMSPlugin
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
     *
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
        $this->helper = new ContentHelper($this->params);
    }


    /**
     * OnRadicalmicroRegisterTypes for init your types for each collection
     *
     * @since 0.2.2
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
     * @since 0.2.2
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
     * @since   0.2.2
     */
    public function onContentPrepareForm(Form $form, $data)
    {
        // Check current plugin form edit
        if ($this->app->isClient('administrator') && $form->getName() === 'com_plugins.plugin')
        {
            $plugin = PluginHelper::getPlugin('radicalmicro', 'content');

            if ($this->app->input->getInt('extension_id') === (int) $plugin->id)
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
        if ($this->app->isClient('administrator') && $form->getName() === 'com_content.article')
        {
            // Add fieldset for article
            FormHelper::addFormPath(__DIR__ . '/forms');
            $form->loadFile('content', true);

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
     * OnRadicalmicroProvider event
     *
     * @return bool
     *
     * @since  0.2.2
     */
    public function onRadicalMicroProvider($params)
    {
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

        return true;
    }
}

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
use RadicalMicro\Helpers\ImageHelper;

#TODO Изменить изображение по умолчанию
#TODO Добавить разметку Schema.org для хлебных крошек
#TODO Добавить разметку Facebook
#TODO Добавить удаление существующих разметок

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
     *
     * @return string||bool
     *
     * @since         1.0.0
     */
    public function onAjaxRadicalMicro()
    {
        $task = $this->app->input->get('task');

        switch ($task)
        {
            case 'image':
                //redirect to image
                $this->app->redirect(ImageHelper::getInstance()->generate(), 302);
        }

        return true;
    }

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
    }

    /**
     * Adds forms for override
     *
     * @param   JForm  $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  boolean
     *
     * @since   1.0
     */
    public function onContentPrepareForm(Form $form, $data)
    {
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
        if ($this->app->isClient('administrator'))
        {
            return false;
        }

        // Get provider plugins
        $params = $this->params;

        // Trigger for data providers
        PluginHelper::importPlugin('radicalmicro');
        $this->app->triggerEvent('onRadicalMicroProvider', [$params]);

        // Set Schema.org and Opengraph to the end of body
        $body   = $this->app->getBody();
        $schema = $opengraph = '';

        // Add Schema.org
        if ($this->params->get('enable_schema', 1))
        {
            // Add website schema type
            if ($this->params->get('schema_enable_type_website', 1))
            {
                $websiteData = TypesHelper::execute('schema', 'website', []);
                SchemaHelper::getInstance()->addChild('root', $websiteData);
            }

            // Add logo schema type
            if ($this->params->get('schema_enable_type_logo', 1) && $logoUrl = $this->params->get('schema_type_logo_image'))
            {
                $logoData = TypesHelper::execute('schema', 'logo', ['image' => $logoUrl]);
                SchemaHelper::getInstance()->addChild('root', $logoData);
            }

            $schema = MainHelper::buildSchema($body);
        }

        // Add Opengraph
        if ($this->params->get('enable_meta', 1))
        {
            $opengraph = MainHelper::buildOpengraph($body);
        }

        $body = str_replace("</body>", $opengraph . $schema . "</body>", $body);

        $this->app->setBody($body);
    }


    /**
     * @param   Form    $form
     * @param   object  $data
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function addRadicalMicroTypeXML(Form $form, $data)
    {
        $extraFields  = PathHelper::getInstance()->getTypes('schema_extra');
        $metaFields   = PathHelper::getInstance()->getTypes('meta');
        $schemaFields = PathHelper::getInstance()->getTypes('schema');

        if ($extraFields)
        {
            foreach ($extraFields as $field)
            {
                $element = XMLHelper::createBoolField('enable_' . $field);

                $form->setField($element, null, true, 'schema');

                // Check field extra config

                $configFields = TypesHelper::getConfig('schema', $field);

                if ($configFields)
                {
                    foreach ($configFields as $key => $value)
                    {
                        $element = XMLHelper::createField('enable_' . $field . '_' . $key, 'enable_' . $field, '', $value);

                        $form->setField($element, null, true, 'schema');
                    }
                }
            }
        }

        // Add meta fields
        if ($metaFields)
        {
            foreach ($metaFields as $field)
            {
                $element = XMLHelper::createBoolField('enable_' . $field);
                $form->setField($element, null, true, 'meta');
            }
        }

        // Add options to schema type select
        if ($schemaFields)
        {
            $element = XMLHelper::createField('type', '', 'list', $schemaFields[0], $schemaFields);
            $form->setField($element, null, true, 'schema');
        }

        return true;
    }

    /**
     * Init types of each collections
     *
     * @throws Exception
     * @since 1.0.0
     */
    public function initCollections()
    {
        // Trigger onRadicalMicroRegisterTypes to collect paths and register types classes
        PluginHelper::importPlugin('radicalmicro');
        Factory::getApplication()->triggerEvent('onRadicalMicroRegisterTypes');
    }

}
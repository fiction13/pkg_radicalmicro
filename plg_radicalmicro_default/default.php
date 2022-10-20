<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use RadicalMicro\Helpers\Tree\OGHelper;
use RadicalMicro\Helpers\TypesHelper;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\UtilityHelper;
use RadicalMicro\Helpers\XMLHelper;

/**
 * Radicalmicro
 *
 * @package   pkgRadicalMicro
 * @since     __DEPLOY_VERSION__
 */
class plgRadicalMicroDefault extends CMSPlugin
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
        if ($this->app->isClient('administrator') && $form->getName() == 'com_plugins.plugin')
        {
            $plugin = PluginHelper::getPlugin('radicalmicro', 'default');

            if ($this->app->input->getInt('extension_id') === (int) $plugin->id)
            {
                // Create simple xml element
                $element = new SimpleXMLElement('<field />');

                $element->addAttribute('name', 'title');
                $element->addAttribute('label', Text::_('PLG_RADICALMICRO_DEFAULT_PARAM_TITLE'));
                $element->addAttribute('type', 'text');
                $element->addAttribute('default', Text::_('PLG_RADICALMICRO_DEFAULT_PARAM_TITLE_VALUE'));
                $element->addAttribute('disabled', true);

                $form->setField($element, null, true, 'basic');
            }
        }

        return true;
    }

    /**
     * OnRadicalMicroProvider event
     *
     * @return array|void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function onRadicalMicroProvider($params)
    {
        // Data object
        $object = new stdClass();

        // Set title
        $object->title = Factory::getDocument()->getTitle();

        // Set description
        if ($this->params->get('description') !== 'none')
        {
            $object->description = Factory::getDocument()->getDescription();
        }

        // Set image
        if ($this->params->get('image_choice') === 'static')
        {
            $object->image = $this->params->get('image');
        }
        else if ($this->params->get('image_choice') === 'body')
        {
            list(, $body) = explode('<body', $this->app->getBody());
            $object->image = UtilityHelper::getFirstImage($body);
        }

        // Get and set opengraph data
        $collections = PathHelper::getInstance()->getTypes('meta');

        foreach ($collections as $collection)
        {
            $ogData = TypesHelper::execute('meta', $collection, $object, 0.4);
            OGHelper::getInstance()->addChild('root', $ogData);
        }
    }
}

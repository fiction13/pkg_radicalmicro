<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
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
use RadicalMicro\Helpers\TypesHelper;

/**
 * Radicalmicro
 *
 * @package   plgRadicalmicroContent
 * @since     1.0.0
 */
class plgRadicalmicroMenu extends CMSPlugin
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
     * @param   JForm  $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  boolean
     *
     * @since   1.0
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

            // Set fields
            $this->helper->setMetaFields($form);
        }

        return true;
    }

    /**
     * OnRadicalmicroProvider event
     *
     * @return array|void
     *
     * @since  1.0.0
     */
    public function onRadicalmicroProvider($params)
    {
        $object = $this->helper->getProviderData();

        if (!$object)
        {
            return;
        }

        // Check enable menu
        if (!$this->params->get('radicalmicro_menu_enable'))
        {
            return;
        }

        // Get and set opengraph data
        $collections = PathHelper::getInstance()->getTypes('meta');

        foreach ($collections as $collection)
        {
            $ogData = TypesHelper::execute('meta', $collection, $object, 0.6);
            OGHelper::getInstance()->addChild('root', $ogData);
        }
    }
}

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

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\Tree\OGHelper;
use RadicalMicro\Helpers\TypesHelper;
use RadicalMicro\Addon\Image\Helpers\ImageHelper;

/**
 * Radicalmicro
 *
 * @package   plgRadicalmicroContent
 * @since     __DEPLOY_VERSION__
 */
class plgRadicalMicroImage extends CMSPlugin
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
     * @param          $subject
     * @param   array  $config
     *
     * @since  __DEPLOY_VERSION__
     *
     * @throws Exception
     */
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        // Helper
        $this->helper = new ImageHelper($this->params);
    }

    /**
     *
     * @return string||bool
     *
     * @since         __DEPLOY_VERSION__
     */
    public function onAjaxRadicalMicroImage()
    {
        $task = $this->app->input->get('task');

        switch ($task)
        {
            case 'generate':
                //redirect to image
                $this->app->redirect($this->helper->generate(), 302);
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
        $object = $this->helper->getProviderData();

        if (!$object)
        {
            return;
        }

        // Get and set opengraph data
        $collections = PathHelper::getInstance()->getTypes('meta');

        foreach ($collections as $collection)
        {
            $ogData = TypesHelper::execute('meta', $collection, $object);
            OGHelper::getInstance()->addChild('root', $ogData);
        }
    }
}

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
class plgRadicalMicroImage extends CMSPlugin
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
     * @since  1.0.0
     *
     * @throws Exception
     */
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        // Include helper
        JLoader::register('plgRadicalMicroImageHelper', __DIR__ . '/src/Helpers/Helper.php');

        // Helper
        $this->helper = new plgRadicalMicroImageHelper($this->params);
    }

    /**
     *
     * @return string||bool
     *
     * @since         1.0.0
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
     * @since  1.0.0
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

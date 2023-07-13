<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalMicro\Image\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\System\RadicalMicro\Helper\PathHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\Tree\OGHelper;
use Joomla\Plugin\System\RadicalMicro\Helper\TypesHelper;
use Joomla\Plugin\RadicalMicro\Image\Helper\ImageHelper;

class Image extends CMSPlugin implements SubscriberInterface
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
     * @var ImageHelper
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
            'onAjaxImage'            => 'onAjaxImage',
            'onRadicalMicroProvider' => 'onRadicalMicroProvider'
        ];
    }

    /**
     * @param          $subject
     * @param   array  $config
     *
     * @throws \Exception
     * @since  0.2.2
     *
     */
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        // Helper
        $this->helper = new ImageHelper($this->params);
    }

    /**
     *
     * @return string|bool
     *
     * @since         0.2.2
     */
    public function onAjaxImage()
    {
        $app  = Factory::getApplication();
        $task = $app->getInput()->get('task');

        switch ($task)
        {
            case 'generate':
                //redirect to image
                $app->redirect($this->helper->generate(), 302);
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
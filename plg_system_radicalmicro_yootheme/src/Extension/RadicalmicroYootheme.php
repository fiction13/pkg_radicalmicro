<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\System\RadicalMicroYootheme\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use YOOtheme\Application;

class RadicalmicroYootheme extends CMSPlugin implements SubscriberInterface
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
            'onAfterInitialise' => 'onAfterInitialise'
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
        // Check if YOOtheme Pro is loaded
        if (!class_exists(Application::class, false))
        {
            return;
        }

        // Load a single module from the same directory
        $app = Application::getInstance();
        $app->load(__DIR__ . '/bootstrap.php');
    }
}
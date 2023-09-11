<?php
/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\System\RadicalMicro\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

class EventHelper
{
    /**
     * @var array
     *
     * @since __DEPLOY_VERSION__
     */
    public static $events = [
        'registerTypes' => 'onRadicalMicroRegisterTypes',
        'provider'      => 'onRadicalMicroProvider',
        'beforeBuild'   => 'onRadicalMicroBeforeBuild',
    ];


    /**
     * Method for register Radical Micro types.
     *
     * @param   object  $subject  Event subject.
     *
     * @return boolean
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function registerTypes(object $subject)
    {
        // Process the RadicalMart Import plugins
        PluginHelper::importPlugin('radicalmicro');

        // Trigger event
        Factory::getApplication()->getDispatcher()->dispatch(
            self::$events[__FUNCTION__],
            AbstractEvent::create(
                self::$events[__FUNCTION__],
                [
                    'subject' => $subject
                ]
            )
        );

        return true;
    }

    /**
     * Method for get data from source by offset.
     *
     * @param   object    $subject  Event subject.
     * @param   Registry  $params   Plugin params.
     *
     * @return  boolean
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function provider($subject, $params)
    {
        // Process the RadicalMart plugins
        PluginHelper::importPlugin('radicalmicro');

        // Trigger event
        Factory::getApplication()->getDispatcher()->dispatch(
            self::$events[__FUNCTION__],
            AbstractEvent::create(
                self::$events[__FUNCTION__],
                [
                    'subject' => $subject,
                    'params'  => $params,
                ]
            )
        );

        return true;
    }

    /**
     * Method for get data from source by offset.
     *
     * @param   object  $subject  Event subject.
     * @param   string  $context  Context.
     * @param   array   $data     Array of data
     *
     * @return  boolean
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function beforeBuild(object $subject, string $context, array &$data)
    {
        // Process the RadicalMart plugins
        PluginHelper::importPlugin('radicalmicro');

        // Trigger event
        Factory::getApplication()->getDispatcher()->dispatch(
            self::$events[__FUNCTION__],
            AbstractEvent::create(
                self::$events[__FUNCTION__],
                [
                    'subject' => $subject,
                    'context' => $context,
                    'data'    => &$data
                ]
            )
        );

        return true;
    }
}

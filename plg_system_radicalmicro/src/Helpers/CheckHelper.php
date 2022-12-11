<?php
/*
 * @package   pkg_radicalmicro
 * @version   0.2.4
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

class CheckHelper
{
    /**
     * @param   string  $text
     * @param   int     $limit
     *
     *
     * @since 0.2.2
     */
    public static function isEnabled()
    {
        return PluginHelper::isEnabled('system', 'radicalmicro');
    }

    /**
     * @param   string  $text
     * @param   int     $limit
     *
     *
     * @since 0.2.2
     */
    public static function isMetaEnabled()
    {
        $plugin = PluginHelper::getPlugin('system', 'radicalmicro');

        $params = new Registry($plugin->params);

        return $params->get('enable_meta', 0);
    }

    /**
     * @param   string  $text
     * @param   int     $limit
     *
     *
     * @since 0.2.2
     */
    public static function isSchemaEnabled()
    {
        $plugin = PluginHelper::getPlugin('system', 'radicalmicro');
        $params = new Registry($plugin->params);

        return $params->get('enable_schema', 0);
    }
}
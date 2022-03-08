<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Helpers;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

final class ParamsHelper
{
    /**
     * @var
     * @since 1.0.0
     */
    protected static $instance;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    Registry
     * @since  1.0.0
     */
    protected $params = '';

    /**
     *
     * @return mixed|ImageHelper
     *
     * @since 1.0.0
     */
    public static function getInstance()
    {
        if (is_null(static::$instance))
        {
            $instance = new self();
        }

        return $instance;
    }

    /**
     *
     * @return Registry
     *
     * @since 1.0.0
     */
    public function getParams()
    {
        if (!$this->params)
        {
            $plugin = PluginHelper::getPlugin('system', 'radicalmicro');

            // Check if plugin is enabled
            if ($plugin)
            {
                // Get plugin params
                return new Registry($plugin->params);
            }
            else
            {
                throw new Exception(Text::_('PLG_SYSTEM_RADICALMICRO_PLUGIN_DISABLED'));
            }
        }

        return $this->params;
    }

    /**
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getDefaultSiteName()
    {
        return $this->getParams()->get('meta_site_name') ?? Factory::getConfig()->get('sitename');
    }

    /**
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getDefaultSiteDescription()
    {
        return $this->getParams()->get('meta_site_description') ?? Factory::getConfig()->get('MetaDesc');
    }
}
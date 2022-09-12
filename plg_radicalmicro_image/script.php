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
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;

class PlgRadicalMicroImageInstallerScript
{
    /**
     * Runs right after any installation action.
     *
     * @param   string            $type    Type of PostFlight action. Possible values are:
     * @param   InstallerAdapter  $parent  Parent object calling object.
     *
     * @return  boolean True on success, False on failure.
     *
     * @throws  Exception
     *
     * @since   __DEPLOY_VERSION__
     */
    function postflight($type, $parent)
    {
        // Enable plugin
        if ($type == 'install')
        {
            $this->enablePlugin($parent);
        }

        return true;
    }

    /**
     * Enable plugin after installation.
     *
     * @param   InstallerAdapter  $parent  Parent object calling object.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function enablePlugin($parent)
    {
        // Prepare plugin object
        $plugin           = new stdClass();
        $plugin->type     = 'plugin';
        $plugin->element  = $parent->getElement();
        $plugin->folder   = (string) $parent->getParent()->manifest->attributes()['group'];
        $plugin->ordering = 1000;
        $plugin->enabled  = 1;

        // Update record
        Factory::getDbo()->updateObject('#__extensions', $plugin, array('type', 'element', 'folder'));
    }
}
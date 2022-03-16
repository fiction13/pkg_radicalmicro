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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

class plgRadicalMicroImageInstallerScript
{

    /**
     * @param $type
     * @param $parent
     *
     * @return false|void
     *
     * @throws Exception
     * @since version
     */
    function postflight($type, $parent)
    {
        $db = Factory::getDbo();

        // Get params
        $params = $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('radicalmicro'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('image'))
        )->loadResult();

        $params = json_decode($params, true);

        // Set secret key

        if (!isset($params['imagetype_generate_secret_key']))
        {
            $params['imagetype_generate_secret_key'] = uniqid(rand());

            // Prepare plugin object
            $plugin          = new stdClass();
            $plugin->type    = 'plugin';
            $plugin->element = $parent->getElement();
            $plugin->folder  = (string) $parent->getParent()->manifest->attributes()['group'];
            $plugin->params  = json_encode($params);

            // Update
            Factory::getDbo()->updateObject('#__extensions', $plugin, array('type', 'element', 'folder'));
        }
    }
}
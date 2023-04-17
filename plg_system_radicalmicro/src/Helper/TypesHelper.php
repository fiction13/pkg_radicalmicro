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

class TypesHelper
{
    /**
     * @param   string  $collectionType  - schema, meta, extra
     * @param           $type            - type of metadata
     * @param           $data            - config of the metadata
     * @param   float   $priority
     *
     * @return array|void
     *
     * @since 0.2.2
     */
    public static function execute(string $collectionType, $type, $data, $priority = 0.5)
    {
        if (empty($type))
        {
            return;
        }

        $class_name = '\\Joomla\\Plugin\\System\\RadicalMicro\\Type\\Collection\\' . ucfirst($collectionType) . '\\' . ucfirst($type);

        if (!class_exists($class_name))
        {
            $class_name = '\\Joomla\\Plugin\\System\\RadicalMicro\\Type\\Collection\\' . ucfirst($collectionType) . '\\Extra\\' . ucfirst($type);

            if (!class_exists($class_name))
            {
                return array();
            }
        }

        $typeClass = new $class_name($data);

        $result = $typeClass->execute($data, $priority);

        return $result;
    }

    /**
     * Get config for type of collection type
     *
     * @param $collectionType  - schema, meta, extra
     * @param $type            - type of metadata
     *
     * @return array
     *
     * @since 0.2.2
     */
    public static function getConfig($collectionType, $type, $addUid = true)
    {
        $class_name = '\\Joomla\\Plugin\\System\\RadicalMicro\\Type\\Collection\\' . ucfirst($collectionType) . '\\' . ucfirst($type);

        if (!class_exists($class_name))
        {
            $class_name = '\\Joomla\\Plugin\\System\\RadicalMicro\\Type\\Collection\\' . ucfirst($collectionType) . '\\Extra\\' . ucfirst($type);

            if (!class_exists($class_name))
            {
                return array();
            }
        }

        $typeClass = new $class_name();

        $result = $typeClass->getConfig($addUid);

        return $result;
    }
}
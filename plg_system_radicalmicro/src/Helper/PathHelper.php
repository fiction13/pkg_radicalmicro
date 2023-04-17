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

use JLoader;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

final class PathHelper
{
    /**
     * @var
     * @since 0.2.2
     */
    protected static $instance;

    /**
     * A list of paths
     *
     * @var array
     * @since 0.2.2
     */
    protected $_paths = array(
        'meta'         => [
            JPATH_PLUGINS . '/system/radicalmicro/src/Type/Collection/Meta'
        ],
        'schema'       => [
            JPATH_PLUGINS . '/system/radicalmicro/src/Type/Collection/Schema'
        ],
        'schema_extra' => [
            JPATH_PLUGINS . '/system/radicalmicro/src/Type/Collection/Schema/Extra'
        ]
    );

    /**
     * @var array
     * @since 0.2.2
     */
    protected $_collections = [];

    /**
     *
     * @return mixed|PathHelper
     *
     * @since 0.2.2
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
     * Register a path and register classes for new path
     *
     * @param   string  $path            The path to register
     * @param   string  $collectionType  Collection type - schema, meta, extra
     *
     * @since 0.2.2
     */
    public function register($path, $collectionType = 'schema')
    {
        if (!is_string($path))
        {
            return;
        }

        if (!isset($this->_paths[$collectionType]))
        {
            $this->_paths[$collectionType] = array();
        }

        $this->_paths[$collectionType][] = $path;

        // Register path classes
//        $this->registerClasses($path);
    }

    /**
     * Get all collected types
     *
     * @param $type  - schema, meta, extra
     *
     * @return array
     *
     * @since 0.2.2
     */
    public function getTypes($type)
    {
        if (!isset($this->_collections[$type]))
        {
            $result = [];
            $paths  = $this->_paths[$type];

            if ($paths)
            {
                foreach ($paths as $path)
                {
                    if (Folder::exists($path))
                    {
                        $files = Folder::files($path, '.php');

                        if ($files)
                        {
                            foreach ($files as $file)
                            {
                                $result[] = lcfirst(File::stripExt($file));
                            }
                        }
                    }
                }

                $this->_collections[$type] = $result;
            }
        }

        return $this->_collections[$type];
    }

    /**
     * Register all classes inside directory
     *
     * @param $type
     *
     * @return array|void
     *
     * @since 0.2.2
     */
    public function registerClasses($path)
    {
        if (Folder::exists($path))
        {
            $files = Folder::files($path, '.php', false, true);

            foreach ($files as $file)
            {
                $className = basename($file, '.php');

                JLoader::register($className, $file);
                JLoader::load($className);
            }
        }

        return;
    }
}

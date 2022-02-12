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

use JLoader;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Uri\Uri;

class PathHelper {

	/**
	 * A list of paths
	 *
	 * @var array
	 * @since 1.0.0
	 */
    protected $_paths = array(
		'meta' => [
			JPATH_PLUGINS.'/system/radicalmicro/src/Types/Collections/Meta'
		],
	    'schema' => [
			JPATH_PLUGINS.'/system/radicalmicro/src/Types/Collections/Schema'
	    ],
	    'extra' => [
			JPATH_PLUGINS.'/system/radicalmicro/src/Types/Collections/Schema/Extra'
	    ]
    );


	/**
	 * @var array
	 * @since 1.0.0
	 */
	protected $_collections = [];

	/**
	 *
	 * @return mixed|PathHelper
	 *
	 * @since 1.0.0
	 */
	public static function getInstance()
    {
        static $instance;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }


	/**
	 * Register a path and register classes for new path
	 *
	 * @param string $path The path to register
	 * @param string $collectionType Collection type - schema, meta, extra
	 *
	 * @since 1.0.0
	 */
	public function register($path, $collectionType = 'schema') {

		if (!is_string($path)) {
			return;
		}

	    if (!isset($this->_paths[$collectionType])) {
	        $this->_paths[$collectionType] = array();
	    }

	    $this->_paths[$collectionType][] = $path;

		// Register path classes
		$this->registerClasses($path);
	}


	/**
	 * Get all collected types
	 *
	 * @param $type - schema, meta, extra
	 *
	 * @return array
	 *
	 * @since 1.0.0
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
	 * @return array
	 *
	 * @since 1.0.0
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
	}

}

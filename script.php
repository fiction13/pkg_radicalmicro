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

class pkg_radicalmicroInstallerScript
{
	/**
	 * Minimum PHP version required to install the extension
	 *
	 * @var  string
	 *
	 * @since  1.4.0
	 */
	protected $minimumPhp = '7.0';

	/**
	 * Minimum Joomla! version required to install the extension
	 *
	 * @var  string
	 *
	 * @since  1.4.0
	 */
	protected $minimumJoomla = '3.9.0';

	/**
	 * Method to check compatible.
	 *
	 * @throws  Exception
	 *
	 * @since  1.4.0
	 */
	function preflight()
	{
		// Check old Joomla!
		if (!class_exists('Joomla\CMS\Version'))
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('PKG_RADICALMICRO_ERROR_COMPATIBLE_JOOMLA',
				$this->minimumJoomla), 'error');

			return false;
		}

		$app      = Factory::getApplication();
		$jversion = new Version();

		// Check PHP
		if (!(version_compare(PHP_VERSION, $this->minimumPhp) >= 0))
		{
			$app->enqueueMessage(Text::sprintf('PKG_RADICALMICRO_ERROR_COMPATIBLE_PHP',
				$this->minimumPhp), 'error');

			return false;
		}

		// Check Joomla version
		if (!$jversion->isCompatible($this->minimumJoomla))
		{
			$app->enqueueMessage(Text::sprintf('PKG_RADICALMICRO_ERROR_COMPATIBLE_JOOMLA',
				$this->minimumJoomla), 'error');

			return false;
		}

		return true;
	}
}
<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use YOOtheme\Application;

/**
 * Radicalmicro_Yootheme
 *
 * @package   plgSystemRadicalmicro_Yootheme
 * @since     1.0.0
 */
class plgSystemRadicalMicro_Yootheme extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0.0
	 */
	protected $app;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * OnAfterInitialise event
	 *
	 * Register RadicalMicro namespace.
	 *
	 * @since  1.0.0
	 */
	public function onAfterInitialise()
	{
		// Check if YOOtheme Pro is loaded
		if (!class_exists(Application::class, false))
		{
			return;
		}

		// Register namespace
		JLoader::registerNamespace('RadicalMicroYootheme', __DIR__ . '/src', false, false, 'psr4');

		// Load a single module from the same directory
		$app = Application::getInstance();
		$app->load(__DIR__ . '/bootstrap.php');
	}

}
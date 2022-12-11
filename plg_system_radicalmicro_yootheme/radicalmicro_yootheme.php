<?php
/*
 * @package   pkg_radicalmicro
 * @version   0.2.4
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;
use YOOtheme\Application;

/**
 * Radicalmicro_Yootheme
 *
 * @package   plgSystemRadicalmicro_Yootheme
 * @since     0.2.2
 */
class plgSystemRadicalMicro_Yootheme extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  0.2.2
	 */
	protected $app;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  0.2.2
	 */
	protected $autoloadLanguage = true;

	/**
	 * OnAfterInitialise event
	 *
	 * Register RadicalMicro namespace.
	 *
	 * @since  0.2.2
	 */
	public function onAfterInitialise()
	{
		// Check if YOOtheme Pro is loaded
		if (!class_exists(Application::class, false))
		{
			return;
		}

		// Load a single module from the same directory
		$app = Application::getInstance();
		$app->load(__DIR__ . '/bootstrap.php');
	}

}
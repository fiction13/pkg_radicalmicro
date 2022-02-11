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
use RadicalMicro\Helpers\MainHelper;
use RadicalMicro\Helpers\ProviderHelper;
use YOOtheme\Application;

/**
 * Radicalmicro
 *
 * @package   plgSystemRadicalmicro
 * @since     1.0.0
 */
class plgSystemRadicalMicro extends CMSPlugin
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
		JLoader::registerNamespace('RadicalMicro', __DIR__ . '/src', false, false, 'psr4');

		// Init Yootheme Pro
		$this->initYoothemePro();
	}

	/**
	 * OnAfterRender event.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onAfterRender()
	{
		// Check site client
		if ($this->app->isClient('administrator'))
		{
			return false;
		}

		// Get provider plugins
		$params = $this->params;

		PluginHelper::importPlugin('radicalmicro');
		Factory::getApplication()->triggerEvent('onRadicalmicroProvider', [$params]);

		// Set Schema.org and Opengraph to the end of body
		$body   = $this->app->getBody();
		$schema = $og = '';

		// Add Schema.org
		if ($this->params->get('enable_schema', 1))
		{
			// Add website schema
			if ($this->params->get('schema_add_website_type', 1))
			{
				ProviderHelper::website();
			}

			// Add logo schema
			if ($this->params->get('schema_add_logo_type', 1) && $logoUrl = $this->params->get('schema_add_logo_type_image'))
			{
				ProviderHelper::logo($logoUrl);
			}

			$schema = MainHelper::buildSchema($body);
		}

		// Add Opengraph
		if ($this->params->get('enable_og', 1))
		{
			$opengraph = MainHelper::buildOpengraph($body);
		}

		$body = str_replace("</body>", $opengraph . $schema . "</body>", $body);

		$this->app->setBody($body);
	}


	/**
	 * Init Yootheme Pro if loaded
	 *
	 * @since version
	 */
	public function initYoothemePro()
	{
		// Check if YOOtheme Pro is loaded
        if (!class_exists(Application::class, false)) {
            return;
        }

        // Load a single module from the same directory
        $app = Application::getInstance();
        $app->load(__DIR__ . '/bootstrap.php');
	}
}
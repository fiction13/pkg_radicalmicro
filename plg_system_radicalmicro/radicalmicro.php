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
		JLoader::registerNamespace('RadicalMicro', __DIR__ . '/src');
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

		$data = [];

		// Get provider plugins
		PluginHelper::importPlugin('radicalmicro');
		Factory::getApplication()->triggerEvent('onRadicalmicroProvider', [&$data]);

        // Set Schema.org and Opengraph to the end of body
		$body      = $this->app->getBody();
		$schema    = MainHelper::buildSchema($body);
		$opengraph = MainHelper::buildOpengraph($body);

		$body = str_replace("</body>", $opengraph . $schema . "</body>", $body);

		$this->app->setBody($body);
	}
}
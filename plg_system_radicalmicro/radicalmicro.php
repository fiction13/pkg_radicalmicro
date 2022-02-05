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
use Joomla\CMS\Uri\Uri;
use RadicalMicro\Helpers\MainHelper;
use RadicalMicro\Helpers\SchemaHelper;

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

		// Add website schema

		if ($this->params->get('add_website_type', 1))
		{
			$data[] = $this->getWebsiteObject();
		}

		// Add logo schema

		if ($this->params->get('add_logo_type', 1) && $this->params->get('add_logo_type_image'))
		{
			$data[] = $this->getLogoObject();
		}

		// No data
		if (empty($data))
		{
			return;
		}

        // Set Schema.org and Opengraph to the end of body
		$body      = $this->app->getBody();
		$schema    = MainHelper::buildSchema($body, $data);
		$opengraph = MainHelper::buildOpengraph($body, $data);

		$body = str_replace("</body>", $opengraph . $schema . "</body>", $body);

		$this->app->setBody($body);
	}

	// Get website object

	public function getWebsiteObject()
	{
		$object = new stdClass();
		$object->type = 'website';

		return $object;
	}

	// Get logo object

	public function getLogoObject()
	{
		$object = new stdClass();
		$object->type = 'logo';
		$object->logo = Uri::root().ltrim($this->params->get('add_logo_type_image'), '/');

		return $object;
	}
}
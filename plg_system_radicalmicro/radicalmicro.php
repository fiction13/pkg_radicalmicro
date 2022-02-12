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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use RadicalMicro\Helpers\MainHelper;
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\ProviderHelper;
use RadicalMicro\Helpers\TypesHelper;
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

		// Init collections
		$this->initCollections();
	}

	/**
	 * Adds forms for override
	 *
	 * @param  JForm $form The form to be altered.
	 * @param  mixed $data The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function onContentPrepareForm(Form $form, object $data)
	{
		// Extend JForm plugins
		$this->addRadicalMicroTypeXML($form, $data);

		// Extend JForm menu
		#TODO Extend menu JForm
		$this->addRadicalMicroMenuXML($form, $data);

		return true;
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

		// Trigger for data providers
		PluginHelper::importPlugin('radicalmicro');
		Factory::getApplication()->triggerEvent('onRadicalmicroProvider', [$params]);

		// Set Schema.org and Opengraph to the end of body
		$body   = $this->app->getBody();
		$schema = $opengraph = '';

		// Add Schema.org
		if ($this->params->get('enable_schema', 1))
		{
			// Add website schema
			if ($this->params->get('schema_add_website_type', 1))
			{
				// ProviderHelper::website();
			}

			// Add logo schema
			if ($this->params->get('schema_add_logo_type', 1) && $logoUrl = $this->params->get('schema_add_logo_type_image'))
			{
				// ProviderHelper::logo($logoUrl);
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


	/**
	 * @param   Form    $form
	 * @param   object  $data
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function addRadicalMicroTypeXML(Form $form, object $data)
	{
		return true;
	}


	/**
	 * @param   Form    $form
	 * @param   object  $data
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function addRadicalMicroMenuXML(Form $form, object $data)
	{
		return true;
	}


	/**
	 * Init types of each collections
	 *
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function initCollections()
	{
		// Trigger onRadicalmicroRegisterTypes to collect paths and register types classes
		PluginHelper::importPlugin('radicalmicro');
		Factory::getApplication()->triggerEvent('onRadicalmicroRegisterTypes');
	}
}
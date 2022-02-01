<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

// Radical Micro Class

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
	 * Database object
	 *
	 * @var    DatabaseDriver
	 * @since  1.0.0
	 */
	protected $db;


	/**
	 * OnAfterRender event.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onAfterRender()
	{	
		if ($this->app->isClient('administrator'))
		{
			return false;
		}

		// Get integrations plugins

		PluginHelper::importPlugin('radicalmicro_integration');

    	$this->app->triggerEvent('onRenderRadicalMicro');
	}
}
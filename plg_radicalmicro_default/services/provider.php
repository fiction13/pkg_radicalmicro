<?php

/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\RadicalMicro\Standard\Extension\Standard;

return new class implements ServiceProviderInterface {

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function register(Container $container)
	{
		$container->set(PluginInterface::class,
			function (Container $container) {
				$plugin  = \Joomla\CMS\Plugin\PluginHelper::getPlugin('radicalmicro', 'default');
				$subject = $container->get(DispatcherInterface::class);

				$plugin = new Standard($subject, (array) $plugin);
				$plugin->setApplication(Factory::getApplication());

				return $plugin;
			}
		);
	}
};

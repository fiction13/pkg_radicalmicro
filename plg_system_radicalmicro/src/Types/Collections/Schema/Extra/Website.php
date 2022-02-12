<?php namespace RadicalMicro\Types\Collections\Schema\Extra;
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

use Joomla\CMS\Uri\Uri;
use RadicalMicro\Types\InterfaceTypes;

defined('_JEXEC') or die;

class Website implements InterfaceTypes
{

	public function execute($item, $priority)
	{
		if (is_array($item))
		{
			$item = (object) $item;
		}

		$data = [
			'uid'       => 'radicalmicro.schema.website',
			'@context'  => 'https://schema.org',
			'@type'     => 'WebSite',
			'url'       => Uri::root(),
		];

		return $data;
	}

	public function getConfig($params = null)
	{

	}

}
<?php namespace RadicalMicro\Types\Collections;
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

class Logo implements InterfaceTypes
{

	public function execute($item)
	{
		if (is_array($item))
		{
			$item = (object) $item;
		}

		$data = [
			'uid'       => 'radicalmicro.schema.logo',
			'@context'  => 'https://schema.org',
			'@type'     => 'Organization',
			'url'       => Uri::root(),
			'logo'      => $item->image
		];

		return $data;
	}

	public function getConfig($params = null)
	{

	}

}
<?php namespace RadicalMicro\Types\Classes;
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

use Joomla\CMS\Uri\Uri;
use RadicalMicro\Types\TypesInterface;

defined('_JEXEC') or die;

class Website implements TypesInterface
{

	public function execute(object $item)
	{
		$data = [
			'uid'       => 'radicalmicro.schema.website',
			'@context'  => 'https://schema.org',
			'@type'     => 'WebSite',
			'url'       => Uri::root(),
		];

		return $data;
	}

}
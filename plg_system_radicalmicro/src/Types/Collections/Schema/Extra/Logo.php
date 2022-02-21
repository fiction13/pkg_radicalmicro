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

class Logo implements InterfaceTypes
{
	/**
	 * @var string
	 * @since 1.0.0
	 */
	private $uid = 'radicalmicro.schema.logo';

	/**
	 * @param $item
	 * @param $priority
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function execute($item, $priority)
	{
		if (is_array($item))
		{
			$item = (object) $item;
		}

		$data = [
			'uid'       => $this->uid,
			'@context'  => 'https://schema.org',
			'@type'     => 'Organization',
			'url'       => Uri::root(),
			'logo'      => $item->image
		];

		return $data;
	}

	/**
	 * Get config for JForm and Yootheme Pro elements
	 *
	 * @param   bool  $addUid
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public function getConfig($addUid = true)
	{
		$config = [
			'image' => '',
		];

		if ($addUid)
		{
			$config['uid'] = $this->uid;
		}

		return $config;
	}

}
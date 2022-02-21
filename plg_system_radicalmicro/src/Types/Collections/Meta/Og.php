<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Types\Collections\Meta;

use RadicalMicro\Types\InterfaceTypes;

defined('_JEXEC') or die;

class Og implements InterfaceTypes
{
	/**
	 * @var string
	 * @since 1.0.0
	 */
	private $uid = 'radicalmicro.meta.og';

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

		$config = $this->getConfig();

		$data['uid']            = $this->uid;
		$data['og:title']       = isset($item->title) ? $item->title : '';
		$data['og:description'] = isset($item->description) ? $item->description : '';
		$data['og:type']        = isset($item->type) ? $item->type : 'website';
		$data['og:url']         = isset($item->url) ? $item->url : '' ;
		$data['og:image']       = isset($item->image) ? $item->image : '' ;
		$data['priority']       = $priority;

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
			'url'         => '',
			'type'        => 'website',
			'title'       => '',
			'description' => '',
			'image'       => '',
		];

		if ($addUid)
		{
			$config['uid'] = $this->uid;
		}

		return $config;
	}

}
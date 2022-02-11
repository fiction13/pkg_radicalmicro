<?php namespace RadicalMicro\Meta\Collections;
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

use RadicalMicro\Meta\InterfaceMeta;

defined('_JEXEC') or die;

class Og implements InterfaceMeta
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
	 * @param   null  $params
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public function getConfig($params = null)
	{
		$config = [
			'uid'         => 'radicalmicro.meta.og',
			'url'         => '',
			'type'        => 'website',
			'title'       => '',
			'description' => '',
			'image'       => '',
		];

		return $config;
	}

}
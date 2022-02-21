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

use Joomla\CMS\Uri\Uri;
use RadicalMicro\Types\InterfaceTypes;

defined('_JEXEC') or die;

class Twitter implements InterfaceTypes
{

	/**
	 * @var string
	 * @since 1.0.0
	 */
	private $uid = 'radicalmicro.meta.twitter';


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

		$data['uid']                 = $this->uid;
		$data['twitter:card']        = 'summary';
		$data['twitter:title']       = isset($item->title) ? $item->title : '';
		$data['twitter:description'] = isset($item->description) ? $item->description : '';
		$data['twitter:site']        = isset($item->site) ? $item->site : Uri::root();
		$data['twitter:image']       = isset($item->image) ? : $item->image;
		$data['priority']            = $priority;

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
			'site'        => '',
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
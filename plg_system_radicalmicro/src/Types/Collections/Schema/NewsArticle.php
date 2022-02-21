<?php namespace RadicalMicro\Types\Collections\Schema;
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

class NewsArticle implements InterfaceTypes
{
	/**
	 * @var string
	 * @since 1.0.0
	 */
	private $uid = 'radicalmicro.schema.newsarticle';

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

		$dataId = 'radicalmicro.schema.article.'.$item->id;

		$data = [
			'uid'               => $this->uid,
			'@context'          => 'https://schema.org',
			'@type'             => 'NewsArticle',
			'url'               => Uri::current(),
			'mainEntityOfPage'  => Uri::current(),
			'headline'          => $item->title,
			'articleBody'       => $item->description,
			'datePublished'     => $item->datePublished,
			'dateModified'      => $item->datePublished,
			'publisher'         => [
				"@type" => "Person",
	            "name" => $item->author
			]
		];

		if (isset($data->image)) {
			$data['image'] = [
				'@type' => 'ImageObject',
				'url' => $item->image
			];
		}

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
			'title'         => '',
			'description'   => '',
			'datePublished' => '',
			'dateModified'  => '',
			'author'        => '',
			'image'         => ''
		];

		if ($addUid)
		{
			$config['uid'] = $this->uid;
		}

		return $config;
	}

}
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

class Article implements InterfaceTypes
{

	public function execute($item)
	{
		if (is_array($item))
		{
			$item = (object) $item;
		}

		$dataId = 'radicalmicro.schema.article.'.$item->id;

		$data = [
			'uid'               => $dataId,
			'@context'          => 'https://schema.org',
			'@type'             => 'Article',
			'headline'          => $item->title,
			'description'       => $item->description,
			'mainEntityOfPage'  => [
				'@type' => 'WebPage',
				'id'    => Uri::current()
			],
			'datePublished'     => $item->datePublished,
			'dateModified'      => $item->dateModified,
			'author'            => [
				"@type" => "Person",
	            "name" => $item->author
			]
		];

		if (isset($item->image)) {
			$data['image'] = [
				'@type' => 'ImageObject',
				'url' => $item->image
			];
		}

		return $data;
	}

	public function getConfig($params = null)
	{

	}

}
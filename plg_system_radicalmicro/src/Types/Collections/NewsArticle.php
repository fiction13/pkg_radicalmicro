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

class NewsArticle implements InterfaceTypes
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

	public function getConfig($params = null)
	{

	}

}
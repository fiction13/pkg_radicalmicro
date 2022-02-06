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

class NewsArticle implements TypesInterface
{

	public function execute(object $item)
	{
		$dataId = 'radicalmicro.schema.article.'.$item->id;

		$data = [
			'uid'               =>$dataId,
			'@context'          => 'https://schema.org',
			'@type'             => 'NewsArticle',
			'url'               => Uri::current(),
			'mainEntityOfPage'  => Uri::current(),
			'headline'          => $item->title,
			'articleBody'       => $item->description,
			'datePublished'     => $item->published,
			'dateModified'      => $item->modified,
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

}
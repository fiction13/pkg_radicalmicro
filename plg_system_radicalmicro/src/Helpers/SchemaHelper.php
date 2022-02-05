<?php namespace RadicalMicro\Helpers;
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

class SchemaHelper extends UnversalTreeHelper
{

	public static function getInstance($name = 'schema')
	{
		return parent::getInstance($name);
	}

	public function getBuild($uid = null): array
	{
		$output = parent::getBuild($uid);

		if ($output)
		{
			// $output = array_values($output);

			foreach ($output as $item){
			    unset($item->priority);
				unset($item->uid);
			}
		}

		return $output;
	}

	// Get Website schema.org

	public static function getSchemaWebsite($item)
	{
		$schemaData = [
			'uid'       => 'radicalmicro.schema.website',
			'@context'  => 'https://schema.org',
			'@type'      => 'WebSite',
			'url'       => Uri::root(),
		];

		return $schemaData;
	}

	// Get Logo schema.org

	public static function getSchemaLogo($item)
	{
		$schemaData = [
			'uid'       => 'radicalmicro.schema.logo',
			'@context'  => 'https://schema.org',
			'@type'      => 'Organization',
			'url'       => Uri::root(),
			'logo'      => $item->logo
		];

		return $schemaData;
	}

	// Get Article schema.org

	public static function getSchemaArticle($item)
	{
		$schemaId = 'radicalmicro.schema.article.'.$item->id;

		$schemaData = [
			'uid'               => $schemaId,
			'@context'          => 'https://schema.org',
			'@type'             => 'Article',
			'headline'          => $item->title,
			'description'       => $item->description,
			'mainEntityOfPage'  => [
				'@type' => 'WebPage',
				'id'    => Uri::current()
			],
			'datePublished'     => $item->published,
			'dateModified'      => $item->modified,
			'author'            => [
				"@type" => "Person",
	            "name" => $item->author
			]
		];

		if (isset($item->image)) {
			$schemaData['image'] = [
				'@type' => 'ImageObject',
				'url' => $item->image
			];
		}

		return $schemaData;
	}

	// Set NewsArticle schema.org

	public static function getSchemaNewsArticle($item)
	{
		$schemaId = 'radicalmicro.schema.article.'.$item->id;

		$schemaData = [
			'uid'               => $schemaId,
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

		if (isset($item->image)) {
			$schemaData['image'] = [
				'@type' => 'ImageObject',
				'url' => $item->image
			];
		}

		return $schemaData;
	}

}
<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use RadicalMicro\Helpers\SchemaHelper;
use RadicalMicro\Helpers\OGHelper;

/**
 * Radicalmicro
 *
 * @package   plgRadicalmicroContent
 * @since     1.0.0
 */
class plgRadicalmicroContent extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0.0
	 */
	protected $app;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

    /**
	 * OnRadicalmicroProvider event
     *
     * @return array.
	 *
	 * @since  1.0.0
	 */
	public function onRadicalmicroProvider(&$data)
	{
		if (!$this->isArticleView())
		{
			return;
		}

		$article_id = (int) $this->app->input->get('id', 0);

		// Get Article
		$article = Table::getInstance('Content', 'JTable');
		$article->load($article_id);
		$image = $this->getImage($article);

		// Add microdata
		$micro             = SchemaHelper::getInstance();
		$schema_article_id = 'plugin-article-' . $article->id;

		$micro->addChild('root', [
			'uid'      => $schema_article_id,
			'name'    => '@context',
			'content' => 'https://schema.org'
		])->addChild($schema_article_id, [
			'name'    => '@type',
			'content' => 'NewsArticle'
		])->addChild($schema_article_id, [
			'name'    => 'headline',
			'content' => $article->title
		])->addChild($schema_article_id, [
			'name'    => 'author',
			'content' => Factory::getUser($article->created_by)->name
		])->addChild($schema_article_id, [
			'name'    => 'datePublished',
			'content' => $article->publish_up //TODO преобразовывать вроде надо
		]);

		// Check image
		if ($image)
		{
			$micro->addChild($schema_article_id, [
				'name'    => 'image',
				'content' => $image
			]);
		}


		// TODO Добавляем Opengraph


	}

	/**
	 * Check article page view
	 *
	 * @since  1.1.0
	 */
	public function isArticleView()
	{
		$input = Factory::getApplication()->input;

		return $input->getCmd('option') === 'com_content' && $input->getCmd('view') === 'article' && is_null($input->getCmd('task'));
	}

	/**
	 * Get image from Article object
     *
     * @return void|string.
	 *
	 * @since  1.1.0
	 */
	public function getImage(object $article)
	{
		$jsonImgObj = json_decode($article->images);

		if (!empty($jsonImgObj->image_fulltext))
		{
			return URI::root() . $jsonImgObj->image_fulltext;
		}

		if (!empty($json->image_intro))
		{
			return URI::root() . $jsonImgObj->image_intro;
		}

		return;
	}
}

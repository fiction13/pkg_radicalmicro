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
use RadicalMicro\Helpers\PathHelper;
use RadicalMicro\Helpers\Tree\SchemaHelper;
use RadicalMicro\Helpers\Tree\OGHelper;
use RadicalMicro\Helpers\DateHelper;
use RadicalMicro\Helpers\TypesHelper;

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
	 * Opengraph type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $ogType = 'og';


	/**
	 * OnRadicalmicroRegisterTypes for init your types for each collection
	 *
	 * @since 1.0.0
	 */
	public function onRadicalmicroRegisterTypes()
	{
		// $path - absolute path of directory with your types of each collection
		//
		// PathHelper::getInstance()->register($path, 'schema');
		// PathHelper::getInstance()->register($path, 'schema_extra');
		// PathHelper::getInstance()->register($path, 'meta');
	}

    /**
	 * OnRadicalmicroProvider event
     *
     * @return array.
	 *
	 * @since  1.0.0
	 */
	public function onRadicalmicroProvider($params)
	{
		$object = $this->getProviderData();

		if (!$object) {
			return;
		}

		// Get schema type
		$type = $this->params->get('type', 'article');

		// Get schema data
		$schemaData = TypesHelper::execute('schema', $type, $object);

		// Get opengraph data
		$ogData = TypesHelper::execute('meta', $this->ogType, $object);

		#TODO Добавить все активные типы meta

		// Set data
		SchemaHelper::getInstance()->addChild('root', $schemaData);
        OGHelper::getInstance()->addChild('root', $ogData);
	}


	/**
	 * Method get provider data
	 *
	 * @since 1.0.0
	 */
	public function getProviderData()
	{
		// Check is article view
		if (!$this->isArticleView())
		{
			return;
		}

		$article_id = (int) $this->app->input->get('id', 0);

		// Get Article
		$article = Table::getInstance('Content', 'JTable');
		$article->load($article_id);

		// Get image
		$image = $this->getImage($article);

		// Data object
		$object = new stdClass();
		$object->id = $article->id;
		$object->type = $this->params->get('type', 'article');
		$object->title = $article->title;
		$object->description = $article->introtext.$article->fulltext;
		$object->url = Uri::current();
		$object->datePublished = DateHelper::format($article->publish_up);
        $object->dateCreated = DateHelper::format($article->created);
		$object->dateModified = DateHelper::format($article->modified);
		$object->author = Factory::getUser($article->created_by)->name;

		// Check if YOOtheme Pro is loaded
        if ($this->isYoothemeBuider($object->description)) {
            $object->description = '';
        }

		if ($image)
		{
			$object->image = $image;
		}

		return $object;
	}

	/**
	 * Check article page view
	 *
	 * @since  1.0.0
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
	public function getImage( $article)
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

	public function isYoothemeBuider($description)
	{
		if (strlen($description) == 0) {
			return false;
		}

		if (substr($description, 0, 4) === '<!--' && substr($description, -3) == '-->')
		{
			return true;
		}

		return false;
	}
}

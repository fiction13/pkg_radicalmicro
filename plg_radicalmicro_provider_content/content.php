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

JLoader::register('RadicalMicroHelper', JPATH_PLUGINS .'/system/radicalmicro/helpers/RadicalMicroHelper.php');
JLoader::load('RadicalMicroHelper');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use RadicalMicro\Helpers\RadicalMicroHelper;

/**
 * Usermail radicalform plugin.
 *
 * @package   plg_radicalform_usermail
 * @since     1.0.0
 */
class plgRadicalMicro_providerContent extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	public function onRenderRadicalMicroSchema(&$data)
	{	
	    if (!$this->isArticleView()) {
	    	return;
	    }

	    $articleId  = (int) Factory::getApplication()->input->get('id', 0);

	    // Get Article
		$article = Table::getInstance('Content', 'JTable');
		$article->load($articleId);

		$micro = RadicalMicroHelper::getInstance('schema.content');

		$micro->addChild('root', [
		    'name' => '@context',
		    'content' => 'https://schema.org'
		])->addChild('root', [
		    'name' => '@type',
		    'content' => 'NewsArticle'
		])->addChild('root', [
		    'name' => 'headline',
		    'content' => $article->title
		])->addChild('root', [
		    'name' => 'author',
		    'content' => Factory::getUser($article->created_by)->name
		])->addChild('root', [
		    'name' => 'datePublished',
		    'content' => $article->publish_up
		]);

		// image

		if ($image = $this->getImage($article)) {
			$micro->addChild('root', [
			    'name' 		=> 'image',
			    'content' 	=> $image
			]);
		}

		$data = $micro->getBuild('root');

		array_push($data);
	}

	// Is article

	public function isArticleView()
	{
		$input = Factory::getApplication()->input;

        return $input->getCmd('option') === 'com_content' && $input->getCmd('view') === 'article' && is_null($input->getCmd('task'));
	}

	// Get image

	public function getImage($article)
	{
		$image 		= '';
		$json 		= json_decode($article->images);

		if (!empty($json->image_fulltext)) {
			$image = URI::root().$json->image_fulltext;

			return $image;
		}

		if (!empty($json->image_intro)) {
			$image = URI::root().$json->image_intro;

			return $image;
		}

		return; 
	}
}

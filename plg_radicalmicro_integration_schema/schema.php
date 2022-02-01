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

// JLoader::register('RadicalMicroHelper', JPATH_PLUGINS .'/system/radicalmicro/helpers/RadicalMicroHelper.php');
// JLoader::load('RadicalMicroHelper');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
// use RadicalMicro\Helpers\RadicalMicroHelper;

/**
 * Usermail radicalform plugin.
 *
 * @package   plg_radicalform_usermail
 * @since     1.0.0
 */
class plgRadicalMicro_integrationSchema extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	public function onRenderRadicalMicro()
	{
	    $data = array();

	    // Get provider plugins
		PluginHelper::importPlugin('radicalmicro_provider');

    	Factory::getApplication()->triggerEvent('onRenderRadicalMicroSchema', array(&$data));

    	$this->renderSchema($data);
	}

	// Render Schema

	public function renderSchema($data)
	{
		if (empty($data)) {
			return;
		}

		$result = array();

		foreach ($data as $key => $value)
		{
			$result[$value->name] = $value->content;
		}

		$result = json_encode($result);

		$body = Factory::getApplication()->getBody();
		$body = str_replace("</body>", "<script type=\"application/ld+json\">$result</script></body>", $body);

		Factory::getApplication()->setBody($body);
	}
}

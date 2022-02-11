<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace RadicalMicro\Helpers\Tree;

defined('_JEXEC') or die;

class OGHelper extends UnversalTreeHelper
{

	public static function getInstance($name = 'opengrapgh')
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

}